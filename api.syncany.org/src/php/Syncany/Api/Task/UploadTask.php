<?php

namespace Syncany\Api\Task;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
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

	public function __construct(FileHandle $fileHandle, $fileName, $checksum, $snapshot)
	{
		$this->pathDist = Config::get("paths.dist");

		$this->fileHandle = $fileHandle;
		$this->fileName = $fileName;
		$this->checksum = $checksum;
		$this->snapshot = $snapshot;
	}

	public abstract function execute();

	protected function validateChecksum(TempFile $tempFile)
	{
		$actualChecksum = FileUtil::calculateChecksum($tempFile);

		if ($this->checksum != $actualChecksum) {
			throw new BadRequestHttpException("Invalid checksum of uploaded file.");
		}
	}
}
