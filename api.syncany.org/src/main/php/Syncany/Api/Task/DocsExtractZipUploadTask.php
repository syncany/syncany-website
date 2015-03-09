<?php

namespace Syncany\Api\Task;

use Syncany\Api\Model\FileHandle;

class DocsExtractZipUploadTask extends ZipExtractUploadTask
{
    public function __construct(FileHandle $fileHandle, $fileName, $checksum)
    {
        parent::__construct($fileHandle, $fileName, $checksum, "paths.docs");
    }

    public function execute()
    {
        $tempExtractDir = $this->extractZip("apps/docs");
        $this->deleteAndMoveDir($tempExtractDir, $this->targetParentDir, "javadoc");
    }
}
