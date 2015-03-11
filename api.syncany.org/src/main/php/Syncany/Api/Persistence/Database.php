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

/**
 * Helper class for database access to create {@link PDO} objects and/or
 * create PDO statements from SQL resources.
 *
 * <p>The class uses the {@link FileUtil} utility to load the file
 * 'database/database.properties' and uses context-specific credentials
 * to either create a PDO object or a statement.
 *
 * @author Philipp Heckel <philipp.heckel@gmail.com>
 */
class Database
{
    const CONFIG_CONTEXT = "database";
    const CONFIG_NAME = "database";

    /**
     * Creates a PDO object from a given database config context. A config
     * context is a prefix used to identify the database credentials in the
     * database properties file.
     *
     * @param $configContext Database config context/prefix, e.g. plugins-write, plugins-read, ...
     * @return \PDO The desired PDO object
     * @throws ConfigException If the given config context is invalid or does not exist.
     */
    public static function createInstance($configContext)
    {
        $config = self::readConfigFile($configContext);

        try {
            return new \PDO($config['dsn'], $config['user'], $config['pass'], array());
        } catch (\PDOException $e) {
            throw new ConfigException("Cannot connect to database. Invalid credentials or database down.");
        }
    }

    /**
     * Retrieves a SQL file from the resources and creates a PDO statement from a given config context,
     * a namespace context and a SQL file identifier. A config context is a prefix used to identify
     * the database credentials in the database properties file. A namespace context is used to identify
     * the folder in which the desired SQL resource file resides. And the SQL file parameter defines the
     * exact file to retrieve from the resources.
     *
     * <p>Example:
     * <pre>
     *   $statement = Database::prepareStatementFromResource("links-read", 'Syncany\Api\Controller', "links.select-link.sql");
     *
     *   // Retrieves file from RESOURCES_DIR . 'Syncany/Api/Controller/links.select-link.sql'
     *   // and creates a PDOStatement object from it.
     * </pre>
     *
     * @param $configContext Database config context/prefix, e.g. plugins-write, plugins-read, ...
     * @param $namespaceContext Namespace context, e.g. 'Syncany\Api\Controller'
     * @param $sqlFile Specific SQL file, e.g. 'plugins.getById.sql'
     * @return \PDOStatement The desired PDO statement
     * @throws ConfigException If any of the given parameters is incorrect/invalid.
     */
    public static function prepareStatementFromResource($configContext, $namespaceContext, $sqlFile)
    {
        $database = Database::createInstance($configContext);
        $sqlQuery = FileUtil::readResourceFile($namespaceContext, $sqlFile);

        return $database->prepare($sqlQuery);
    }

    private static function readConfigFile($configName)
    {
        $config = FileUtil::readPropertiesFile(self::CONFIG_CONTEXT, self::CONFIG_NAME);

        if (!$config) {
            throw new ConfigException("Invalid config file. Parsing failed.");
        }

        if (!isset($config[$configName . '.dsn']) || !isset($config[$configName . '.user']) || !isset($config[$configName . '.pass'])) {
            throw new ConfigException("Invalid config file. Mandatory properties are 'dsn', 'user' and 'pass'.");
        }

        return array(
            'dsn' => $config[$configName . '.dsn'],
            'user' => $config[$configName . '.user'],
            'pass' => $config[$configName . '.pass']
        );
    }
}
