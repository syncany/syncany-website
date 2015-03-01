<?php

define('LIB_PATH', realpath(__DIR__ . "/../../src/php"));
define('RESOURCES_PATH', realpath(__DIR__ . "/../../src/resources"));
define('CONFIG_PATH', realpath(__DIR__ . "/../../config"));
define('UPLOAD_PATH', realpath(__DIR__ . "/../../upload"));

function __autoload($class)
{
    require_once(LIB_PATH . '/' . str_replace('\\', '/', $class) . ".php");
}

use Syncany\Api\Dispatcher\RequestDispatcher;
use Syncany\Api\Exception\Http\HttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Exception\Http\BadRequestHttpException;

try {
    if (!isset($_GET['request'])) {
        throw new BadRequestHttpException("Invalid request, param 'request' missing");
    }

    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $request = $_GET['request'];

    unset($_GET['request']);

    RequestDispatcher::dispatch($requestMethod, $request);
} catch (HttpException $e) {
    $e->sendErrorHeadersAndExit();
} catch (Exception $e) {
    $wrappedError = new ServerErrorHttpException($e->getMessage());
    $wrappedError->sendErrorHeadersAndExit();
}

