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

use Naneau\SemVer\Compare;
use Naneau\SemVer\Parser;
use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\Plugin;
use Syncany\Api\Persistence\Database;
use Syncany\Api\Task\DebPluginReleaseUploadTask;
use Syncany\Api\Task\JarPluginReleaseUploadTask;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\Log;
use Syncany\Api\Util\StringUtil;

class PluginsController extends Controller
{
    public function get(array $methodArgs, array $requestArgs)
    {
        $this->getList($methodArgs, $requestArgs);
    }

    public function getList(array $methodArgs, array $requestArgs)
    {
        // Check request params
        $appVersion = ControllerHelper::validateAppVersion($methodArgs);
        $operatingSystem = ControllerHelper::validateOperatingSystem($methodArgs);
        $architecture = ControllerHelper::validateArchitecture($methodArgs);
        $includeSnapshots = ControllerHelper::validateWithSnapshots($methodArgs);

        $pluginId = $this->validatePluginId($methodArgs, $requestArgs);

        // Get data
        $plugins = ($pluginId)
            ? $this->queryWithPluginId($pluginId, $operatingSystem, $architecture, $includeSnapshots)
            : $this->queryWithoutPluginId($operatingSystem, $architecture, $includeSnapshots);

        $compatiblePlugins = $this->getCompatiblePlugins($appVersion, $plugins);

        // Print XML
        $this->printResponseXml(200, "OK", $compatiblePlugins);
        exit;
    }

    public function put(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        $pluginId = $this->validatePluginId($methodArgs, $requestArgs);

        if (!$pluginId) {
            throw new BadRequestHttpException("Invalid request, no plugin identifier given");
        }

        Log::info(__CLASS__, __METHOD__, "Put request for plugin $pluginId received. Authenticating ...");
        $this->authorize("plugins-put-$pluginId", $methodArgs, $requestArgs);

        $checksum = ControllerHelper::validateChecksum($methodArgs);
        $fileName = ControllerHelper::validateFileName($methodArgs);
        $snapshot = ControllerHelper::validateIsSnapshot($methodArgs);
        $os = ControllerHelper::validateOperatingSystem($methodArgs);
        $arch = ControllerHelper::validateArchitecture($methodArgs);

        $type = $this->validateType($methodArgs);

        $task = $this->createTask($type, $fileHandle, $fileName, $checksum, $snapshot, $os, $arch, $pluginId);
        $task->execute();
    }

    private function createTask($type, $fileHandle, $fileName, $checksum, $snapshot, $os, $arch, $pluginId)
    {
        switch ($type) {
            case "jar":
                return new JarPluginReleaseUploadTask($fileHandle, $fileName, $checksum, $snapshot, $os, $arch, $pluginId);

            case "deb":
                return new DebPluginReleaseUploadTask($fileHandle, $fileName, $checksum, $snapshot, $os, $arch, $pluginId);

            default:
                throw new ServerErrorHttpException("Type not supported.");
        }
    }

    private function validateType($methodArgs)
    {
        if (!isset($methodArgs['type']) || !in_array($methodArgs['type'], array("jar", "deb", "app.zip", "exe"))) {
            throw new BadRequestHttpException("No or invalid type argument given.");
        }

        return $methodArgs['type'];
    }

    private function validatePluginId($methodArgs, $requestArgs)
    {
        $methodArgGiven = isset($methodArgs['pluginId']) && !empty($methodArgs['pluginId']); // Treat empty as not present (v2-compatible!)
        $requestArgGiven = isset($requestArgs[0]);

        if ($methodArgGiven || $requestArgGiven) {
            $pluginId = ($methodArgGiven) ? $methodArgs['pluginId'] : $requestArgs[0];

            if (!preg_match('/^[a-z0-9]+/i', $pluginId)) {
                throw new BadRequestHttpException("Invalid request. Plugin identifier is invalid.");
            }

            return $pluginId;
        } else {
            return false;
        }
    }

    private function queryWithPluginId($pluginId, $operatingSystem, $architecture, $includeSnapshots)
    {
        $sqlQueryFile = ($includeSnapshots) ? "plugins.select-with-id-with-snapshots.sql" : "plugins.select-with-id-release-only.sql";
        $statement = Database::prepareStatementFromResource("plugins-read", __NAMESPACE__, $sqlQueryFile);

        $statement->bindValue(':pluginId', $pluginId, \PDO::PARAM_STR);
        $statement->bindValue(':pluginOperatingSystem', $operatingSystem, \PDO::PARAM_STR);
        $statement->bindValue(':pluginArchitecture', $architecture, \PDO::PARAM_STR);

        return $this->fetchPlugins($statement);
    }

    private function queryWithoutPluginId($operatingSystem, $architecture, $includeSnapshots)
    {
        $sqlQueryFile = ($includeSnapshots) ? "plugins.select-all-with-snapshots.sql" : "plugins.select-all-release-only.sql";
        $statement = Database::prepareStatementFromResource("plugins-read", __NAMESPACE__, $sqlQueryFile);

        $statement->bindParam(':pluginOperatingSystem', $operatingSystem, \PDO::PARAM_STR);
        $statement->bindParam(':pluginArchitecture', $architecture, \PDO::PARAM_STR);

        return $this->fetchPlugins($statement);
    }

    private function fetchPlugins(\PDOStatement $statement)
    {
        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        if (!$statement->execute()) {
            throw new ServerErrorHttpException("Cannot retrieve plugins from database.");
        }

        $plugins = array();

        while($pluginArray = $statement->fetch()) {
            $plugins[] = Plugin::fromArray($pluginArray);
        }

        return $plugins;
    }

    private function getCompatiblePlugins($appVersion, $allPlugins)
    {
        $compatiblePlugins = array();

        foreach ($allPlugins as $plugin) {
            $pluginId = $plugin->getId();
            $isCompatiblePlugin = $this->greaterOrEqual($appVersion, $plugin->getAppMinVersion());

            if ($isCompatiblePlugin) {
                $pluginAlreadyInResultList = isset($compatiblePlugins[$pluginId]);

                if (!$pluginAlreadyInResultList) {
                    $compatiblePlugins[$pluginId] = $plugin;
                }
            }
        }

        return $compatiblePlugins;
    }

    private function greaterOrEqual($givenAppVersion, $pluginMinAppVersion)
    {
        try {
            $givenAppVersionSem = Parser::parse($givenAppVersion);
            $pluginMinAppVersionSem = Parser::parse($pluginMinAppVersion);

            return Compare::greaterThan($givenAppVersionSem, $pluginMinAppVersionSem)
                || Compare::equals($givenAppVersionSem, $pluginMinAppVersionSem);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function printResponseXml($code, $message, $plugins)
    {
        $teamSupportedPlugins = preg_split("/,/", Config::get("plugins.team-supported"));
        $downloadBaseUrl = Config::get("plugins.base-url");

        $wrapperSkeleton = FileUtil::readResourceFile(__NAMESPACE__, "plugins.get-response.wrapper.skeleton.xml");
        $pluginInfoSkeleton = FileUtil::readResourceFile(__NAMESPACE__, "plugins.get-response.plugininfo.skeleton.xml");

        $pluginInfoBlocks = array();

        foreach ($plugins as $plugin) {
            $downloadUrl = $downloadBaseUrl . $plugin->getFilenameFull();
            $isThirdPartyPlugin = (in_array($plugin->getId(), $teamSupportedPlugins)) ? "false" : "true";
            $isRelease = ($plugin->getRelease()) ? "true" : "false";

            $pluginInfoBlocks[] = StringUtil::replace($pluginInfoSkeleton, array(
                "pluginId" => $plugin->getId(),
                "pluginName" => $plugin->getName(),
                "pluginVersion" => $plugin->getVersion(),
                "pluginOperatingSystem" => $plugin->getOperatingSystem(),
                "pluginArchitecture" => $plugin->getArchitecture(),
                "pluginDate" => $plugin->getDate(),
                "pluginAppMinVersion" => $plugin->getAppMinVersion(),
                "pluginRelease" => $isRelease,
                "pluginConflictsWith" => $plugin->getConflictsWith(),
                "pluginThirdParty" => $isThirdPartyPlugin,
                "downloadUrl" => $downloadUrl,
                "sha256sum" => $plugin->getSha256sum()
            ));
        }

        $pluginsStr = join("\n", $pluginInfoBlocks);

        $xml = StringUtil::replace($wrapperSkeleton, array(
            "code" => $code,
            "message" => $message,
            "plugins" => $pluginsStr
        ));

        header("Content-Type: application/xml");
        echo $xml;

        exit;
    }
}