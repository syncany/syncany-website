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

namespace Syncany\Api\Util;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\ConfigException;

/**
 * Logging class used throughout the application to log to a
 * certain log file, and to rotate the logs.
 *
 * <p>This class can only be used if bootstrapping is done. In
 * particular, the constant LOG_PATH needs to be set.
 *
 * @author Philipp Heckel <philipp.heckel@gmail.com>
 */
class Log
{
    private static $initialized = false;
    private static $file;

    public static function info($class, $method, $message, $args = null)
    {
        self::log('INFO', $class, $method, $message, $args);
    }

    public static function warning($class, $method, $message, $args = null)
    {
        self::log('WARNING', $class, $method, $message, $args);
    }

    public static function error($class, $method, $message, $args = null, \Exception $exception = null)
    {
        self::log('ERROR', $class, $method, $message, $args, $exception);
    }

    public static function debug($class, $method, $message, $args = null, \Exception $exception = null)
    {
        self::log('DEBUG', $class, $method, $message, $args, $exception);
    }

    public static function init()
    {
        if (self::$initialized) {
            return;
        }

        self::initLogFile();

        self::$initialized = true;
    }

    private static function log($logLevel, $class, $method, $message, array $args = null, \Exception $exception = null)
    {
        self::init();

        $logLevel = substr($logLevel, 0, 4);
        $datetime = @date("Y-m-d H:i:s");
        $ipAddr = $_SERVER['REMOTE_ADDR'];
        $formattedMessage = StringUtil::replace($message, $args);

        if ($class) {
            $reflectionClass = new \ReflectionClass($class);
            $class = substr($reflectionClass->getShortName(), 0, 15);
        }

        if ($method) {
            if (strpos($method, "::") !== false) {
                $method = substr($method, strpos($method, "::") + 2, 15);
            } else {
                $method = substr($method, 0, 20);
            }
        }

        $line = sprintf("%-10s | %-15s | %-15s | %-15s | %-4s | %s\n", $datetime, $ipAddr, $class, $method, $logLevel, $formattedMessage);

        if ($exception) {
            $line .= "\nException: \n" . $exception->getTraceAsString() . "\n";
        }

        $fd = fopen(self::$file, "a");
        fputs($fd, $line);
        fclose($fd);
    }

    private static function initLogFile()
    {
        if (!defined('LOG_PATH')) {
            throw new ConfigException("Log path not set via LOG_PATH.");
        }

        if (!is_writable(LOG_PATH)) {
            throw new ConfigException("Cannot write to log path. Invalid permissions.");
        }

        self::$file = LOG_PATH . "/api.log";
    }
}
