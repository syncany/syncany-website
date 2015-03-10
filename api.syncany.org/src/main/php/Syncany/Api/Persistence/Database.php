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
