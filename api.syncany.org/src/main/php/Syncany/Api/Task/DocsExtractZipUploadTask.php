<?php

namespace Syncany\Api\Task;

use Syncany\Api\Model\FileHandle;
use Syncany\Api\Util\Log;

class DocsExtractZipUploadTask extends ZipExtractUploadTask
{
    public function __construct(FileHandle $fileHandle, $fileName, $checksum)
    {
        parent::__construct($fileHandle, $fileName, $checksum, "paths.docs");
    }

    public function execute()
    {
        Log::info(__CLASS__, __METHOD__, "Processing uploaded DOCS archive file ...");

        $tempExtractDir = $this->extractZip("app/docs");
        $this->deleteAndMoveDir($tempExtractDir, $this->targetParentDir, "javadoc");
    }
}
