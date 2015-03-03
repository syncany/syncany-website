<?php

namespace Syncany\Api\Controller;

use Naneau\SemVer\Compare;
use Naneau\SemVer\Parser;
use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\Plugin;
use Syncany\Api\Persistence\Database;
use Syncany\Api\Task\AppZipPluginUploadTask;
use Syncany\Api\Task\DebPluginUploadTask;
use Syncany\Api\Task\ExePluginUploadTask;
use Syncany\Api\Task\JarPluginUploadTask;
use Syncany\Api\Util\Log;

class PluginController extends Controller
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

        Log::info(__CLASS__, "Put request for plugin $pluginId received. Authenticating ...");
        $this->authenticate("plugins-put-$pluginId", $methodArgs, $requestArgs);

        $checksum = ControllerHelper::validateChecksum($methodArgs);
        $fileName = ControllerHelper::validateFileName($methodArgs);
        $snapshot = ControllerHelper::validateIsSnapshot($methodArgs);
        $os = ControllerHelper::validateOperatingSystem($methodArgs);
        $arch = ControllerHelper::validateArchitecture($methodArgs);

        $type = $this->validateType($methodArgs);

        switch ($type) {
            case "jar":
                $task = new JarPluginUploadTask($fileHandle, $fileName, $checksum, $snapshot, $os, $arch, $pluginId);
                break;

            case "deb":
                $task = new DebPluginUploadTask($fileHandle, $fileName, $checksum, $snapshot, $os, $arch, $pluginId);
                break;

            case "app.zip":
                $task = new AppZipPluginUploadTask($fileHandle, $fileName, $checksum, $snapshot, $os, $arch, $pluginId);
                break;

            case "exe":
                $task = new ExePluginUploadTask($fileHandle, $fileName, $checksum, $snapshot, $os, $arch, $pluginId);
                break;

            default:
                throw new ServerErrorHttpException("Type not supported.");
        }

        $task->execute();
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
        if (isset($methodArgs['pluginId']) || isset($requestArgs[0])) {
            $pluginId = (isset($methodArgs['pluginId'])) ? $methodArgs['pluginId'] : $requestArgs[0];

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

    private function printResponseXml($code, $message, $plugins) {
        $downloadBaseUrl = Config::get("plugins.base-url");

        header("Content-Type: application/xml");

        echo "<?xml version=\"1.0\"?>\n";
        echo "<pluginListResponse xmlns=\"http://syncany.org/plugins/1/list\">\n";
        echo "	<code>$code</code>\n";
        echo "	<message>$message</message>\n";
        echo "	<plugins>\n";

        foreach ($plugins as $plugin) {
            $downloadUrl = $downloadBaseUrl . $plugin->getFilenameFull();

            echo "		<pluginInfo>\n";
            echo "			<pluginId>{$plugin->getId()}</pluginId>\n";
            echo "			<pluginName>{$plugin->getName()}</pluginName>\n";
            echo "			<pluginVersion>{$plugin->getVersion()}</pluginVersion>\n";
            echo "			<pluginOperatingSystem>{$plugin->getOperatingSystem()}</pluginOperatingSystem>\n";
            echo "			<pluginArchitecture>{$plugin->getArchitecture()}</pluginArchitecture>\n";
            echo "			<pluginDate>{$plugin->getDate()}</pluginDate>\n";
            echo "			<pluginAppMinVersion>{$plugin->getAppMinVersion()}</pluginAppMinVersion>\n";
            echo "			<pluginRelease>{$plugin->getRelease()}</pluginRelease>\n";
            echo "			<pluginConflictsWith>{$plugin->getConflictsWith()}</pluginConflictsWith>\n";
            echo "			<downloadUrl>{$downloadUrl}</downloadUrl>\n";
            echo "			<sha256sum>{$plugin->getSha256sum()}</sha256sum>\n";
            echo "		</pluginInfo>\n";
        }

        echo "	</plugins>\n";
        echo "</pluginListResponse>\n";

        exit;
    }
}