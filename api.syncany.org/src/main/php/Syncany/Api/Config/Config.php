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

namespace Syncany\Api\Config;

use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Util\FileUtil;

/**
 * The config class retrieves configuration values from the central
 * config file in the config directory. It is located in CONFIG_PATH
 * in a file called config/config.properties.
 *
 * @author Philipp Heckel <philipp.heckel@gmail.com>
 */
class Config
{
    const CONFIG_CONTEXT = "config";
    const CONFIG_NAME = "config";

    private static $config;

    public static function load()
    {
        self::$config = FileUtil::readPropertiesFile(self::CONFIG_CONTEXT, self::CONFIG_NAME);
    }

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
}