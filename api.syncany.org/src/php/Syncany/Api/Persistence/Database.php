<?php

namespace Syncany\Api\Persistence;

use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Util\FileUtil;

class Database
{
	public static function createInstance($configContext)
	{
		$config = self::readConfigFile($configContext);

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

	private static function readConfigFile($configName)
	{
		$config = FileUtil::readPropertiesFile("database", $configName);

		if (!$config) {
			throw new ConfigException("Invalid config file. Parsing failed.");
		}

		if (!isset($config['dsn']) || !isset($config['user']) || !isset($config['pass'])) {
			throw new ConfigException("Invalid config file. Mandatory properties are 'dsn', 'user' and 'pass'.");
		}

		return $config;
	}
}
