<?php

/*
 * Syncany, www.syncany.org
 * Copyright (C) 2011-2015 Philipp C. Heckel <philipp.heckel@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Syncany\Api\Task;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\Log;

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

        Log::info(__CLASS__, __METHOD__, "Moving to target: " . $tempFile->getFile() . " -> $targetFile ...");

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

        Log::info(__CLASS__, __METHOD__, "Creating symlink: $targetLinkFile -> $targetFileBasename ...");

		@unlink($targetLinkFile);

		if (!symlink($targetFileBasename, $targetLinkFile)) {
			throw new ServerErrorHttpException("Cannot create symlink");
		}
	}
}
