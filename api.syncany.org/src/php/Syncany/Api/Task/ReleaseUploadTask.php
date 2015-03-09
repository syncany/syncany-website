<?php

namespace Syncany\Api\Task;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Util\FileUtil;

abstract class ReleaseUploadTask extends UploadTask
{
	protected $pathDist;

	protected $snapshot;
	protected $os;
	protected $arch;

	public function __construct(FileHandle $fileHandle, $fileName, $checksum, $snapshot, $os, $arch)
	{
        parent::__construct($fileHandle, $fileName, $checksum);

		$this->pathDist = Config::get("paths.dist");

		$this->snapshot = $snapshot;
		$this->os = $os;
		$this->arch = $arch;
	}

    public abstract function execute();

    protected abstract function getTargetFile();
    protected abstract function getLatestLinkBasename();

    protected function getTargetFolder()
    {
        return dirname($this->getTargetFile());
    }

    protected function moveFile(TempFile $tempFile)
    {
        $targetFolder = $this->getTargetFolder();
        $targetFile = $this->getTargetFile();

        if (!file_exists($targetFolder) || !is_dir($targetFolder)) {
            if (!mkdir($targetFolder, 0755, true)) {
                throw new ServerErrorHttpException("Cannot create target folder");
            }
        }

        if (!is_writable($targetFolder)) {
            throw new ServerErrorHttpException("Cannot write to target folder");
        }

        if (!rename($tempFile->getFile(), $targetFile)) {
            throw new ServerErrorHttpException("Cannot move temp file to target file");
        }

        return $targetFile;
    }

    protected function createLatestLink($targetFile)
	{
		$targetLinkBasename = $this->getLatestLinkBasename();
		$pluginTargetFolder = $this->getTargetFolder();

		$targetLinkFile = $pluginTargetFolder . "/" . $targetLinkBasename;
		$targetFileBasename = basename($targetFile);

		@unlink($targetLinkFile);

		if (!symlink($targetFileBasename, $targetLinkFile)) {
			throw new ServerErrorHttpException("Cannot create symlink");
		}
	}
}
