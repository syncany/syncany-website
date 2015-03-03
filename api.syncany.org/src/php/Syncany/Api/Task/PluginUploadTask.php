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

	protected function getTargetFile()
	{
		$pluginTargetSubFolder = ($this->snapshot) ? "snapshots" : "releases";
		$pluginTargetFolder = $this->pathPluginDist . "/" . $pluginTargetSubFolder . "/" . $this->pluginId;

		return $pluginTargetFolder . "/" . basename($this->fileName);
	}
}
