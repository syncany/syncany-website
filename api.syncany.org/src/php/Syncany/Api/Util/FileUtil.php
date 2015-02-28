<?php

namespace Syncany\Api\Util;

use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Model\FileHandle;

class FileUtil
{
	public static function readPropertiesFile($configContext, $configName)
	{
		$propertiesFile = self::getConfigFileName($configContext, $configName);

		if (!file_exists($propertiesFile)) {
			throw new ConfigException("Properties file does not exist.");
		}

		$properties = array();
		$lines = explode("\n", file_get_contents($propertiesFile));

		foreach ($lines as $line) {
			if (!preg_match('/^\s*#/', $line) && preg_match('/^([^=]+)\s*=\s*(.*)$/', $line, $m)) {
				$properties[trim($m[1])] = trim($m[2]);
			}
		}

		return $properties;
	}

	public static function readResourceFile($namespace, $relativePath)
	{
		if (!defined('RESOURCES_PATH')) {
			throw new ConfigException("Resources path not set via RESOURCES_PATH.");
		}

		$absoluteFilePath = RESOURCES_PATH . '/' . str_replace('\\', '/', $namespace) . '/' . $relativePath;

		if (!file_exists($absoluteFilePath)) {
			throw new ConfigException("File file does not exist: " . $absoluteFilePath);
		}

		return file_get_contents($absoluteFilePath);
	}

	private static function getConfigFileName($configContext, $configName)
	{
		if (!defined('CONFIG_PATH')) {
			throw new ConfigException("Config path not set via CONFIG_PATH.");
		}

		if (!preg_match('/^[-_a-z0-9]+$/', $configContext) || !preg_match('/^[-_a-z0-9]+$/', $configName)) {
			throw new ConfigException("Invalid config context passed. Illegal characters.");
		}

		$configFile = CONFIG_PATH . "/" . $configContext . "/" . $configName . ".properties";

		if (!file_exists($configFile)) {
			throw new ConfigException("Config file not found for context $configContext.");
		}

		return $configFile;
	}

	public static function saveToTemp($uploadContext, FileHandle $file)
	{
		if (!defined('UPLOAD_PATH')) {
			throw new ConfigException("Upload path not set via CONFIG_PATH.");
		}

		if (!preg_match('/^[-_\/a-z0-9]+$/', $uploadContext)) {
			throw new ConfigException("Invalid upload context passed. Illegal characters.");
		}

		$tempDir = UPLOAD_PATH . "/" . $uploadContext . "/" . time();

		if (!mkdir($tempDir, 0777, true)) {
			throw new ConfigException("Cannot create upload directory");
		}

		$tempFileName = $tempDir . "/" . StringUtil::generateRandomString(5);
		$tempFile = new FileHandle(fopen($tempFileName, "w"));

		while (!feof($file->getHandle())) {
			$buffer = fread($file->getHandle(), 8192);
			fwrite($tempFile->getHandle(), $buffer);
		}

		fclose($file->getHandle());
		fclose($tempFile->getHandle());
	}
}
