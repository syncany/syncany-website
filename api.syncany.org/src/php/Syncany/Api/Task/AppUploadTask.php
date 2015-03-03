<?php

namespace Syncany\Api\Task;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\StringUtil;

abstract class AppUploadTask extends UploadTask
{
	public function __construct(FileHandle $fileHandle, $fileName, $checksum, $snapshot)
	{
		parent::__construct($fileHandle, $fileName, $checksum, $snapshot, "all", "all");
	}

	protected function getTargetFile()
	{
		$targetSubFolder = ($this->snapshot) ? "snapshots" : "releases";
		$targetFolder = $this->pathDist . "/" . $targetSubFolder;

		return $targetFolder . "/" . basename($this->fileName);
	}
}
