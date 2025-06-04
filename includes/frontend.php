<?php
namespace InstantGuestPostRequest;

class FrontEnd {
    public static function init() {
        add_shortcode( 'igpr_form', [ __CLASS__, 'render_form' ] );
        add_action( 'admin_post_nopriv_igpr_submit', [ __CLASS__, 'handle_submission' ] );
        add_action( 'admin_post_igpr_submit', [ __CLASS__, 'handle_submission' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_styles' ] );
    }

    public static function enqueue_styles() {
        wp_enqueue_style( 'igpr-front', plugins_url( '../front/css/front.css', __FILE__ ) );
    }

    public static function render_form() {
        if ( isset( $_GET['igpr_success'] ) ) {
            return '<div class="p-4 bg-green-100 text-green-800 rounded">' . esc_html__( "Thanks for your submission! We'll review and get back soon.", 'instant-guest-post-request' ) . '</div>';
        }

        $errors = [];
        if ( isset( $_GET['igpr_errors'] ) ) {
            $errors = explode( ',', sanitize_text_field( $_GET['igpr_errors'] ) );
        }

        ob_start();
        ?>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="max-w-xl mx-auto p-6 bg-white shadow rounded space-y-4">
            <input type="hidden" name="action" value="igpr_submit" />
            <?php wp_nonce_field( 'igpr_submit', 'igpr_nonce' ); ?>
            <div>
                <label class="block font-medium" for="igpr-name"><?php esc_html_e( 'Your Name', 'instant-guest-post-request' ); ?></label>
                <input class="mt-1 block w-full border rounded p-2" type="text" id="igpr-name" name="name" value="<?php echo isset( $_POST['name'] ) ? esc_attr( wp_unslash( $_POST['name'] ) ) : ''; ?>" required />
                <?php if ( in_array( 'name', $errors, true ) ) : ?>
                    <p class="text-red-600 text-sm mt-1"><?php esc_html_e( 'Name is required.', 'instant-guest-post-request' ); ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label class="block font-medium" for="igpr-email"><?php esc_html_e( 'Email', 'instant-guest-post-request' ); ?></label>
                <input class="mt-1 block w-full border rounded p-2" type="email" id="igpr-email" name="email" value="<?php echo isset( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required />
                <?php if ( in_array( 'email', $errors, true ) ) : ?>
                    <p class="text-red-600 text-sm mt-1"><?php esc_html_e( 'Valid email is required.', 'instant-guest-post-request' ); ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label class="block font-medium" for="igpr-content"><?php esc_html_e( 'Post Content', 'instant-guest-post-request' ); ?></label>
                <textarea class="mt-1 block w-full border rounded p-2" id="igpr-content" name="content" rows="6" required><?php echo isset( $_POST['content'] ) ? esc_textarea( wp_unslash( $_POST['content'] ) ) : ''; ?></textarea>
                <?php if ( in_array( 'content', $errors, true ) ) : ?>
                    <p class="text-red-600 text-sm mt-1"><?php esc_html_e( 'Content is required.', 'instant-guest-post-request' ); ?></p>
                <?php endif; ?>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded"><?php esc_html_e( 'Submit', 'instant-guest-post-request' ); ?></button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function handle_submission() {
        if ( ! isset( $_POST['igpr_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['igpr_nonce'] ) ), 'igpr_submit' ) ) {
            wp_die( esc_html__( 'Invalid nonce', 'instant-guest-post-request' ) );
        }

        $errors = [];
        $name = isset( $_POST['name'] ) ? trim( wp_unslash( $_POST['name'] ) ) : '';
        $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $content = isset( $_POST['content'] ) ? trim( wp_unslash( $_POST['content'] ) ) : '';

        if ( '' === $name ) {
            $errors[] = 'name';
        }
        if ( '' === $email || ! is_email( $email ) ) {
            $errors[] = 'email';
        }
        if ( '' === $content ) {
            $errors[] = 'content';
        }

        if ( $errors ) {
            $redirect = add_query_arg( 'igpr_errors', implode( ',', $errors ), wp_get_referer() );
            wp_safe_redirect( $redirect );
            exit;
        }

        wp_insert_post( [
            'post_type'   => 'igpr_submission',
            'post_status' => 'pending',
            'post_title'  => $name . ' - ' . wp_date( 'Y-m-d H:i' ),
            'post_content'=> $content,
            'meta_input'  => [ 'email' => $email ],
        ] );

        $redirect = add_query_arg( 'igpr_success', '1', wp_get_referer() );
        wp_safe_redirect( $redirect );
        exit;
    }
}
