<?php

namespace Syncany\Api\Test;

use Syncany\Api\Dispatcher\RequestDispatcher;

class BadgesControllerTest extends \PHPUnit_Framework_TestCase {
    public function testBadgeCoverage()
    {
        $_GET['request'] = "/badges/coverage";
        RequestDispatcher::dispatch();
    }
}
