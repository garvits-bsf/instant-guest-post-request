<?php
/**
 * Autoloader for the Instant Guest Post Request plugin.
 *
 * @package Instant_Guest_Post_Request
 */

spl_autoload_register( function( $class ) {
	// Project-specific namespace prefix.
	$prefix = 'IGPR\\';

	// Base directory for the namespace prefix.
	$base_dir = plugin_dir_path( dirname( __FILE__ ) );

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		// No, move to the next registered autoloader.
		return;
	}

	// Get the relative class name.
	$relative_class = substr( $class, $len );

	// Replace namespace separators with directory separators in the relative class name.
	$file = $base_dir . 'includes/class-' . strtolower( $relative_class ) . '.php';

	// If the file exists, require it.
	if ( file_exists( $file ) ) {
		require $file;
	}
} );