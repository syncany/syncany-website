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

namespace Syncany\Api\Controller;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle as FileHandle;
use Syncany\Api\Task\DebAppReleaseUploadTask;
use Syncany\Api\Task\DocsExtractZipUploadTask;
use Syncany\Api\Task\ExeAppReleaseUploadTask;
use Syncany\Api\Task\ReportsExtractZipUploadTask;
use Syncany\Api\Task\TarGzAppReleaseUploadTask;
use Syncany\Api\Task\ZipAppReleaseUploadTask;
use Syncany\Api\Util\Log;

class AppController extends Controller
{
    public function put(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        Log::info(__CLASS__, __METHOD__, "Put request for application received. Authenticating ...");
        $this->authorize("application-put", $methodArgs, $requestArgs);

        $checksum = ControllerHelper::validateChecksum($methodArgs);
        $fileName = ControllerHelper::validateFileName($methodArgs);
        $snapshot = ControllerHelper::validateIsSnapshot($methodArgs);

        $type = $this->validateType($methodArgs);

        $task = $this->createTask($type, $fileHandle, $fileName, $checksum, $snapshot);
        $task->execute();
    }

    private function validateType($methodArgs)
    {
        if (!isset($methodArgs['type']) || !in_array($methodArgs['type'], array("tar.gz", "zip", "deb", "exe", "docs", "reports"))) {
            throw new BadRequestHttpException("No or invalid type argument given.");
        }

        return $methodArgs['type'];
    }

    private function createTask($type, $fileHandle, $fileName, $checksum, $snapshot)
    {
        switch ($type) {
            case "tar.gz":
                return new TarGzAppReleaseUploadTask($fileHandle, $fileName, $checksum, $snapshot);

            case "zip":
                return new ZipAppReleaseUploadTask($fileHandle, $fileName, $checksum, $snapshot);

            case "deb":
                return new DebAppReleaseUploadTask($fileHandle, $fileName, $checksum, $snapshot);

            case "exe":
                return new ExeAppReleaseUploadTask($fileHandle, $fileName, $checksum, $snapshot);

            case "docs":
                return new DocsExtractZipUploadTask($fileHandle, $fileName, $checksum);

            case "reports":
                return new ReportsExtractZipUploadTask($fileHandle, $fileName, $checksum);

            default:
                throw new ServerErrorHttpException("Type not supported.");
        }
    }
}