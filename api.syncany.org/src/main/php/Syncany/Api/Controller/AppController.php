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

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Exception\Http\UnauthorizedHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Persistence\Database;
use Syncany\Api\Task\AppZipOsxNotifierReleaseUploadTask;
use Syncany\Api\Task\DebAppReleaseUploadTask;
use Syncany\Api\Task\DocsExtractZipUploadTask;
use Syncany\Api\Task\ExeAppReleaseUploadTask;
use Syncany\Api\Task\ReportsExtractZipUploadTask;
use Syncany\Api\Task\TarGzAppReleaseUploadTask;
use Syncany\Api\Task\ZipAppReleaseUploadTask;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\Log;
use Syncany\Api\Util\StringUtil;

/**
 * The app controller is responsible to handling application related requests, mainly
 * the upload of new main/core application releases and snapshots.
 *
 * @author Philipp Heckel <philipp.heckel@gmail.com>
 */
class AppController extends Controller
{
    public function get(array $methodArgs, array $requestArgs)
    {
        // Check request params
        $operatingSystem = ControllerHelper::validateOperatingSystem($methodArgs);
        $architecture = ControllerHelper::validateArchitecture($methodArgs);
        $includeSnapshots = ControllerHelper::validateWithSnapshots($methodArgs);

        // Get data
        $appList = $this->queryLatestAppList($operatingSystem, $architecture, $includeSnapshots);

        // Print XML
        $this->printResponseXml($appList);
        exit;
    }

    /**
     * This method handles the upload of a release or snapshot file, as well as the upload of reports
     * and documentation in the form of an archive. The uploaded files are validated and then placed in
     * the appropriate place to make them accessible to the end users.
     *
     * <p>The release/snapshot formats tar.gz, zip, exe and deb are simply put moved to the target download
     * page. The deb file is additionally put into the Debian/APT archive. The reports archive is extracted
     * and placed on the reports page, the docs archive is extracted and placed on the docs page.
     *
     * <p>Expected method arguments (in <tt>methodArgs</tt>) are:
     * <ul>
     *   <li>filename: Target filename of the uploaded file</li>
     *   <li>checksum: SHA-256 checksum of the uploaded file</li>
     *   <li>snapshot: Whether or not the uploaded file is a snapshot, or a release (true or false)</li>
     *   <li>type: Type of the upload (one in: tar.gz, zip, deb, exe, reports or docs)</li>
     * </ul>
     *
     * @param array $methodArgs GET arguments, expected are filename, checksum, snapshot and type
     * @param array $requestArgs No request arguments are expected by this method
     * @param FileHandle $fileHandle File handle to the uploaded file
     * @throws BadRequestHttpException If any of the given arguments is invalid
     * @throws UnauthorizedHttpException If the given signature does not match the expected signature
     * @throws ServerErrorHttpException If there is any unexpected server behavior
     */
    public function put(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        Log::info(__CLASS__, __METHOD__, "Put request for application received. Authenticating ...");
        $this->authorize("application-put", $methodArgs, $requestArgs);

        $checksum = ControllerHelper::validateChecksum($methodArgs);
        $fileName = ControllerHelper::validateFileName($methodArgs);
        $version = ControllerHelper::validateAppVersion($methodArgs);
        $date = ControllerHelper::validateAppDate($methodArgs);
        $snapshot = ControllerHelper::validateIsSnapshot($methodArgs);

        $type = $this->validateType($methodArgs);

        $task = $this->createTask($type, $fileHandle, $fileName, $checksum, $version, $date, $snapshot);
        $task->execute();
    }

    public function putOsxnotifier(array $methodArgs, array $requestArgs, FileHandle $fileHandle)
    {
        Log::info(__CLASS__, __METHOD__, "Put request for OSX notifier received. Authenticating ...");
        $this->authorize("osx-notifier-put", $methodArgs, $requestArgs);

        $checksum = ControllerHelper::validateChecksum($methodArgs);
        $fileName = ControllerHelper::validateFileName($methodArgs);
        $snapshot = ControllerHelper::validateIsSnapshot($methodArgs);

        $task = new AppZipOsxNotifierReleaseUploadTask($fileHandle, $fileName, $checksum, $snapshot);
        $task->execute();
    }

    private function validateType($methodArgs)
    {
        if (!isset($methodArgs['type']) || !in_array($methodArgs['type'], array("tar.gz", "zip", "deb", "exe", "docs", "reports"))) {
            throw new BadRequestHttpException("No or invalid type argument given.");
        }

        return $methodArgs['type'];
    }

    private function createTask($type, $fileHandle, $fileName, $checksum, $version, $date, $snapshot)
    {
        switch ($type) {
            case "tar.gz":
                return new TarGzAppReleaseUploadTask($fileHandle, $fileName, $checksum, $version, $date, $snapshot);

            case "zip":
                return new ZipAppReleaseUploadTask($fileHandle, $fileName, $checksum, $version, $date, $snapshot);

            case "deb":
                return new DebAppReleaseUploadTask($fileHandle, $fileName, $checksum, $version, $date, $snapshot);

            case "exe":
                return new ExeAppReleaseUploadTask($fileHandle, $fileName, $checksum, $version, $date, $snapshot);

            case "docs":
                return new DocsExtractZipUploadTask($fileHandle, $fileName, $checksum);

            case "reports":
                return new ReportsExtractZipUploadTask($fileHandle, $fileName, $checksum);

            default:
                throw new ServerErrorHttpException("Type not supported.");
        }
    }

    private function queryLatestAppList($operatingSystem, $architecture, $includeSnapshots)
    {
        $release = ($includeSnapshots) ? 0 : 1;
        $statement = Database::prepareStatementFromResource("app-read", __NAMESPACE__, "app.select-latest.sql");

        $statement->bindParam(':release', $release, \PDO::PARAM_INT);
        $statement->bindParam(':os', $operatingSystem, \PDO::PARAM_STR);
        $statement->bindParam(':arch', $architecture, \PDO::PARAM_STR);

        return $this->fetchLatestAppList($statement);
    }

    private function fetchLatestAppList(\PDOStatement $statement)
    {
        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        if (!$statement->execute()) {
            throw new ServerErrorHttpException("Cannot retrieve apps from database.");
        }

        $appList = array();

        while ($appArray = $statement->fetch()) {
            $appList[] = $appArray;
        }

        return $appList;
    }

    private function printResponseXml($appList) {
        header("Content-Type: application/xml");

        $firstApp = (count($appList) > 0) ? $appList[0] : false;

        if ($firstApp) {
            $this->printSuccessResponseXml(200, "OK", $firstApp, $appList);
        }
        else {
            $this->printFailureResponseXml(404, "No apps found");
        }
    }

    private function printSuccessResponseXml($code, $message, $firstApp, $appList)
    {
        $downloadBaseUrl = Config::get("app.base-url");

        $wrapperSkeleton = FileUtil::readResourceFile(__NAMESPACE__, "app.get-response.success.wrapper.skeleton.xml");
        $appInfoSkeleton = FileUtil::readResourceFile(__NAMESPACE__, "app.get-response.success.appinfo.skeleton.xml");

        $appInfoBlocks = array();

        foreach ($appList as $app) {
            $downloadUrl = $downloadBaseUrl . $app['fullpath'];

            $appInfoBlocks[] = StringUtil::replace($appInfoSkeleton, array(
                "type" => $app['type'],
                "checksum" => $app['checksum'],
                "downloadUrl" => $downloadUrl
            ));
        }

        $release = ($firstApp['release']) ? "true" : "false";
        $apps = join("\n", $appInfoBlocks);

        $xml = StringUtil::replace($wrapperSkeleton, array(
            "code" => $code,
            "message" => $message,
            "appVersion" => $firstApp['appVersion'],
            "date" => $firstApp['date'],
            "release" => $release,
            "apps" => $apps
        ));

        echo $xml;
        exit;
    }

    private function printFailureResponseXml($code, $message)
    {
        $failureXmlSkeleton = FileUtil::readResourceFile(__NAMESPACE__, "app.get-response.failure.skeleton.xml");

        $xml = StringUtil::replace($failureXmlSkeleton, array(
            "code" => $code,
            "message" => $message
        ));

        echo $xml;
        exit;
    }
}