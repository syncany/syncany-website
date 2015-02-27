<?php

define('LIB_PATH', dirname(__FILE__) . "/../../src/php");
define('RESOURCES_PATH', dirname(__FILE__) . "/../../src/resources");
define('CONFIG_PATH', dirname(__FILE__) . "/../../config");

function __autoload($class)
{
    include(LIB_PATH . '/' . str_replace('\\', '/', $class) . ".php");
}

use Syncany\Api\Dispatcher\RequestDispatcher;
use Syncany\Api\Exception\Http\HttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;

try {
    RequestDispatcher::dispatch($_GET['request']);
} catch (HttpException $e) {
    $e->sendErrorHeadersAndExit();
} catch (Exception $e) {
    $wrappedError = new ServerErrorHttpException($e->getMessage());
    $wrappedError->sendErrorHeadersAndExit();
}

