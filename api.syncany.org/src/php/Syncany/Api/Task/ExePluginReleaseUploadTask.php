<?php

namespace Syncany\Api\Task;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\StringUtil;

class ExePluginReleaseUploadTask extends PluginReleaseUploadTask
{
	public function execute()
	{
		$this->validatePluginId();

		$tempDirContext = "plugins/" . $this->pluginId . "/exe";
		$tempDir = FileUtil::createTempDir($tempDirContext);
		$tempFile = FileUtil::writeToTempFile($this->fileHandle, $tempDir, ".exe");

		$this->validateChecksum($tempFile);

		$targetFile = $this->moveFile($tempFile);
		$this->createLatestLink($targetFile);

		FileUtil::deleteTempDir($tempDir);
	}

	private function validatePluginId()
	{
		if ($this->pluginId != "gui") {
			throw new BadRequestHttpException("Exe files can only be uploaded for the GUI plugin");
		}
	}

	protected function getLatestLinkBasename()
	{
		$snapshotSuffix = ($this->snapshot) ? "-snapshot" : "";
		$archSuffix = (isset($this->arch) && $this->arch != "" && $this->arch != "all") ? "-" . $this->arch : "";

		return StringUtil::replace("syncany-plugin-latest{snapshot}-{id}{arch}.exe", array(
			"id" => $this->pluginId,
			"snapshot" => $snapshotSuffix,
			"arch" => $archSuffix
		));
	}
}
