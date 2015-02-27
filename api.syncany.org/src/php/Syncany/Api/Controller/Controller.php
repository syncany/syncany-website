<?php

namespace Syncany\Api\Controller;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Model\FileHandle;

abstract class Controller
{
    public function isCallable($method, $verb)
    {
        if (!$verb) {
            return false;
        }
        else {
            $methodName = $this->getMethodName($method, $verb);
            return method_exists($this, $methodName);
        }
    }

    public function call($method, $verb, $requestArgs)
    {
        $methodName = $this->getMethodName($method, $verb);

        switch ($method) {
            case 'DELETE':
            case 'POST':
                return $this->$methodName($_POST, $requestArgs);

            case 'GET':
                return $this->$methodName($_GET, $requestArgs);

            case 'PUT':
                $fileHandle = new FileHandle(fopen("php://input", "r"));
                return $this->$methodName($_GET, $requestArgs, $fileHandle);

            default:
                throw new BadRequestHttpException("Invalid method " . $method);
        }
    }

    public static function getNamespace()
    {
        return __NAMESPACE__;
    }

    public static function getBaseDir()
    {
        return dirname(__FILE__);
    }

    private function getMethodName($method, $verb)
    {
        return strtolower($method) . strtoupper(substr($verb, 0, 1)) . substr($verb, 1);
    }
}