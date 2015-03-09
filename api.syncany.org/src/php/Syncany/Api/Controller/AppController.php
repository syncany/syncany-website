<?php

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

    public function putDocs(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        $methodArgs['type'] = "docs";
        $this->put($methodArgs, $requestArgs, $fileHandle);
    }

    public function putReports(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        $methodArgs['type'] = "reports";
        $this->put($methodArgs, $requestArgs, $fileHandle);
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