<?php

namespace Syncany\Api\Util;

use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;

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

	public static function createTempDir($uploadContext)
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

		return new TempFile($tempDir);
	}

	public static function writeToTempFile(FileHandle $sourceFileInputStream, TempFile $targetDir, $suffix = "")
	{
		if (!preg_match('/^[.a-z0-9]*$/i', $suffix)) {
			throw new ConfigException("Invalid suffix passed. Illegal characters.");
		}


		$tempFile = new TempFile($targetDir->getFile() . "/" . StringUtil::generateRandomString(5) . $suffix);
		return self::writeToFile($sourceFileInputStream, $tempFile);
	}

	public static function writeToFile(FileHandle $sourceFileInputStream, TempFile $tempFile)
	{
		$tempFileHandle = new FileHandle(fopen($tempFile->getFile(), "w"));

		while (!feof($sourceFileInputStream->getHandle())) {
			$buffer = fread($sourceFileInputStream->getHandle(), 8192);
			fwrite($tempFileHandle->getHandle(), $buffer);
		}

		fclose($sourceFileInputStream->getHandle());
		fclose($tempFileHandle->getHandle());

		return $tempFile;
	}

	public static function calculateChecksum(TempFile $file)
	{
		if (!file_exists($file->getFile())) {
			throw new ConfigException("Cannot calculate checksum. File does not exist.");
		}

		return hash_file("sha256", $file->getFile());
	}
}
