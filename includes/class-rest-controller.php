<?php
namespace IGPR;

use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

/**
 * REST controller for guest post submissions.
 */
class Rest_Controller {
    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST routes.
     */
    public function register_routes() {
        register_rest_route(
            'igpr/v1',
            '/submit',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'handle_submission' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            'igpr/v1',
            '/settings',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_settings' ),
                'permission_callback' => array( $this, 'settings_permissions' ),
            )
        );

        register_rest_route(
            'igpr/v1',
            '/settings',
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_settings' ),
                'permission_callback' => array( $this, 'settings_permissions' ),
            )
        );
    }

    /**
     * Handle guest post submission.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_submission( WP_REST_Request $request ) {
        $params    = $request->get_json_params();
        $title     = sanitize_text_field( $params['title'] ?? '' );
        $content   = wp_kses_post( $params['content'] ?? '' );
        $name      = sanitize_text_field( $params['authorName'] ?? '' );
        $email     = sanitize_email( $params['authorEmail'] ?? '' );
        $bio       = sanitize_textarea_field( $params['authorBio'] ?? '' );
        $nonce     = $params['nonce'] ?? '';
        $honeypot  = $params['honeypot'] ?? '';
        $image_src = $params['featuredImage'] ?? '';

        if ( ! wp_verify_nonce( $nonce, 'igpr_form' ) || ! empty( $honeypot ) ) {
            return new WP_Error( 'invalid_nonce', 'Invalid submission.' );
        }

        $post_id = wp_insert_post(
            array(
                'post_type'   => 'post',
                'post_status' => 'pending',
                'post_title'  => $title,
                'post_content'=> $content,
            )
        );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        update_post_meta( $post_id, 'igpr_author_name', $name );
        update_post_meta( $post_id, 'igpr_author_email', $email );
        update_post_meta( $post_id, 'igpr_author_bio', $bio );

        if ( $image_src ) {
            $attachment_id = media_sideload_image( $image_src, $post_id, null, 'id' );
            if ( ! is_wp_error( $attachment_id ) ) {
                set_post_thumbnail( $post_id, $attachment_id );
            }
        }

        Email::send_admin_notification( $post_id );

        return new WP_REST_Response( array( 'post_id' => $post_id ), 201 );
    }

    /**
     * Check permissions for settings routes.
     *
     * @return bool
     */
    public function settings_permissions() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Get plugin settings.
     *
     * @return WP_REST_Response
     */
    public function get_settings() {
        $data = array(
            'igpr_autoreply'              => (bool) get_option( 'igpr_autoreply', false ),
            'igpr_autoreply_tpl_approved' => get_option( 'igpr_autoreply_tpl_approved', '' ),
            'igpr_autoreply_tpl_rejected' => get_option( 'igpr_autoreply_tpl_rejected', '' ),
        );

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Update plugin settings.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function update_settings( WP_REST_Request $request ) {
        $params = $request->get_json_params();

        update_option( 'igpr_autoreply', ! empty( $params['igpr_autoreply'] ) );
        update_option( 'igpr_autoreply_tpl_approved', sanitize_textarea_field( $params['igpr_autoreply_tpl_approved'] ?? '' ) );
        update_option( 'igpr_autoreply_tpl_rejected', sanitize_textarea_field( $params['igpr_autoreply_tpl_rejected'] ?? '' ) );

        return new WP_REST_Response( array( 'success' => true ), 200 );
    }
}
