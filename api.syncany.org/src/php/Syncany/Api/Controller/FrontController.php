<?php

namespace Syncany\Api\Controller;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle as FileHandle;

class FrontController
{
    protected $requestArgs = Array();
    protected $method = '';
    protected $object = '';
    protected $verb = '';

    public function dispatch($request)
    {
        $this->requestArgs = explode('/', rtrim($request, '/'));
        $this->object = array_shift($this->requestArgs);
        $this->verb = array_shift($this->requestArgs);
        $this->method = $_SERVER['REQUEST_METHOD'];

        $controller = $this->getController();
        $controllerMethodName = $this->getControllerMethod($controller);

        $this->callController($controller, $controllerMethodName);
    }

    private function callController($controller, $controllerMethodName)
    {
        switch ($this->method) {
            case 'DELETE':
            case 'POST':
                return $controller->$controllerMethodName($_POST, $this->requestArgs);

            case 'GET':
                return $controller->$controllerMethodName($_GET, $this->requestArgs);

            case 'PUT':
                $fileHandle = new FileHandle(fopen("php://input", "r"));
                return $controller->$controllerMethodName($_GET, $this->requestArgs, $fileHandle);

            default:
                throw new BadRequestHttpException("Invalid method " . $this->method);
        }
    }

    private function getController()
    {
        $controllerSimpleClassName = strtoupper(substr($this->object, 0, 1)) . substr($this->object, 1) . "Controller";
        $controllerFullyQualifiedClassName = "\\" . __NAMESPACE__ . "\\" . $controllerSimpleClassName;
        $controllerFileName = dirname(__FILE__) . "/$controllerSimpleClassName.php";

        if (!file_exists($controllerFileName))
        {
            throw new BadRequestHttpException("Invalid controller file. Not found.");
        }

        require_once($controllerFileName);

        if (!class_exists($controllerFullyQualifiedClassName)) {
            throw new ServerErrorHttpException("Cannot find controller class.");
        }

        $controller = new $controllerFullyQualifiedClassName();
        return $controller;
    }

    private function getControllerMethod($controller)
    {
        $controllerMethodName = strtolower($this->method) . strtoupper(substr($this->verb, 0, 1)) . substr($this->verb, 1);

        if (!method_exists($controller, $controllerMethodName)) {
            $controllerMethodName = strtolower($this->method);

            if (!method_exists($controller, $controllerMethodName)) {
                throw new BadRequestHttpException("Cannot find controller request method.");
            }
        }

        return $controllerMethodName;
    }
}