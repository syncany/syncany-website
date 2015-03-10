<?php

namespace Syncany\Api\Test;

use Syncany\Api\Dispatcher\RequestDispatcher;

class PluginsControllerTest extends \PHPUnit_Framework_TestCase {
    public function testPluginsList()
    {
        $_GET['request'] = "/plugins";
        RequestDispatcher::dispatch();
    }
}
