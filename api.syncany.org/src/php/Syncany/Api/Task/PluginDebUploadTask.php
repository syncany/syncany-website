<?php

namespace Syncany\Api\Task;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Persistence\Database;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\StringUtil;

class PluginDebUploadTask extends UploadTask
{
	const REPREPRO_CALL_FORMAT = 'sudo -u debarchive {wrapper} {codename} "{debfile}"';

	private $pathPluginDist;
	private $pluginId;

	public function __construct(FileHandle $fileHandle, $fileName, $checksum, $snapshot, $pluginId)
	{
		parent::__construct($fileHandle, $fileName, $checksum, $snapshot);

		$this->pathPluginDist = Config::get("paths.plugindist");
		$this->pluginId = $pluginId;
	}

	public function execute()
	{
		$tempDirContext = "plugins/" . $this->pluginId . "/deb";

		$tempDir = FileUtil::createTempDir($tempDirContext);
		$tempFile = FileUtil::writeToTempFile($this->fileHandle, $tempDir, ".deb");

		$this->validateChecksum($tempFile);
		$this->addToAptArchive($tempFile);

		//$targetFile = $this->moveFile($tempFile);

	}

	private function moveFile(TempFile $tempFile)
	{
		$pluginTargetFolder = $this->getTargetFolder();
		$pluginTargetJarFile = $pluginTargetFolder . "/" . basename($this->fileName);

		if (!file_exists($pluginTargetFolder)) {
			if (!mkdir($pluginTargetFolder, 0755, true)) {
				throw new ServerErrorHttpException("Cannot create target plugin folder");
			}
		}

		if (!is_writable($pluginTargetFolder)) {
			throw new ServerErrorHttpException("Cannot write to target plugin folder");
		}

		if (!rename($tempFile->getFile(), $pluginTargetJarFile)) {
			throw new ServerErrorHttpException("Cannot move JAR file");
		}

		return $pluginTargetJarFile;
	}

	private function addToAptArchive(TempFile $debFile)
	{
		$wrapperPath = Config::get("paths.apt.wrapper");
		$codename = ($this->snapshot) ? "snapshot" : "release";

		$command = StringUtil::replace(self::REPREPRO_CALL_FORMAT, array(
			"wrapper" => $wrapperPath,
			"codename" => $codename,
			"debfile" => $debFile->getFile()
		));

		$output = array();
		$exitCode = -1;

		exec($command, $output, $exitCode);

		if ($exitCode != 0) {
			throw new ServerErrorHttpException("Calling reprepro-wrapper failed with exit code $exitCode.");
		}
	}

}
