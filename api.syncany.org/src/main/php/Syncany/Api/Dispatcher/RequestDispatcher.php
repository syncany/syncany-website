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

namespace Syncany\Api\Dispatcher;

use Syncany\Api\Controller\Controller;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\HttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Util\Log;

/**
 * The request dispatcher is the entry point for all API requests. It analyzes the
 * request URI and determines a responsible {@link Controller}, and calls it. The request
 * URI is passed via the GET variable 'request'.
 *
 * <p>The request pattern is /[controller]/[verb]/[arg1]/[arg2]/...
 * The controller is mandatory, the verb and the arguments are optional. The dispatcher
 * instantiates a new controller class and calls it via its call() method.
 *
 * <p>Example: A GET request to /plugins/list will instantiate a <tt>PluginsController</tt> class
 * and eventually call its <tt>getList()</tt> method. If no method for the given verb is found,
 * the verb is interpreted as a request argument. In this example, if <tt>getList()</tt> does not
 * exist, <tt>get()</tt> is called.
 *
 * @author Philipp Heckel <philipp.heckel@gmail.com>
 */
class RequestDispatcher
{
    public static function dispatch()
    {
        try {
            if (!isset($_GET['request'])) {
                throw new BadRequestHttpException("Invalid request, param 'request' missing");
            }

            $method = $_SERVER['REQUEST_METHOD'];
            $request = $_GET['request'];

            unset($_GET['request']);

            Log::info(__CLASS__, __METHOD__, "$method " . $_SERVER['REQUEST_URI']);

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
        } catch (HttpException $e) {
            Log::error(__CLASS__, __METHOD__, $e->getMessage() . ": " . $e->getReason(), null, $e);
            $e->sendErrorHeadersAndExit();
        } catch (\Exception $e) {
            Log::error(__CLASS__, __METHOD__, $e->getMessage(), null, $e);

            $wrappedError = new ServerErrorHttpException($e->getMessage());
            $wrappedError->sendErrorHeadersAndExit();
        }
    }

    private static function createController($object)
    {
        $controllerSimpleClassName = self::getControllerClassName($object);
        $controllerFileName = Controller::getBaseDir() . "/$controllerSimpleClassName.php";
        $controllerFullyQualifiedClassName = Controller::getNamespace() . "\\" . $controllerSimpleClassName;

        if (!file_exists($controllerFileName))
        {
            throw new BadRequestHttpException("Invalid controller file. Not found.");
        }

        require_once $controllerFileName;

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
