<?php

namespace Syncany\Api\Controller;

use Syncany\Api\Model\FileHandle as FileHandle;

class AppController extends Controller
{
    public function putRelease(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        print_r($fileHandle->getHandle());
    }

    public function putSnapshot(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        print_r($fileHandle->getHandle());
    }

    public function getInfo(array $methodArgs, array $requestArgs)
    {
        if (isset($methodArgs['snapshot'])) {
            echo "v0.4.3-alpha+SNAPSHOT-123123";
        }
        else {
            echo "v0.4.3-alpha";
        }
    }
}