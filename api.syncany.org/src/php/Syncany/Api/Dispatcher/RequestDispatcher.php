<?php

namespace Syncany\Api\Dispatcher;

use Syncany\Api\Controller\Controller;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;

class RequestDispatcher
{
    public static function dispatch($method, $request)
    {
        $requestArgs = explode('/', rtrim($request, '/'));
        $object = array_shift($requestArgs);
        $verb = (count($requestArgs) > 0) ? $requestArgs[0] : false;

        $controller = self::createController($object);

        if ($verb && $controller->isCallable($method, $verb)) {
            array_shift($requestArgs);
            $controller->call($method, $verb, $requestArgs);
        }
        else {
            $controller->call($method, "", $requestArgs);
        }
    }

    /**
     * @return Controller
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    private static function createController($object)
    {
        $controllerSimpleClassName = self::getControllerClassName($object);
        $controllerFileName = Controller::getBaseDir() . "/$controllerSimpleClassName.php";
        $controllerFullyQualifiedClassName = Controller::getNamespace() . "\\" . $controllerSimpleClassName;

        if (!file_exists($controllerFileName))
        {
            throw new BadRequestHttpException("Invalid controller file. Not found.");
        }

        require_once($controllerFileName);

        if (!class_exists($controllerFullyQualifiedClassName)) {
            throw new ServerErrorHttpException("Cannot find controller class.");
        }

        return new $controllerFullyQualifiedClassName($object);
    }

    private static function getControllerClassName($object)
    {
        $controllerName = strtoupper(substr($object, 0, 1)) . substr($object, 1) . "Controller";

        if (!preg_match('/^[a-z]+$/i', $controllerName)) {
            throw new BadRequestHttpException("Illegal controller name.");
        }

        return $controllerName;
    }
}
