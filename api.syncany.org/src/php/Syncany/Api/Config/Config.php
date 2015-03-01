<?php

namespace Syncany\Api\Config;

use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Util\FileUtil;

class Config
{
    private static $config;

    public static function get($id)
    {
        if (!self::$config) {
            throw new ConfigException("Config not loaded or invalid");
        }

        if (!isset(self::$config[$id])) {
            throw new ConfigException("Value does not exist in global config instance");
        }

        return self::$config[$id];
    }

    public static function load()
    {
        self::$config = FileUtil::readPropertiesFile("config", "config");
    }
}