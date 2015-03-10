<?php

define('LIB_PATH', realpath(__DIR__ . "/../../src/main/php"));
define('RESOURCES_PATH', realpath(__DIR__ . "/../../src/main/resources"));
define('CONFIG_PATH', realpath(__DIR__ . "/../../config"));
define('UPLOAD_PATH', realpath(__DIR__ . "/../../upload"));
define('LOG_PATH', realpath(__DIR__ . "/../../log"));
define('VENDOR_PATH', realpath(__DIR__ . "/../../vendor"));

if (!LIB_PATH || !RESOURCES_PATH || !CONFIG_PATH || !UPLOAD_PATH || !LOG_PATH || !VENDOR_PATH) {
    header("HTTP/1.1 500 Server Error");
    header("X-Syncany-Reason: Invalid root configuration");

    exit;
}

// Autoload
require_once VENDOR_PATH . '/autoload.php';

// Go!
use Syncany\Api\Config\Config;
use Syncany\Api\Util\Log;
use Syncany\Api\Dispatcher\RequestDispatcher;

Config::load();
Log::init();

RequestDispatcher::dispatch();