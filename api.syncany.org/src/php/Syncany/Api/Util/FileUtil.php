<?php

namespace Syncany\Api\Util;

use Syncany\Api\Exception\ConfigException;

class FileUtil
{
	public static function readPropertiesFile($propertiesFile)
	{
		if (!file_exists($propertiesFile)) {
			throw new ConfigException("Properties file does not exist.");
		}

		$properties = array();
		$lines = explode("\n", file_get_contents($propertiesFile));

		foreach ($lines as $line) {
			if (preg_match("/^([^=]+)\s*=\s*(.*)$/", $line, $m)) {
				$properties[trim($m[1])] = trim($m[2]);
			}
		}

		return $properties;
	}

	public static function readResourceFile($namespace, $relativePath)
	{
		$absoluteFilePath = RESOURCES_PATH . '/' . str_replace('\\', '/', $namespace) . '/' . $relativePath;

		if (!file_exists($absoluteFilePath)) {
			throw new ConfigException("File file does not exist: " . $absoluteFilePath);
		}

		return file_get_contents($absoluteFilePath);
	}
}
