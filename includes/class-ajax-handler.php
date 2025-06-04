<?php
/**
 * AJAX Handler class for the Instant Guest Post Request plugin.
 *
 * @package Instant_Guest_Post_Request
 */

namespace IGPR;

/**
 * AJAX Handler class.
 */
class AJAX_Handler {

	/**
	 * Initialize the AJAX handler class.
	 */
	public function __construct() {
		add_action( 'wp_ajax_igpr_submit_post', array( $this, 'handle_post_submission' ) );
		add_action( 'wp_ajax_nopriv_igpr_submit_post', array( $this, 'handle_post_submission' ) );
	}

	/**
	 * Handle post submission.
	 */
	public function handle_post_submission() {
		// Check nonce.
		if ( ! check_ajax_referer( 'igpr_form_nonce', 'security', false ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security check failed. Please refresh the page and try again.', 'instant-guest-post-request' ),
			) );
		}

		// Get form data.
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';

		// Validate form data.
		if ( empty( $name ) || empty( $email ) || empty( $title ) || empty( $content ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please fill in all required fields.', 'instant-guest-post-request' ),
			) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please enter a valid email address.', 'instant-guest-post-request' ),
			) );
		}

		// Create post.
		$post_id = wp_insert_post( array(
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'pending',
			'post_type'    => 'post',
			'meta_input'   => array(
				'igpr_author_name'  => $name,
				'igpr_author_email' => $email,
			),
		) );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Failed to submit your post. Please try again.', 'instant-guest-post-request' ),
			) );
		}

		// Handle featured image upload if present
		if ( ! empty( $_FILES['featured_image']['name'] ) ) {
			$upload_result = $this->handle_featured_image_upload( $post_id );
			
			if ( is_wp_error( $upload_result ) ) {
				// Continue with post submission but include warning about image
				$image_error = $upload_result->get_error_message();
			}
		}

		// Send notification email to admin.
		$this->send_notification_email( $post_id, $name, $email, $title );

		// Send auto-reply to submitter
		$this->send_author_confirmation( $post_id, $name, $email, $title );

		// Log the email.
		$this->log_email( $email, __( 'New Guest Post Submission', 'instant-guest-post-request' ), 'sent' );

		// Success message
		$success_message = __( 'Your guest post has been submitted successfully and is awaiting review.', 'instant-guest-post-request' );
		
		// Add image upload warning if applicable
		if ( isset( $image_error ) ) {
			$success_message .= ' ' . __( 'Note: There was an issue with your image upload: ', 'instant-guest-post-request' ) . $image_error;
		}
		
		// Check if this is a non-AJAX fallback submission
		if ( isset( $_POST['igpr_fallback'] ) && $_POST['igpr_fallback'] === '1' ) {
			// Redirect with success parameters
			wp_redirect( add_query_arg( 
				array(
					'igpr_status' => 'success',
					'igpr_message' => urlencode( $success_message ),
				),
				wp_get_referer() 
			) );
			exit;
		}

		wp_send_json_success( array(
			'message' => $success_message,
		) );
	}

	/**
	 * Handle featured image upload.
	 *
	 * @param int $post_id Post ID.
	 * @return int|WP_Error Attachment ID on success, WP_Error on failure.
	 */
	private function handle_featured_image_upload( $post_id ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// Check file type
		$file = $_FILES['featured_image'];
		$allowed_types = array( 'image/jpeg', 'image/png', 'image/gif' );
		
		if ( ! in_array( $file['type'], $allowed_types, true ) ) {
			return new \WP_Error( 'invalid_file_type', __( 'Invalid file type. Please upload a JPG, PNG, or GIF image.', 'instant-guest-post-request' ) );
		}
		
		// Check file size (2MB max)
		$max_size = 2 * 1024 * 1024; // 2MB in bytes
		if ( $file['size'] > $max_size ) {
			return new \WP_Error( 'file_too_large', __( 'File is too large. Maximum size is 2MB.', 'instant-guest-post-request' ) );
		}

		// Upload the image
		$attachment_id = media_handle_upload( 'featured_image', $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Set as featured image
		set_post_thumbnail( $post_id, $attachment_id );

		return $attachment_id;
	}

	/**
	 * Send notification email to admin.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $name    Author name.
	 * @param string $email   Author email.
	 * @param string $title   Post title.
	 */
	private function send_notification_email( $post_id, $name, $email, $title ) {
		$admin_email = get_option( 'admin_email' );
		$subject     = sprintf( __( '[%s] New Guest Post Submission: %s', 'instant-guest-post-request' ), get_bloginfo( 'name' ), $title );
		
		// Create direct action links without nonce for email
		$approve_url = admin_url( 'admin.php?action=igpr_approve_post&post=' . $post_id . '&token=' . $this->generate_action_token($post_id, 'approve') );
		$reject_url = admin_url( 'admin.php?action=igpr_reject_post&post=' . $post_id . '&token=' . $this->generate_action_token($post_id, 'reject') );
		$edit_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
		
		// Plain text email with direct URLs
		$message = sprintf(
			/* translators: %1$s: post title, %2$s: author name, %3$s: author email, %4$s: edit link, %5$s: approve link, %6$s: reject link */
			__( 'A new guest post has been submitted for review.

Post Title: %1$s
Author Name: %2$s
Author Email: %3$s

Actions:
- Edit Post: %4$s
- Approve: %5$s
- Reject: %6$s', 'instant-guest-post-request' ),
			$title,
			$name,
			$email,
			$edit_url,
			$approve_url,
			$reject_url
		);

		// Set content type to plain text to avoid HTML encoding
		add_filter( 'wp_mail_content_type', function() { return 'text/plain'; } );
		
		wp_mail( $admin_email, $subject, $message );
		
		// Reset content type
		remove_all_filters( 'wp_mail_content_type' );
	}

	/**
	 * Generate a secure token for post actions.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $action  Action type (approve or reject).
	 * @return string Secure token.
	 */
	private function generate_action_token( $post_id, $action ) {
		$key = wp_hash( $post_id . $action . get_option( 'auth_salt' ) );
		update_post_meta( $post_id, '_igpr_' . $action . '_token', $key );
		return $key;
	}

	/**
	 * Send confirmation email to the author.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $name    Author name.
	 * @param string $email   Author email.
	 * @param string $title   Post title.
	 */
	private function send_author_confirmation( $post_id, $name, $email, $title ) {
		$subject = sprintf( __( '[%s] Thank You for Your Guest Post Submission', 'instant-guest-post-request' ), get_bloginfo( 'name' ) );
		$message = sprintf(
			/* translators: %1$s: author name, %2$s: post title, %3$s: site name */
			__( 'Hello %1$s,

Thank you for submitting your guest post "%2$s" to %3$s.

Your submission has been received and is currently under review. We will notify you once a decision has been made.

Regards,
%3$s Team', 'instant-guest-post-request' ),
			$name,
			$title,
			get_bloginfo( 'name' )
		);

		wp_mail( $email, $subject, $message );
		
		// Log the confirmation email
		$this->log_email( $email, $subject, 'sent' );
	}

	/**
	 * Log email.
	 *
	 * @param string $to      Email recipient.
	 * @param string $subject Email subject.
	 * @param string $status  Email status.
	 */
	private function log_email( $to, $subject, $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'igpr_email_logs';

		// Create table if it doesn't exist.
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				to_email varchar(100) NOT NULL,
				subject varchar(255) NOT NULL,
				status varchar(20) NOT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		// Insert log.
		$wpdb->insert(
			$table_name,
			array(
				'to_email' => $to,
				'subject'  => $subject,
				'status'   => $status,
			)
		);
	}
}