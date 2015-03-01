<?php

namespace Syncany\Api\Controller;

use Naneau\SemVer\Compare;
use Naneau\SemVer\Parser;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\Plugin;
use Syncany\Api\Persistence\Database;
use Syncany\Api\Task\PluginJarUploadTask;

class PluginsController extends Controller
{
    const DOWNLOAD_BASE_URL = "https://www.syncany.org/dist/plugins/";

    public function get(array $methodArgs, array $requestArgs)
    {
        // Check request params
        $appVersion = $this->getAppVersion($methodArgs);
        $pluginId = $this->getPluginId($methodArgs, $requestArgs);
        $operatingSystem = $this->getOperatingSystem($methodArgs);
        $architecture = $this->getArchitecture($methodArgs);
        $includeSnapshots = $this->getIncludeSnapshot($methodArgs);

        // Get data
        $plugins = ($pluginId)
            ? $this->queryWithPluginId($pluginId, $operatingSystem, $architecture, $includeSnapshots)
            : $this->queryWithoutPluginId($operatingSystem, $architecture, $includeSnapshots);

        $compatiblePlugins = $this->getCompatiblePlugins($appVersion, $plugins);

        // Print XML
        $this->printResponseXml(200, "OK", $compatiblePlugins);
        exit;
    }

    public function getList(array $methodArgs, array $requestArgs)
    {
        $this->get($methodArgs, $requestArgs);
    }

    public function put(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        $pluginId = $this->getPluginId($methodArgs, $requestArgs);

        if (!$pluginId) {
            throw new BadRequestHttpException("Invalid request, no plugin identifier given");
        }

        $this->authenticate("plugins-put-$pluginId", $methodArgs, $requestArgs);

        $checksum = $this->getChecksum($methodArgs);
        $fileName = $this->getFileName($methodArgs);
        $snapshot = $this->getIsSnapshot($methodArgs);
        $type = $this->getType($methodArgs);

        switch ($type) {
            case "jar":
                $task = new PluginJarUploadTask($fileHandle, $fileName, $checksum, $snapshot, $pluginId);
                break;

            case "deb":
            case "app.zip":
            case "exe":
            default:
                throw new ServerErrorHttpException("Not yet supported.");
        }

        $task->execute();
    }

    private function getAppVersion(array $methodArgs)
    {
        if (!isset($methodArgs['appVersion'])) {
            throw new BadRequestHttpException("Invalid request. appVersion is required.");
        }

        try {
            $givenAppVersion = $methodArgs['appVersion'];
            return Parser::parse($givenAppVersion);
        }
        catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid request. appVersion is invalid.");
        }
    }

    private function getPluginId($methodArgs, $requestArgs)
    {
        if (isset($methodArgs['pluginId']) || isset($requestArgs[0])) {
            $pluginId = (isset($methodArgs['pluginId'])) ? $methodArgs['pluginId'] : $requestArgs[0];

            if (!preg_match('/^[a-z0-9]+/i', $pluginId)) {
                throw new BadRequestHttpException("Invalid request. Plugin identifier is invalid.");
            }

            return $pluginId;
        }
        else {
            return false;
        }
    }

    private function getFileName($methodArgs)
    {
        if (!isset($methodArgs['filename']) || !preg_match('/^[-.+_a-z0-9]+$/i', $methodArgs['filename'])) {
            throw new BadRequestHttpException("No or invalid filename argument given.");
        }

        return $methodArgs['filename'];
    }

    private function getChecksum($methodArgs)
    {
        if (!isset($methodArgs['checksum']) || !preg_match('/^[a-f0-9]+$/i', $methodArgs['checksum'])) {
            throw new BadRequestHttpException("No or invalid checksum argument given.");
        }

        return $methodArgs['checksum'];
    }

    private function getType($methodArgs)
    {
        if (!isset($methodArgs['type']) || !in_array($methodArgs['type'], array("jar", "deb", "app.zip", "exe"))) {
            throw new BadRequestHttpException("No or invalid type argument given.");
        }

        return $methodArgs['type'];
    }

    private function getIsSnapshot($methodArgs)
    {
        return isset($methodArgs['snapshot']) && $methodArgs['snapshot'] == "true";
    }

    private function getIncludeSnapshot($methodArgs)
    {
        return isset($methodArgs['snapshots']) && $methodArgs['snapshots'] == "true";
    }

    private function getOperatingSystem($methodArgs)
    {
        $os = (isset($methodArgs['os'])) ? $methodArgs['os'] : "all";
        $os = ($os == "mac") ? "macosx" : $os; // Hack for Mac OSX

        if (!in_array($os, array("all", "linux", "windows", "macosx"))) {
            throw new BadRequestHttpException("Invalid request. Operating System (os) invalid.");
        }

        return $os;
    }

    private function getArchitecture($methodArgs)
    {
        $arch = (isset($methodArgs['arch'])) ? $methodArgs['arch'] : "all";

        if (!in_array($arch, array("all", "x86", "x86_64"))) {
            throw new BadRequestHttpException("Invalid request. Architecture (arch) invalid.");
        }

        return $arch;
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
        header("Content-Type: application/xml");

        echo "<?xml version=\"1.0\"?>\n";
        echo "<pluginListResponse xmlns=\"http://syncany.org/plugins/1/list\">\n";
        echo "	<code>$code</code>\n";
        echo "	<message>$message</message>\n";
        echo "	<plugins>\n";

        foreach ($plugins as $plugin) {
            $downloadUrl = self::DOWNLOAD_BASE_URL . $plugin->getFilenameFull();

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