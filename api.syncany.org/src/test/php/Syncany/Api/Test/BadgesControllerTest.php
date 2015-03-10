<?php

namespace Syncany\Api\Test;

use Syncany\Api\Controller\BadgesController;

class BadgesControllerTest extends \PHPUnit_Framework_TestCase {
    public function testBadgeCoverage()
    {
        $methodArgs = array();
        $requestArgs = array();

        $controller = new BadgesController("badges");
        $controller->getCoverage($methodArgs, $requestArgs);
    }
}
