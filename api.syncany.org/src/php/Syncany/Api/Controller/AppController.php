<?php

namespace Syncany\Api\Controller;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle as FileHandle;
use Syncany\Api\Task\DebAppUploadTask;
use Syncany\Api\Task\ExeAppUploadTask;
use Syncany\Api\Task\TarGzAppUploadTask;
use Syncany\Api\Task\ZipAppUploadTask;
use Syncany\Api\Util\Log;

class AppController extends Controller
{
    public function put(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        Log::info(__CLASS__, "Put request for application received. Authenticating ...");
        $this->authenticate("application-put", $methodArgs, $requestArgs);

        $checksum = ControllerHelper::validateChecksum($methodArgs);
        $fileName = ControllerHelper::validateFileName($methodArgs);
        $snapshot = ControllerHelper::validateIsSnapshot($methodArgs);

        $type = $this->validateType($methodArgs);

        switch ($type) {
            case "tar.gz":
                $task = new TarGzAppUploadTask($fileHandle, $fileName, $checksum, $snapshot);
                break;

            case "zip":
                $task = new ZipAppUploadTask($fileHandle, $fileName, $checksum, $snapshot);
                break;

            case "deb":
                $task = new DebAppUploadTask($fileHandle, $fileName, $checksum, $snapshot);
                break;

            case "exe":
                $task = new ExeAppUploadTask($fileHandle, $fileName, $checksum, $snapshot);
                break;

            default:
                throw new ServerErrorHttpException("Type not supported.");
        }

        $task->execute();
    }

    public function putJavadoc(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        // Nothing.
    }

    public function putReports(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        // Nothing.
    }

    private function validateType($methodArgs)
    {
        if (!isset($methodArgs['type']) || !in_array($methodArgs['type'], array("tar.gz", "zip", "deb", "exe"))) {
            throw new BadRequestHttpException("No or invalid type argument given.");
        }

        return $methodArgs['type'];
    }
}