#!/usr/bin/php
<?php

define('LIB_PATH', realpath(__DIR__ . "/../src/php"));
define('RESOURCES_PATH', realpath(__DIR__ . "/../src/resources"));
define('CONFIG_PATH', realpath(__DIR__ . "/../config"));
define('UPLOAD_PATH', realpath(__DIR__ . "/../upload"));

if (!LIB_PATH || !RESOURCES_PATH || !CONFIG_PATH || !UPLOAD_PATH) {
    echo "Invalid root configuration\n.";
    exit(1);
}

if (!is_dir(UPLOAD_PATH)) {
    echo "ERROR: Upload dir not found at ../upload. ABORTING.";
	exit(2);
}

if (count($argv) != 3 || ($argv[1] != "release" && $argv[1] != "snapshot")) {
    printUsageAndExit();
}

use Syncany\Api\Config\Config;
use Syncany\Api\Util\RepreproUtil;
use Syncany\Api\Model\TempFile;

try {
    $codename = $argv[1];
    $debFile = new TempFile(realpath(UPLOAD_PATH . "/" . $argv[2])); // Makes sure that file is in upload folder!

    Config::load();
    RepreproUtil::call($codename, $debFile);
}
catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(4);
}

function printUsageAndExit() {
	echo "repropro-wrapper is a wrapper for the APT archive tool reprepro. It is called by the\n";
	echo "Syncany API via sudo. To ensure that only allowed calls are let through, this wrapper\n";
	echo "has to be used. All paths are relative to the 'upload' folder (located at ../upload).\n";
	echo "\n";
	echo "Syntax:\n";
	echo "  reprepro-wrapper (release|snapshot) <relative-deb-path>\n";
	echo "\n";
	echo "Example:\n";
	echo "  reprepro-wrapper release plugins/sftp/deb/1425225412/syncany-plugin-sftp-0.4.2.deb\n";
	echo "\n";

	exit(3);
}

function __autoload($class)
{
    require_once(LIB_PATH . '/' . str_replace('\\', '/', $class) . ".php");
}
