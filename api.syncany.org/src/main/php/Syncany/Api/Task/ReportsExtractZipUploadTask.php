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

        $this->deleteAndMoveDir($tempExtractDir->getFile(), $this->targetParentDir, "reports/tests");
        $this->deleteAndMoveDir($tempExtractDir->getFile(), $this->targetParentDir, "reports/coverage");
        $this->deleteAndMoveFile($tempExtractDir->getFile(), $this->targetParentDir, "reports/cloc.xml");
	}
}
