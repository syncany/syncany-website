<?php

namespace Syncany\Api\Task;

use Syncany\Api\Model\TempFile;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\RepreproUtil;
use Syncany\Api\Util\StringUtil;

class DebPluginReleaseUploadTask extends PluginReleaseUploadTask
{
	public function execute()
	{
		$tempDirContext = "plugins/" . $this->pluginId . "/deb";

		$tempDir = FileUtil::createTempDir($tempDirContext);
		$tempFile = FileUtil::writeToTempFile($this->fileHandle, $tempDir, ".deb");

		$this->validateChecksum($tempFile);
		$this->addToAptArchive($tempFile);

		$targetFile = $this->moveFile($tempFile);
		$this->createLatestLink($targetFile);

		FileUtil::deleteTempDir($tempDir);
	}

	private function addToAptArchive(TempFile $debFile)
	{
		$codename = ($this->snapshot) ? "snapshot" : "release";
		RepreproUtil::includeDeb($codename, $debFile);
	}

	protected function getLatestLinkBasename()
	{
		$snapshotSuffix = ($this->snapshot) ? "-snapshot" : "";
		$archSuffix = (!isset($this->arch)) ? "" : ($this->arch == "x86") ? "-i386" : "-amd64";

		return StringUtil::replace("syncany-plugin-latest{snapshot}-{id}{arch}.deb", array(
			"id" => $this->pluginId,
			"snapshot" => $snapshotSuffix,
			"arch" => $archSuffix,
		));
	}
}
