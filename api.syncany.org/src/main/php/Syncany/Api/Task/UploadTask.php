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
