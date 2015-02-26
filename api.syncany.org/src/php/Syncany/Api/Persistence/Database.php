<?php

namespace Syncany\Api\Persistence;

use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Util\FileUtil;

class Database
{
	public static function createInstance($configContext)
	{
		$configFile = self::getConfigFile($configContext);
		$config = self::readConfigFile($configFile);

		try {
			return new \PDO($config['dsn'], $config['user'], $config['pass'], array());
		} catch (\PDOException $e) {
			throw new ConfigException("Cannot connect to database. Invalid credentials or database down.");
		}
	}

	public static function prepareStatementFromResource($configContext, $namespaceContext, $sqlFile)
	{
		$database = Database::createInstance($configContext);
		$sqlQuery = FileUtil::readResourceFile($namespaceContext, $sqlFile);

		return $database->prepare($sqlQuery);
	}

	private static function getConfigFile($configContext)
	{
		if (!defined('CONFIG_PATH')) {
			throw new ConfigException("Config path not set via CONFIG_PATH.");
		}

		if (!preg_match('/^[-_a-z0-9]+$/', $configContext)) {
			throw new ConfigException("Invalid config context passed. Illegal characters.");
		}

		$configFile = CONFIG_PATH . "/database/" . $configContext . ".properties";

		if (!file_exists($configFile)) {
			throw new ConfigException("Config context file not found at $configFile.");
		}

		return $configFile;
	}

	private static function readConfigFile($configFile)
	{
		$config = FileUtil::readPropertiesFile($configFile);

		if (!$config) {
			throw new ConfigException("Invalid config file. Parsing failed.");
		}

		if (!isset($config['dsn']) || !isset($config['user']) || !isset($config['pass'])) {
			throw new ConfigException("Invalid config file. Mandatory properties are 'dsn', 'user' and 'pass'.");
		}

		return $config;
	}
}
