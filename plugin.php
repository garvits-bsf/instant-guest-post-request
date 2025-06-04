<?php
/**
 * Plugin Name: Instant Guest Post Request
 * Description: Allows visitors to submit guest post requests easily.
 * Version: 0.1.0
 * Author: Example Author
 * Text Domain: instant-guest-post-request
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

require_once __DIR__ . '/includes/autoload.php';

// Initialize plugin.
\InstantGuestPostRequest\Plugin::init();
\InstantGuestPostRequest\FrontEnd::init();
