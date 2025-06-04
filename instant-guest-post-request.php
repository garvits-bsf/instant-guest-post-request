<?php
/**
 * Plugin Name: Instant Guest Post Request
 * Description: Allow visitors to submit guest post requests that can be reviewed by admins.
 * Version: 0.1.0
 * Author: BSF AI Hackathon
 * Text Domain: instant-guest-post-request
 *
 * @package Instant_Guest_Post_Request
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'IGPR_VERSION', '0.1.0' );
define( 'IGPR_FILE', __FILE__ );
define( 'IGPR_DIR', plugin_dir_path( __FILE__ ) );
define( 'IGPR_URL', plugin_dir_url( __FILE__ ) );

// Include class files directly.
require_once IGPR_DIR . 'includes/class-admin.php';
require_once IGPR_DIR . 'includes/class-frontend.php';
require_once IGPR_DIR . 'includes/class-rest-api.php';
require_once IGPR_DIR . 'includes/class-ajax-handler.php';

// Initialize the plugin.
function igpr_init() {
	// Load admin functionality.
	if ( is_admin() ) {
		new IGPR\Admin();
	}

	// Load frontend functionality.
	new IGPR\Frontend();
	
	// Initialize REST API.
	new IGPR\REST_API();
	
	// Initialize AJAX handler.
	new IGPR\AJAX_Handler();
}
add_action( 'plugins_loaded', 'igpr_init' );

/**
 * Plugin activation hook.
 */
function igpr_activate() {
	// Create email logs table.
	global $wpdb;
	$table_name = $wpdb->prefix . 'igpr_email_logs';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
register_activation_hook( __FILE__, 'igpr_activate' );