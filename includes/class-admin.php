<?php
/**
 * Admin class for the Instant Guest Post Request plugin.
 *
 * @package Instant_Guest_Post_Request
 */

namespace IGPR;

/**
 * Admin class.
 */
class Admin {

	/**
	 * Initialize the admin class.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_filter( 'post_row_actions', array( $this, 'add_guest_post_actions' ), 10, 2 );
		add_action( 'admin_action_igpr_approve_post', array( $this, 'approve_guest_post' ) );
		add_action( 'admin_action_igpr_reject_post', array( $this, 'reject_guest_post' ) );
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
	}

	/**
	 * Register admin menu.
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'Guest Post Requests', 'instant-guest-post-request' ),
			__( 'Guest Posts', 'instant-guest-post-request' ),
			'manage_options',
			'igpr-submissions',
			array( $this, 'render_submissions_page' ),
			'dashicons-welcome-write-blog',
			30
		);

		add_submenu_page(
			'igpr-submissions',
			__( 'Submissions', 'instant-guest-post-request' ),
			__( 'Submissions', 'instant-guest-post-request' ),
			'manage_options',
			'igpr-submissions',
			array( $this, 'render_submissions_page' )
		);

		add_submenu_page(
			'igpr-submissions',
			__( 'Email Logs', 'instant-guest-post-request' ),
			__( 'Email Logs', 'instant-guest-post-request' ),
			'manage_options',
			'igpr-email-logs',
			array( $this, 'render_email_logs_page' )
		);

		add_submenu_page(
			'igpr-submissions',
			__( 'Settings', 'instant-guest-post-request' ),
			__( 'Settings', 'instant-guest-post-request' ),
			'manage_options',
			'igpr-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( ! in_array( $hook, array( 
			'toplevel_page_igpr-submissions',
			'guest-posts_page_igpr-email-logs',
			'guest-posts_page_igpr-settings'
		), true ) ) {
			return;
		}

		wp_enqueue_style(
			'igpr-admin-style',
			plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/admin.css',
			array(),
			filemtime( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/css/admin.css' )
		);

		$asset_file = include plugin_dir_path( dirname( __FILE__ ) ) . 'admin/js/index.asset.php';

                wp_enqueue_script(
                        'igpr-admin-script',
                        plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/index.js',
                        $asset_file['dependencies'],
                        $asset_file['version'],
                        true
                );

                wp_localize_script(
                        'igpr-admin-script',
                        'igprSettings',
                        array(
                                'nonce'   => wp_create_nonce( 'wp_rest' ),
                                'restUrl' => esc_url_raw( rest_url( 'igpr/v1/settings' ) ),
                        )
                );
        }

	/**
	 * Add custom actions to guest post rows in admin.
	 *
	 * @param array    $actions An array of row action links.
	 * @param \WP_Post $post    The post object.
	 * @return array Updated array of row action links.
	 */
	public function add_guest_post_actions( $actions, $post ) {
		// Only add actions to pending posts that have our meta
		if ( $post->post_status === 'pending' && get_post_meta( $post->ID, 'igpr_author_email', true ) ) {
			$approve_url = wp_nonce_url( 
				admin_url( 'admin.php?action=igpr_approve_post&post=' . $post->ID ), 
				'igpr_approve_post_' . $post->ID, 
				'igpr_nonce' 
			);
			
			$reject_url = wp_nonce_url( 
				admin_url( 'admin.php?action=igpr_reject_post&post=' . $post->ID ), 
				'igpr_reject_post_' . $post->ID, 
				'igpr_nonce' 
			);
			
			$actions['igpr_approve'] = sprintf(
				'<a href="%s" class="igpr-approve" style="color:#0c0;">%s</a>',
				esc_url( $approve_url ),
				esc_html__( 'Approve', 'instant-guest-post-request' )
			);
			
			$actions['igpr_reject'] = sprintf(
				'<a href="%s" class="igpr-reject" style="color:#c00;">%s</a>',
				esc_url( $reject_url ),
				esc_html__( 'Reject', 'instant-guest-post-request' )
			);
		}
		
		return $actions;
	}

	/**
	 * Approve a guest post.
	 */
	public function approve_guest_post() {
		$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;
		
		if ( ! $post_id ) {
			wp_die( esc_html__( 'No post ID specified.', 'instant-guest-post-request' ) );
		}
		
		// Check if using token from email
		if ( isset( $_GET['token'] ) ) {
			$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
			$stored_token = get_post_meta( $post_id, '_igpr_approve_token', true );
			
			if ( empty( $stored_token ) || $token !== $stored_token ) {
				wp_die( esc_html__( 'Invalid or expired token.', 'instant-guest-post-request' ) );
			}
		} else {
			// Regular nonce check for admin UI
			check_admin_referer( 'igpr_approve_post_' . $post_id, 'igpr_nonce' );
		}
		
		// Check permissions
		if ( ! current_user_can( 'publish_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to publish posts.', 'instant-guest-post-request' ) );
		}
		
		// Update post status to publish
		$result = wp_update_post( array(
			'ID'          => $post_id,
			'post_status' => 'publish',
		) );
		
		if ( $result ) {
			// Clean up the token
			delete_post_meta( $post_id, '_igpr_approve_token' );
			delete_post_meta( $post_id, '_igpr_reject_token' );
			
			// Send notification to author
			$this->send_author_notification( $post_id, 'approved' );
			
			// Redirect back with success message
			wp_redirect( add_query_arg( 'igpr_message', 'approved', admin_url( 'edit.php' ) ) );
			exit;
		} else {
			wp_die( esc_html__( 'Failed to approve post.', 'instant-guest-post-request' ) );
		}
	}

	/**
	 * Reject a guest post.
	 */
	public function reject_guest_post() {
		$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;
		
		if ( ! $post_id ) {
			wp_die( esc_html__( 'No post ID specified.', 'instant-guest-post-request' ) );
		}
		
		// Check if using token from email
		if ( isset( $_GET['token'] ) ) {
			$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
			$stored_token = get_post_meta( $post_id, '_igpr_reject_token', true );
			
			if ( empty( $stored_token ) || $token !== $stored_token ) {
				wp_die( esc_html__( 'Invalid or expired token.', 'instant-guest-post-request' ) );
			}
		} else {
			// Regular nonce check for admin UI
			check_admin_referer( 'igpr_reject_post_' . $post_id, 'igpr_nonce' );
		}
		
		// Check permissions
		if ( ! current_user_can( 'delete_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to trash posts.', 'instant-guest-post-request' ) );
		}
		
		// Send notification to author before trashing
		$this->send_author_notification( $post_id, 'rejected' );
		
		// Move post to trash
		$result = wp_trash_post( $post_id );
		
		if ( $result ) {
			// Clean up the tokens
			delete_post_meta( $post_id, '_igpr_approve_token' );
			delete_post_meta( $post_id, '_igpr_reject_token' );
			
			// Redirect back with success message
			wp_redirect( add_query_arg( 'igpr_message', 'rejected', admin_url( 'edit.php' ) ) );
			exit;
		} else {
			wp_die( esc_html__( 'Failed to reject post.', 'instant-guest-post-request' ) );
		}
	}

	/**
	 * Send notification to the guest post author.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $status  Status of the post (approved or rejected).
	 */
	private function send_author_notification( $post_id, $status ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}
		
		$author_email = get_post_meta( $post_id, 'igpr_author_email', true );
		$author_name = get_post_meta( $post_id, 'igpr_author_name', true );
		
		if ( ! $author_email ) {
			return;
		}
		
		$subject = '';
		$message = '';
		
		if ( $status === 'approved' ) {
			$subject = sprintf( __( '[%s] Your Guest Post Has Been Approved', 'instant-guest-post-request' ), get_bloginfo( 'name' ) );
			$message = sprintf(
				/* translators: %1$s: author name, %2$s: post title, %3$s: post URL */
				__( 'Hello %1$s,

Great news! Your guest post "%2$s" has been approved and is now published on our site.

You can view your published post here: %3$s

Thank you for your contribution!

Regards,
%4$s Team', 'instant-guest-post-request' ),
				$author_name,
				$post->post_title,
				get_permalink( $post_id ),
				get_bloginfo( 'name' )
			);
		} else {
			$subject = sprintf( __( '[%s] Your Guest Post Submission', 'instant-guest-post-request' ), get_bloginfo( 'name' ) );
			$message = sprintf(
				/* translators: %1$s: author name, %2$s: post title, %3$s: site name */
				__( 'Hello %1$s,

Thank you for submitting your guest post "%2$s" to our site.

After careful review, we have decided not to publish this submission at this time.

We encourage you to review our guidelines and consider submitting another post in the future.

Regards,
%3$s Team', 'instant-guest-post-request' ),
				$author_name,
				$post->post_title,
				get_bloginfo( 'name' )
			);
		}
		
		wp_mail( $author_email, $subject, $message );
		
		// Log the email
		global $wpdb;
		$table_name = $wpdb->prefix . 'igpr_email_logs';
		
		$wpdb->insert(
			$table_name,
			array(
				'to_email' => $author_email,
				'subject'  => $subject,
				'status'   => 'sent',
			)
		);
	}

	/**
	 * Display admin notices.
	 */
	public function display_admin_notices() {
		if ( ! isset( $_GET['igpr_message'] ) ) {
			return;
		}
		
		$message = sanitize_text_field( wp_unslash( $_GET['igpr_message'] ) );
		$class = 'notice notice-success is-dismissible';
		$text = '';
		
		switch ( $message ) {
			case 'approved':
				$text = __( 'Guest post approved and published successfully. Author has been notified.', 'instant-guest-post-request' );
				break;
			case 'rejected':
				$text = __( 'Guest post rejected and moved to trash. Author has been notified.', 'instant-guest-post-request' );
				$class = 'notice notice-warning is-dismissible';
				break;
		}
		
		if ( $text ) {
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $text ) );
		}
	}

	/**
	 * Render submissions page.
	 */
	public function render_submissions_page() {
		echo '<div class="wrap"><div id="igpr-submissions-root"></div></div>';
	}

	/**
	 * Render email logs page.
	 */
	public function render_email_logs_page() {
		echo '<div class="wrap"><div id="igpr-email-logs-root"></div></div>';
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		echo '<div class="wrap"><div id="igpr-settings-root"></div></div>';
	}
}