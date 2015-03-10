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
use Syncany\Api\Util\StringUtil;

abstract class AppReleaseUploadTask extends ReleaseUploadTask
{
	public function __construct(FileHandle $fileHandle, $fileName, $checksum, $snapshot)
	{
		parent::__construct($fileHandle, $fileName, $checksum, $snapshot, "all", "all");
	}

	protected function getTargetFile()
	{
		$targetSubFolder = ($this->snapshot) ? "snapshots" : "releases";
		$targetFolder = $this->pathDist . "/" . $targetSubFolder;

		return $targetFolder . "/" . basename($this->fileName);
	}
}
