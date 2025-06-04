<?php
use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/instant-guest-post-request.php';

final class ActionHandlerTest extends TestCase {
    public function test_handler_class_exists() {
        $this->assertTrue( class_exists( 'IGPR\\Action_Handler' ) );
    }
}
