<?php

namespace Syncany\Api\Task;

use Syncany\Api\Model\FileHandle;
use Syncany\Api\Util\Log;

class ReportsExtractZipUploadTask extends ZipExtractUploadTask
{
    public function __construct(FileHandle $fileHandle, $fileName, $checksum)
    {
        parent::__construct($fileHandle, $fileName, $checksum, "paths.reports");
    }

	public function execute()
	{
        Log::info(__CLASS__, __METHOD__, "Processing uploaded REPORTS archive file ...");

		$tempExtractDir = $this->extractZip("app/reports");

        $this->deleteAndMoveDir($tempExtractDir, $this->targetParentDir, "tests");
        $this->deleteAndMoveDir($tempExtractDir, $this->targetParentDir, "coverage");
        $this->deleteAndMoveFile($tempExtractDir, $this->targetParentDir, "cloc.xml");
	}
}
