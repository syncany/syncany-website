<?php

/*
 * Syncany, www.syncany.org
 * Copyright (C) 2011-2015 Philipp C. Heckel <philipp.heckel@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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