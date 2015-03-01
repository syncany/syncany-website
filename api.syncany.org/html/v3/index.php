<?php

define('LIB_PATH', realpath(__DIR__ . "/../../src/php"));
define('RESOURCES_PATH', realpath(__DIR__ . "/../../src/resources"));
define('CONFIG_PATH', realpath(__DIR__ . "/../../config"));
define('UPLOAD_PATH', realpath(__DIR__ . "/../../upload"));

if (!LIB_PATH || !RESOURCES_PATH || !CONFIG_PATH || !UPLOAD_PATH) {
    header("HTTP/1.1 500 Server Error");
    header("X-Syncany-Reason: Invalid root configuration");

    exit;
}

function __autoload($class)
{
    require_once(LIB_PATH . '/' . str_replace('\\', '/', $class) . ".php");
}

use Syncany\Api\Config\Config;
use Syncany\Api\Dispatcher\RequestDispatcher;

Config::load();
RequestDispatcher::dispatch();
