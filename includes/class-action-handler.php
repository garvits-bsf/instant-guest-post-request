<?php
namespace IGPR;

/**
 * Handle approval and rejection actions.
 */
class Action_Handler {
    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'maybe_handle' ) );
    }

    /**
     * Process approve/reject requests.
     */
    public function maybe_handle() {
        if ( strpos( $_SERVER['REQUEST_URI'], '/igpr-action/' ) === false ) {
            return;
        }

        $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
        $act     = isset( $_GET['act'] ) ? sanitize_key( $_GET['act'] ) : '';
        $token   = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

        if ( ! $post_id || ! $act || ! wp_verify_nonce( $token, 'igpr_action_' . $post_id ) ) {
            wp_die( 'Invalid action.' );
        }

        if ( 'approve' === $act ) {
            wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
            Email::send_autoreply( $post_id, 'approved' );
        } elseif ( 'reject' === $act ) {
            wp_trash_post( $post_id );
            Email::send_autoreply( $post_id, 'rejected' );
        }

        echo 'Action completed';
        exit;
    }
}
