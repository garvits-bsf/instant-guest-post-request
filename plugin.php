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

// Initialize the plugin.
function igpr_init() {
	// Load admin functionality.
	if ( is_admin() ) {
		new IGPR\Admin();
	}

	// Load frontend functionality.
	new IGPR\Frontend();
}
add_action( 'plugins_loaded', 'igpr_init' );