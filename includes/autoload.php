<?php
namespace InstantGuestPostRequest;

spl_autoload_register( function( $class ) {
    if ( strpos( $class, __NAMESPACE__ ) !== 0 ) {
        return;
    }

    $filename = __DIR__ . '/' . strtolower( str_replace( [ __NAMESPACE__ . '\\', '_' ], [ '', '-' ], $class ) ) . '.php';

    if ( file_exists( $filename ) ) {
        require_once $filename;
    }
} );
