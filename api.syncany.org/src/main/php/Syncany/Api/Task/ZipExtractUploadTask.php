<?php

namespace Syncany\Api\Task;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\Log;

abstract class ZipExtractUploadTask extends UploadTask
{
    protected $targetParentDir;

    public function __construct(FileHandle $fileHandle, $fileName, $checksum, $targetDirId)
    {
        parent::__construct($fileHandle, $fileName, $checksum);
        $this->targetParentDir = Config::get($targetDirId);
    }

    protected function extractZip($contextPrefix)
    {
        $tempLandingDirContext = $contextPrefix . "/landing";
        $tempExtractDirContext = $contextPrefix . "/extract";

        $tempLandingDir = FileUtil::createTempDir($tempLandingDirContext);
        $tempExtractDir = FileUtil::createTempDir($tempExtractDirContext);

        $tempFile = FileUtil::writeToTempFile($this->fileHandle, $tempLandingDir, ".zip");
        $this->validateChecksum($tempFile);

        FileUtil::extractZipArchive($tempFile, $tempExtractDir);
        FileUtil::deleteTempDir($tempLandingDir);

        Log::info(__CLASS__, __METHOD__, "Successfully extracted to " . $tempExtractDir->getFile());
        return $tempExtractDir;
    }

    protected function deleteAndMoveDir(TempFile $tempExtractDir, $targetParentDir, $relativeDir)
    {
        $tempSourceDir = $tempExtractDir->getFile() . "/" . $relativeDir;
        $targetDir = $targetParentDir . "/" . $relativeDir;

        if (!is_dir($tempSourceDir)) {
            throw new BadRequestHttpException("Source dir not found in archive");
        }

        if (is_dir($targetDir)) {
            Log::info(__CLASS__, __METHOD__, "Target dir exists at '$targetDir'; deleting it ...");
            FileUtil::deleteDir($this->targetParentDir, $targetDir);

            if (is_dir($targetDir)) {
                Log::info(__CLASS__, __METHOD__, "Deleting failed. Maybe a permission issue.");
                throw new ConfigException("Unable to delete target dir.");
            }
        }

        FileUtil::makeParentDirs($targetDir);
        FileUtil::moveFile($tempExtractDir->getFile(), $tempSourceDir, $this->targetParentDir, $targetDir);
    }

    protected function deleteAndMoveFile(TempFile $tempExtractDir, $targetParentDir, $relativeFile)
    {
        $tempSourceFile = $tempExtractDir->getFile() . "/" . $relativeFile;
        $targetFile = $targetParentDir . "/" . $relativeFile;

        if (!is_file($tempSourceFile)) {
            throw new BadRequestHttpException("Source file not found in archive");
        }

        if (is_file($targetFile)) {
            Log::info(__CLASS__, __METHOD__, "Target file exists at '$targetFile'; deleting it ...");
            FileUtil::deleteFile($this->targetParentDir, $targetFile);

            if (is_dir($targetFile)) {
                Log::info(__CLASS__, __METHOD__, "Deleting failed. Maybe a permission issue.");
                throw new ConfigException("Unable to delete target dir.");
            }
        }

        FileUtil::moveFile($tempExtractDir->getFile(), $tempSourceFile, $this->targetParentDir, $targetFile);
    }
}
