<?php

namespace Syncany\Api\Task;

use Syncany\Api\Model\FileHandle;

class ReportsExtractZipUploadTask extends ZipExtractUploadTask
{
    public function __construct(FileHandle $fileHandle, $fileName, $checksum)
    {
        parent::__construct($fileHandle, $fileName, $checksum, "paths.reports");
    }

	public function execute()
	{
		$tempExtractDir = $this->extractZip("apps/reports");

        $this->deleteAndMoveDir($tempExtractDir, $this->targetParentDir, "tests");
        $this->deleteAndMoveDir($tempExtractDir, $this->targetParentDir, "coverage");
        $this->deleteAndMoveFile($tempExtractDir, $this->targetParentDir, "cloc.xml");
	}
}
