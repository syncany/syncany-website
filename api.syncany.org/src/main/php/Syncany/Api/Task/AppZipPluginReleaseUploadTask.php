<?php

namespace Syncany\Api\Task;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\StringUtil;

class AppZipPluginReleaseUploadTask extends PluginReleaseUploadTask
{
	public function execute()
	{
		$this->validatePluginId();

		$tempDirContext = "plugins/" . $this->pluginId . "/appzip";

		$tempDir = FileUtil::createTempDir($tempDirContext);
		$tempFile = FileUtil::writeToTempFile($this->fileHandle, $tempDir, ".app.zip");

		$this->validateChecksum($tempFile);

		$targetFile = $this->moveFile($tempFile);
		$this->createLatestLink($targetFile);

		FileUtil::deleteTempDir($tempDir);
	}

	private function validatePluginId()
	{
		if ($this->pluginId != "gui") {
			throw new BadRequestHttpException("AppZip files can only be uploaded for the GUI plugin");
		}
	}

	protected function getLatestLinkBasename()
	{
		$snapshotSuffix = ($this->snapshot) ? "-snapshot" : "";
		$archSuffix = (!isset($this->arch)) ? "" : ($this->arch == "x86") ? "-i386" : "-amd64";

		return StringUtil::replace("syncany-plugin-latest{snapshot}-{id}{arch}.app.zip", array(
			"id" => $this->pluginId,
			"snapshot" => $snapshotSuffix,
			"arch" => $archSuffix
		));
	}
}
