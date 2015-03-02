<?php

namespace Syncany\Api\Task;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Util\FileUtil;

abstract class UploadTask
{
	protected $pathDist;

	protected $fileHandle;
	protected $fileName;
	protected $checksum;
	protected $snapshot;
	protected $os;
	protected $arch;

	public function __construct(FileHandle $fileHandle, $fileName, $checksum, $snapshot, $os, $arch)
	{
		$this->pathDist = Config::get("paths.dist");

		$this->fileHandle = $fileHandle;
		$this->fileName = $fileName;
		$this->checksum = $checksum;
		$this->snapshot = $snapshot;
		$this->os = $os;
		$this->arch = $arch;
	}

	public abstract function execute();
	protected abstract function getTargetFolder();
	protected abstract function getTargetFile();

	protected function validateChecksum(TempFile $tempFile)
	{
		$actualChecksum = FileUtil::calculateChecksum($tempFile);

		if ($this->checksum != $actualChecksum) {
			throw new BadRequestHttpException("Invalid checksum of uploaded file.");
		}
	}

	protected function moveFile(TempFile $tempFile)
	{
		$targetFolder = $this->getTargetFolder();
		$targetFile = $this->getTargetFile();

		if (!file_exists($targetFolder) || !is_dir($targetFolder)) {
			if (!mkdir($targetFolder, 0755, true)) {
				throw new ServerErrorHttpException("Cannot create target folder");
			}
		}

		if (!is_writable($targetFolder)) {
			throw new ServerErrorHttpException("Cannot write to target folder");
		}

		if (!rename($tempFile->getFile(), $targetFile)) {
			throw new ServerErrorHttpException("Cannot move temp file to target file");
		}

		return $targetFile;
	}

}
