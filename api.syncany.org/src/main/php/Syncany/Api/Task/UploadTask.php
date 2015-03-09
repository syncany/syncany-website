<?php

namespace Syncany\Api\Task;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Util\FileUtil;

abstract class UploadTask
{
    protected $fileHandle;
    protected $fileName;
    protected $checksum;

    public function __construct(FileHandle $fileHandle, $fileName, $checksum)
    {
        $this->fileHandle = $fileHandle;
        $this->fileName = $fileName;
        $this->checksum = $checksum;
    }

    protected function validateChecksum(TempFile $tempFile)
    {
        $actualChecksum = FileUtil::calculateChecksum($tempFile);

        if ($this->checksum != $actualChecksum) {
            throw new BadRequestHttpException("Invalid checksum of uploaded file.");
        }
    }
}
