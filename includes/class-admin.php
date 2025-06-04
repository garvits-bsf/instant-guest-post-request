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