<?php

namespace Syncany\Api\Util;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\ConfigException;

class Log
{
    private static $initialized = false;
    private static $file;

    public static function info($class, $message, array $args = array())
    {
        self::log('INFO', $class, $message, $args);
    }

    public static function warning($class, $message, array $args = array())
    {
        self::log('WARNING', $class, $message, $args);
    }

    public static function error($class, $message, array $args = array())
    {
        self::log('ERROR', $class, $message, $args);
    }

    public static function debug($class, $message, array $args = array())
    {
        self::log('DEBUG', $class, $message, $args);
    }

    public static function init()
    {
        if (self::$initialized) {
            return;
        }

        self::initLogFile();
        //self::rotateIfNeeded(); // This let's Apache segfault :-(

        self::$initialized = true;
    }

    private static function log($logLevel, $class, $message, array $args)
    {
        self::init();

        $datetime = @date("Y-m-d H:i:s");
        $ipAddr = $_SERVER['REMOTE_ADDR'];
        $formattedMessage = StringUtil::replace($message, $args);

        if ($class) {
            $reflectionClass = new \ReflectionClass($class);
            $class = $reflectionClass->getShortName();
        }

        $line = sprintf("%-10s | %-15s | %-20s | %-6s | %s\n", $datetime, $ipAddr, $class, $logLevel, $formattedMessage);

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

    private static function rotateIfNeeded()
    {
        $maxSize = Config::get("log.max-size");
        $maxCount = Config::get("log.max-count");

        $actualSize = @filesize(self::$file);

        if ($actualSize > $maxSize) {
            $oldestLogFile = self::$file . "." . $maxCount;
            @unlink($oldestLogFile);

            $currentIndex = $maxCount - 1;

            while ($currentIndex > 1) {
                $currentLogFile = self::$file . "." . $currentIndex;

                $previousIndex = $currentIndex - 1;
                $previousLogFile = self::$file . "." . $previousIndex;

                @rename($previousLogFile, $currentLogFile);

                $currentIndex--;
            }

            self::info(__CLASS__, "Log rotated.");
        }
    }
}
