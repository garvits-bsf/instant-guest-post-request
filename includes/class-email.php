<?php
namespace IGPR;

/**
 * Email utility functions.
 */
class Email {
    /**
     * Send notification to admin about a new submission.
     *
     * @param int $post_id Post ID.
     */
    public static function send_admin_notification( $post_id ) {
        $post = get_post( $post_id );
        $token = wp_create_nonce( 'igpr_action_' . $post_id );

        $approve = add_query_arg(
            array(
                'post'  => $post_id,
                'act'   => 'approve',
                'token' => $token,
            ),
            site_url( '/igpr-action/' )
        );

        $reject = add_query_arg(
            array(
                'post'  => $post_id,
                'act'   => 'reject',
                'token' => $token,
            ),
            site_url( '/igpr-action/' )
        );

        $message = sprintf(
            "Title: %s\nPreview: %s\nApprove: %s\nReject: %s",
            $post->post_title,
            get_preview_post_link( $post ),
            $approve,
            $reject
        );

        wp_mail( get_option( 'admin_email' ), 'New guest post submission', $message );
    }

    /**
     * Send autoreply to submitter if enabled.
     *
     * @param int    $post_id Post ID.
     * @param string $status  approved|rejected.
     */
    public static function send_autoreply( $post_id, $status ) {
        if ( ! get_option( 'igpr_autoreply' ) ) {
            return;
        }

        $email = get_post_meta( $post_id, 'igpr_author_email', true );
        if ( ! $email ) {
            return;
        }

        if ( 'approved' === $status ) {
            $body = get_option( 'igpr_autoreply_tpl_approved', 'Your post is approved.' );
        } else {
            $body = get_option( 'igpr_autoreply_tpl_rejected', 'Your post has been rejected.' );
        }

        wp_mail( $email, 'Guest post update', $body );
    }
}
