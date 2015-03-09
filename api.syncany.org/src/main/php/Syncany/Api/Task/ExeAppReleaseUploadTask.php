<?php

namespace Syncany\Api\Task;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\StringUtil;

class ExeAppReleaseUploadTask extends AppReleaseUploadTask
{
	public function execute()
	{
		$tempDirContext = "app/exe";
		$tempDir = FileUtil::createTempDir($tempDirContext);
		$tempFile = FileUtil::writeToTempFile($this->fileHandle, $tempDir, ".exe");

		$this->validateChecksum($tempFile);

		$targetFile = $this->moveFile($tempFile);
		$this->createLatestLink($targetFile);

		FileUtil::deleteTempDir($tempDir);
	}

	protected function getLatestLinkBasename()
	{
		$snapshotSuffix = ($this->snapshot) ? "-snapshot" : "";

		return StringUtil::replace("syncany-cli-latest{snapshot}.exe", array(
			"snapshot" => $snapshotSuffix
		));
	}
}
