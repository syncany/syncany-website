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

	protected function getTargetFolder()
	{
		$targetSubFolder = ($this->snapshot) ? "snapshots" : "releases";
		$targetFolder = $this->pathDist . "/" . $targetSubFolder;

		return $targetFolder;
	}

	protected function getTargetFile()
	{
		return $this->getTargetFolder() . "/" . basename($this->fileName);
	}

	protected function createLatestLink($targetFile)
	{
		$targetLinkBasename = $this->getLatestLinkBasename();
		$targetFolder = $this->getTargetFolder();

		$targetLinkFile = $targetFolder . "/" . $targetLinkBasename;
		$targetFileBasename = basename($targetFile);

		@unlink($targetLinkFile);

		if (!symlink($targetFileBasename, $targetLinkFile)) {
			throw new ServerErrorHttpException("Cannot create symlink");
		}
	}

	abstract protected function getLatestLinkBasename();
}
