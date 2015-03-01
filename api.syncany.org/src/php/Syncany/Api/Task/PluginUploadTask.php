<?php

namespace Syncany\Api\Task;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Util\FileUtil;

class PluginUploadTask
{
	private $pluginId;
	private $fileHandle;
	private $checksum;

	public function __construct($pluginId, FileHandle $fileHandle, $checksum)
	{
		$this->pluginId = $pluginId;
		$this->fileHandle = $fileHandle;
		$this->checksum = $checksum;
	}

	public function execute()
	{
		$tempDirContext = "plugins/" . $this->pluginId;

		$tempDir = FileUtil::createTempDir($tempDirContext);
		$tempFile = FileUtil::writeToTempFile($this->fileHandle, $tempDir, ".zip");

		$this->validateChecksum($tempFile);
		$this->extractZipArchive($tempFile, $tempDir);

		
	}

	private function validateChecksum(TempFile $tempFile)
	{
		$actualChecksum = FileUtil::calculateChecksum($tempFile);

		if ($this->checksum != $actualChecksum) {
			throw new BadRequestHttpException("Invalid checksum of uploaded file.");
		}
	}

	private function extractZipArchive($tempFile, $tempDir)
	{
		$zip = new \ZipArchive();

		if ($zip->open($tempFile->getFile()) === true) {
			$zip->extractTo($tempDir->getFile() . "/");
			$zip->close();
		} else {
			throw new ServerErrorHttpException("Cannot extract ZIP archive.");
		}
	}
}
