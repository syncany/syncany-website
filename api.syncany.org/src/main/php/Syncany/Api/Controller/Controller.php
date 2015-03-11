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

use Syncany\Api\Exception\ApiException;
use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\UnauthorizedHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\Log;

/**
 * A controller implements the behavioral logic for a certain resource
 * group. All its public methods are by convention accessible via the API.
 * They must follow a certain method name pattern (see below) and have
 * exactly two arguments, <tt>requestArgs</tt> and <tt>methodArgs</tt>.
 * All <tt>put*</tt> methods must have an additional argument <tt>$fileHandle</tt>,
 * which will contain the file input stream of the uploaded file.
 *
 * <p>Method pattern: [method][verb], e.g. get, getList, putSnapshot, ...
 * The method is mandatory, the verb is optional.
 *
 * <p><tt>requestArgs</tt> are the arguments passed in request URI (e.g.
 * /plugins/list/arg1/arg2), <tt>methodArgs</tt> are the arguments of the
 * corresponding method, i.e. $_GET for GET/PUT, and $_POST for POST/DELETE.
 *
 * <p>Example method signatures:
 * <pre>
 *   public function getList(array $methodArgs, array $requestArgs) { .. }
 *   public function post(array $methodArgs, array $requestArgs) { .. }
 *   public function putFile(array $methodArgs, array $requestArgs, FileHandle $fileHandle) { .. }
 * </pre>
 *
 * @author Philipp Heckel <philipp.heckel@gmail.com>
 */
abstract class Controller
{
    /**
     * Generic error message to avoid exposing the
     * exact error position.
     */
    const ERR_INVALID_AUTH_PARAMS = "Authentication failed";

    /**
     * Number of seconds to allow for the client and server
     * time to deviate. This should be small, to prevent replay
     * attacks.
     */
    const REPLAY_RANGE = 180;

    private $name;
    private $method;
    private $verb;

    public function __construct($name)
    {
        $this->name = $name;
    }

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
        $this->method = $method;
        $this->verb = $verb;

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

    protected function authorize($keyName, array $methodArgs, array $requestArgs)
    {
        $this->validateTimeRandAndSignature($methodArgs);

        $actualSignature = $methodArgs['signature'];
        unset($methodArgs['signature']);

        $originalRequest = $this->getOriginalRequest($requestArgs);

        $protectedInput =
                    $this->method
            . ":" . $originalRequest
            . ":" . http_build_query($methodArgs);

        Log::debug(__CLASS__, __METHOD__, "Protected input is $protectedInput");

        try {
            $apiKey = $this->readApiKey($keyName);
        } catch (ApiException $e) {
            throw new UnauthorizedHttpException(self::ERR_INVALID_AUTH_PARAMS);
        }

        $expectedSignature = hash_hmac("sha256", $protectedInput, $apiKey);

        if ($expectedSignature != $actualSignature) {
            Log::error(__CLASS__, __METHOD__, "Given signature does not match expected signature.");
            throw new UnauthorizedHttpException(self::ERR_INVALID_AUTH_PARAMS);
        }

        Log::info(__CLASS__, __METHOD__, "Authentication successful.");
    }

    private function readApiKey($keyName)
    {
        $keys = FileUtil::readPropertiesFile("keys", $keyName);

        if (!isset($keys['key'])) {
            throw new ConfigException("Cannot read API key from configuration");
        }

        return $keys['key'];
    }

    private function validateTimeRandAndSignature($methodArgs)
    {
        Log::debug(__CLASS__, __METHOD__, "Validating time, rand and signature parameter ...");

        if (!isset($methodArgs['signature']) || !isset($methodArgs['time']) || !isset($methodArgs['rand'])
            || !is_numeric($methodArgs['time'])) {

            Log::warning(__CLASS__, __METHOD__, "Invalid signature, time or rand value while.");
            throw new UnauthorizedHttpException(self::ERR_INVALID_AUTH_PARAMS);
        }

        $actualTime = intval($methodArgs['time']);
        $timeInAllowedRange = abs($actualTime - time()) <= self::REPLAY_RANGE; // Replay attacks

        if (!$timeInAllowedRange) {
            Log::warning(__CLASS__, __METHOD__, "Time not in allowed range.");
            throw new UnauthorizedHttpException(self::ERR_INVALID_AUTH_PARAMS);
        }
    }

    private function getOriginalRequest(array $requestArgs)
    {
        $nameStr = $this->name;
        $verbStr = ($this->verb) ? "/" . $this->verb : "";
        $requestArgsStr = (count($requestArgs) > 0) ? "/" . join("/", $requestArgs) : "";

        return $nameStr . $verbStr . $requestArgsStr;
    }
}