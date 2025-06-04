<?php
use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/instant-guest-post-request.php';

final class RestSubmitTest extends TestCase {
    public function test_controller_class_exists() {
        $this->assertTrue( class_exists( 'IGPR\\Rest_Controller' ) );
    }
}
