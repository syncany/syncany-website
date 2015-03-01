<?php

namespace Syncany\Api\Task;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\StringUtil;

abstract class PluginUploadTask extends UploadTask
{
	protected $pathPluginDist;
	protected $pluginId;

	public function __construct(FileHandle $fileHandle, $fileName, $checksum, $snapshot, $os, $arch, $pluginId)
	{
		parent::__construct($fileHandle, $fileName, $checksum, $snapshot, $os, $arch);

		$this->pathPluginDist = Config::get("paths.plugindist");
		$this->pluginId = $pluginId;
	}

	protected function getTargetFolder()
	{
		$pluginTargetSubFolder = ($this->snapshot) ? "snapshots" : "releases";
		$pluginTargetFolder = $this->pathPluginDist . "/" . $pluginTargetSubFolder . "/" . $this->pluginId;

		return $pluginTargetFolder;
	}

	protected function getTargetFile()
	{
		return $this->getTargetFolder() . "/" . basename($this->fileName);
	}

	protected function createLatestLink($targetFile)
	{
		$targetLinkBasename = $this->getLatestLinkBasename();
		$pluginTargetFolder = $this->getTargetFolder();

		$targetLinkFile = $pluginTargetFolder . "/" . $targetLinkBasename;
		$targetFileBasename = basename($targetFile);

		@unlink($targetLinkFile);

		if (!symlink($targetFileBasename, $targetLinkFile)) {
			throw new ServerErrorHttpException("Cannot create symlink");
		}
	}

	abstract protected function getLatestLinkBasename();
}
