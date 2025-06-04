<?php
namespace InstantGuestPostRequest;

class Plugin {
    public static function init() {
        // Hooks registration.
        add_action( 'init', [ __CLASS__, 'register_post_type' ] );
        add_action( 'admin_menu', [ __CLASS__, 'register_admin_pages' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
    }

    public static function register_post_type() {
        register_post_type( 'igpr_submission', [
            'label' => 'Guest Post Submissions',
            'public' => false,
            'show_ui' => false,
            'supports' => [ 'title', 'editor', 'author' ],
        ] );
    }

    public static function register_admin_pages() {
        add_menu_page(
            __( 'Guest Post Requests', 'instant-guest-post-request' ),
            __( 'Guest Posts', 'instant-guest-post-request' ),
            'manage_options',
            'igpr-settings',
            [ __CLASS__, 'render_settings_page' ],
            'dashicons-admin-post'
        );

        add_submenu_page(
            'igpr-settings',
            __( 'Submissions', 'instant-guest-post-request' ),
            __( 'Submissions', 'instant-guest-post-request' ),
            'manage_options',
            'igpr-submissions',
            [ __CLASS__, 'render_submissions_page' ]
        );

        add_submenu_page(
            'igpr-settings',
            __( 'Email Logs', 'instant-guest-post-request' ),
            __( 'Email Logs', 'instant-guest-post-request' ),
            'manage_options',
            'igpr-email-logs',
            [ __CLASS__, 'render_email_logs_page' ]
        );
    }

    public static function render_settings_page() {
        echo '<div id="igpr-settings-root"></div>';
    }

    public static function render_submissions_page() {
        echo '<div id="igpr-submissions-root"></div>';
    }

    public static function render_email_logs_page() {
        echo '<div id="igpr-email-logs-root"></div>';
    }

    public static function enqueue_admin_assets() {
        wp_enqueue_script(
            'igpr-admin',
            plugins_url( '../admin/js/index.js', __FILE__ ),
            [ 'wp-element' ],
            null,
            true
        );
        wp_enqueue_style(
            'igpr-admin',
            plugins_url( '../admin/css/admin.css', __FILE__ )
        );
    }
}
