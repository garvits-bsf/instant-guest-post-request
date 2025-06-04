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

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'IGPR_VERSION', '0.1.0' );
define( 'IGPR_FILE', __FILE__ );
define( 'IGPR_DIR', plugin_dir_path( __FILE__ ) );
define( 'IGPR_URL', plugin_dir_url( __FILE__ ) );

require_once IGPR_DIR . 'includes/class-admin.php';
require_once IGPR_DIR . 'includes/class-frontend.php';
require_once IGPR_DIR . 'includes/class-rest-controller.php';
require_once IGPR_DIR . 'includes/class-action-handler.php';
require_once IGPR_DIR . 'includes/class-email.php';
require_once IGPR_DIR . 'includes/class-ajax-handler.php';

function igpr_init() {
    if ( is_admin() ) {
        new IGPR\Admin();
    }

    new IGPR\Frontend();
    new IGPR\Rest_Controller();
    new IGPR\Action_Handler();

    	
	// Initialize AJAX handler.
	new IGPR\AJAX_Handler();
}
add_action( 'plugins_loaded', 'igpr_init' );

function igpr_register_settings() {
    register_setting( 'igpr_settings', 'igpr_autoreply' );
    register_setting( 'igpr_settings', 'igpr_autoreply_tpl_approved' );
    register_setting( 'igpr_settings', 'igpr_autoreply_tpl_rejected' );
}
add_action( 'admin_init', 'igpr_register_settings' );
