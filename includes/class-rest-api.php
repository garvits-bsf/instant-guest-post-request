<?php
/**
 * REST API class for the Instant Guest Post Request plugin.
 *
 * @package Instant_Guest_Post_Request
 */

namespace IGPR;

/**
 * REST API class.
 */
class REST_API {

	/**
	 * Default settings.
	 *
	 * @var array
	 */
	private $default_settings = array(
		'enable_admin_notifications'  => true,
		'admin_email'                => '',
		'admin_email_subject'        => '[{site_name}] New Guest Post Submission: {post_title}',
		'enable_author_notifications' => true,
		'author_email_subject'       => '[{site_name}] Thank You for Your Guest Post Submission',
		'author_email_template'      => "Hello {author_name},\n\nThank you for submitting your guest post \"{post_title}\" to {site_name}.\n\nYour submission has been received and is currently under review. We will notify you once a decision has been made.\n\nRegards,\n{site_name} Team",
		'approval_email_subject'     => '[{site_name}] Your Guest Post Has Been Approved',
		'approval_email_template'    => "Hello {author_name},\n\nGreat news! Your guest post \"{post_title}\" has been approved and is now published on our site.\n\nYou can view your published post here: {post_url}\n\nThank you for your contribution!\n\nRegards,\n{site_name} Team",
		'rejection_email_subject'    => '[{site_name}] Your Guest Post Submission',
		'rejection_email_template'   => "Hello {author_name},\n\nThank you for submitting your guest post \"{post_title}\" to our site.\n\nAfter careful review, we have decided not to publish this submission at this time.\n\nWe encourage you to review our guidelines and consider submitting another post in the future.\n\nRegards,\n{site_name} Team",
		'form_title'                 => 'Submit a Guest Post',
		'success_message'            => 'Your guest post has been submitted successfully and is awaiting review.',
		'required_fields'            => array('name', 'email', 'title', 'content'),
		'enable_featured_image'      => true,
		'max_upload_size'            => 2, // In MB
	);

	/**
	 * Initialize the REST API class.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			'igpr/v1',
			'/submissions',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_submissions' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			'igpr/v1',
			'/submissions/(?P<id>\d+)/approve',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'approve_submission' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			'igpr/v1',
			'/submissions/(?P<id>\d+)/reject',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'reject_submission' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			'igpr/v1',
			'/email-logs',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_email_logs' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			'igpr/v1',
			'/settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
	}

	/**
	 * Check if user has admin permission.
	 *
	 * @return bool Whether user has permission.
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get submissions.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response object.
	 */
	public function get_submissions( $request ) {
		$page = isset( $request['page'] ) ? absint( $request['page'] ) : 1;
		$per_page = 10;
		$offset = ( $page - 1 ) * $per_page;

		$args = array(
			'post_type'      => 'post',
			'post_status'    => array( 'pending', 'publish', 'trash' ),
			'posts_per_page' => $per_page,
			'offset'         => $offset,
			'meta_query'     => array(
				array(
					'key'     => 'igpr_author_email',
					'compare' => 'EXISTS',
				),
			),
		);

		$query = new \WP_Query( $args );
		$submissions = array();

		foreach ( $query->posts as $post ) {
			$status = $post->post_status;
			if ( $status === 'trash' ) {
				$status = 'rejected';
			}

			$submissions[] = array(
				'id'           => $post->ID,
				'title'        => $post->post_title,
				'author_name'  => get_post_meta( $post->ID, 'igpr_author_name', true ),
				'author_email' => get_post_meta( $post->ID, 'igpr_author_email', true ),
				'date'         => get_the_date( 'Y-m-d H:i:s', $post ),
				'status'       => $status,
				'edit_url'     => get_edit_post_link( $post->ID, 'raw' ),
			);
		}

		$total_posts = $query->found_posts;
		$total_pages = ceil( $total_posts / $per_page );

		return rest_ensure_response(
			array(
				'submissions' => $submissions,
				'totalPages'  => $total_pages,
			)
		);
	}

	/**
	 * Approve submission.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response object.
	 */
	public function approve_submission( $request ) {
		$post_id = $request['id'];
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new \WP_Error( 'not_found', __( 'Post not found.', 'instant-guest-post-request' ), array( 'status' => 404 ) );
		}

		$result = wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Send notification to author.
		$this->send_author_notification( $post_id, 'approved' );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Post approved successfully.', 'instant-guest-post-request' ),
			)
		);
	}

	/**
	 * Reject submission.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response object.
	 */
	public function reject_submission( $request ) {
		$post_id = $request['id'];
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new \WP_Error( 'not_found', __( 'Post not found.', 'instant-guest-post-request' ), array( 'status' => 404 ) );
		}

		// Send notification to author before trashing.
		$this->send_author_notification( $post_id, 'rejected' );

		$result = wp_trash_post( $post_id );

		if ( ! $result ) {
			return new \WP_Error( 'trash_failed', __( 'Failed to reject post.', 'instant-guest-post-request' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Post rejected successfully.', 'instant-guest-post-request' ),
			)
		);
	}

	/**
	 * Send notification to the guest post author.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $status  Status of the post (approved or rejected).
	 */
	public function send_author_notification( $post_id, $status ) {
		$settings = $this->get_plugin_settings();
		
		if ( empty( $settings['enable_author_notifications'] ) ) {
			return;
		}

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
		$site_name = get_bloginfo( 'name' );
		
		if ( $status === 'approved' ) {
			$subject = ! empty( $settings['approval_email_subject'] ) 
				? $settings['approval_email_subject'] 
				: sprintf( __( '[%s] Your Guest Post Has Been Approved', 'instant-guest-post-request' ), $site_name );
			
			$subject = str_replace( 
				array( '{site_name}', '{post_title}' ), 
				array( $site_name, $post->post_title ), 
				$subject 
			);
			
			$template = ! empty( $settings['approval_email_template'] ) 
				? $settings['approval_email_template'] 
				: sprintf(
					__( 'Hello {author_name},

Great news! Your guest post "{post_title}" has been approved and is now published on our site.

You can view your published post here: {post_url}

Thank you for your contribution!

Regards,
{site_name} Team', 'instant-guest-post-request' )
				);
			
			$message = str_replace( 
				array( '{author_name}', '{post_title}', '{site_name}', '{post_url}' ), 
				array( $author_name, $post->post_title, $site_name, get_permalink( $post_id ) ), 
				$template 
			);
		} else {
			$subject = ! empty( $settings['rejection_email_subject'] ) 
				? $settings['rejection_email_subject'] 
				: sprintf( __( '[%s] Your Guest Post Submission', 'instant-guest-post-request' ), $site_name );
			
			$subject = str_replace( 
				array( '{site_name}', '{post_title}' ), 
				array( $site_name, $post->post_title ), 
				$subject 
			);
			
			$template = ! empty( $settings['rejection_email_template'] ) 
				? $settings['rejection_email_template'] 
				: sprintf(
					__( 'Hello {author_name},

Thank you for submitting your guest post "{post_title}" to our site.

After careful review, we have decided not to publish this submission at this time.

We encourage you to review our guidelines and consider submitting another post in the future.

Regards,
{site_name} Team', 'instant-guest-post-request' )
				);
			
			$message = str_replace( 
				array( '{author_name}', '{post_title}', '{site_name}' ), 
				array( $author_name, $post->post_title, $site_name ), 
				$template 
			);
		}
		
		wp_mail( $author_email, $subject, $message );
		
		// Log the email.
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
	 * Get email logs.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response object.
	 */
	public function get_email_logs( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'igpr_email_logs';

		$page = isset( $request['page'] ) ? absint( $request['page'] ) : 1;
		$per_page = 10;
		$offset = ( $page - 1 ) * $per_page;

		// Check if table exists.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name;

		if ( ! $table_exists ) {
			return rest_ensure_response(
				array(
					'logs'       => array(),
					'totalPages' => 0,
				)
			);
		}

		$logs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);

		$total_logs = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
		$total_pages = ceil( $total_logs / $per_page );

		return rest_ensure_response(
			array(
				'logs'       => $logs,
				'totalPages' => $total_pages,
			)
		);
	}

	/**
	 * Get settings.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function get_settings() {
		$settings = $this->get_plugin_settings();
		return rest_ensure_response( $settings );
	}

	/**
	 * Update settings.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response object.
	 */
	public function update_settings( $request ) {
		$settings = $request->get_json_params();
		$sanitized = $this->sanitize_settings( $settings );

		update_option( 'igpr_settings', $sanitized );

		return rest_ensure_response(
			array(
				'success'  => true,
				'message'  => __( 'Settings saved successfully.', 'instant-guest-post-request' ),
				'settings' => $sanitized,
			)
		);
	}

	/**
	 * Get plugin settings.
	 *
	 * @return array Settings.
	 */
	private function get_plugin_settings() {
		$settings = get_option( 'igpr_settings', array() );
		return wp_parse_args( $settings, $this->default_settings );
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Settings input.
	 * @return array Sanitized settings.
	 */
	private function sanitize_settings( $input ) {
		$sanitized = array();

		// Boolean settings
		$boolean_settings = array(
			'enable_admin_notifications',
			'enable_author_notifications',
			'enable_featured_image',
		);

		foreach ( $boolean_settings as $key ) {
			$sanitized[ $key ] = isset( $input[ $key ] ) ? (bool) $input[ $key ] : false;
		}

		// Text settings
		$text_settings = array(
			'admin_email',
			'admin_email_subject',
			'author_email_subject',
			'approval_email_subject',
			'rejection_email_subject',
			'form_title',
			'success_message',
		);

		foreach ( $text_settings as $key ) {
			$sanitized[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
		}

		// Textarea settings
		$textarea_settings = array(
			'author_email_template',
			'approval_email_template',
			'rejection_email_template',
		);

		foreach ( $textarea_settings as $key ) {
			$sanitized[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( $input[ $key ] ) : '';
		}

		// Array settings
		if ( isset( $input['required_fields'] ) && is_array( $input['required_fields'] ) ) {
			$sanitized['required_fields'] = array_map( 'sanitize_text_field', $input['required_fields'] );
		} else {
			$sanitized['required_fields'] = $this->default_settings['required_fields'];
		}

		// Numeric settings
		$sanitized['max_upload_size'] = isset( $input['max_upload_size'] ) ? absint( $input['max_upload_size'] ) : $this->default_settings['max_upload_size'];
		if ( $sanitized['max_upload_size'] < 1 ) {
			$sanitized['max_upload_size'] = 1;
		} elseif ( $sanitized['max_upload_size'] > 10 ) {
			$sanitized['max_upload_size'] = 10;
		}

		return $sanitized;
	}
}