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

namespace Syncany\Api\Task;

use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Persistence\Database;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\Log;
use Syncany\Api\Util\StringUtil;

class JarPluginReleaseUploadTask extends PluginReleaseUploadTask
{
	private $manifest;

	public function execute()
	{
        Log::info(__CLASS__, __METHOD__, "Processing uploaded JAR plugin release file ...");

        $tempDirContext = "plugins/" . $this->pluginId . "/jar";

		$tempDir = FileUtil::createTempDir($tempDirContext);
		$tempFile = FileUtil::writeToTempFile($this->fileHandle, $tempDir, ".jar");

		$this->validateChecksum($tempFile);
		$this->readManifest($tempFile);

		$targetFile = $this->moveFile($tempFile);

		$this->createLatestLink($targetFile);
		$this->addDatabaseEntry($targetFile);

		FileUtil::deleteTempDir($tempDir);
	}

	private function readManifest(TempFile $tempFile)
	{
		$manifestFileContents = FileUtil::readZipFileEntry($tempFile->getFile(), "META-INF/MANIFEST.MF");

		if (!$manifestFileContents) {
			throw new BadRequestHttpException("Cannot read MANIFEST.MF from JAR file");
		}

		$this->manifest = FileUtil::parseJarManifest($manifestFileContents);

		if (!$this->manifest) {
			throw new BadRequestHttpException("Plugin manifest cannot be parsed.");
		}

		if (!isset($this->manifest['Plugin-Id']) || !isset($this->manifest['Plugin-Name']) || !isset($this->manifest['Plugin-Version'])
			|| !isset($this->manifest['Plugin-Date']) || !isset($this->manifest['Plugin-App-Min-Version'])
			|| !isset($this->manifest['Plugin-Release'])) {

			throw new BadRequestHttpException("Plugin manifest not valid. Missing arguments.");
		}

		if ($this->manifest['Plugin-Id'] != $this->pluginId) {
			throw new BadRequestHttpException("Plugin ID in manifest does not match plugin ID in request.");
		}
	}

	protected function getLatestLinkBasename()
	{
		$snapshotSuffix = ($this->snapshot) ? "-snapshot" : "";
		$osSuffix = (isset($this->os) && $this->os != "" && $this->os != "all") ? "-" . $this->os : "";
		$archSuffix = (isset($this->arch) && $this->arch != "" && $this->arch != "all") ? "-" . $this->arch : "";

		return StringUtil::replace("syncany-plugin-latest{snapshot}-{id}{os}{arch}.jar", array(
			"id" => $this->pluginId,
			"snapshot" => $snapshotSuffix,
			"os" => $osSuffix,
			"arch" => $archSuffix,
		));
	}

	private function addDatabaseEntry($targetFile)
	{
		$pluginId = $this->pluginId;
		$pluginName = $this->manifest['Plugin-Name'];
		$pluginVersion = $this->manifest['Plugin-Version'];
		$pluginOperatingSystem = (isset($this->manifest['Plugin-Operating-System'])) ? $this->manifest['Plugin-Operating-System'] : "all";
		$pluginArchitecture = (isset($this->manifest['Plugin-Architecture'])) ? $this->manifest['Plugin-Architecture'] : "all";
		$pluginDate = $this->manifest['Plugin-Date'];
		$pluginAppMinVersion = $this->manifest['Plugin-App-Min-Version'];
		$pluginRelease = ($this->snapshot) ? 0 : 1;
		$pluginConflictsWith = (isset($this->manifest['Plugin-Conflicts-With'])) ? $this->manifest['Plugin-Conflicts-With'] : "";
		$sha256sum = $this->checksum;
		$filenameBasename = basename($targetFile);
		$filenameFull = substr($targetFile, strlen($this->pathPluginDist)+1);

		$statement = Database::prepareStatementFromResource("plugins-write", __NAMESPACE__, "plugins.insert.sql");

		$statement->bindValue(':pluginId', $pluginId, \PDO::PARAM_STR);
		$statement->bindValue(':pluginName', $pluginName, \PDO::PARAM_STR);
		$statement->bindValue(':pluginVersion', $pluginVersion, \PDO::PARAM_STR);
		$statement->bindValue(':pluginOperatingSystem', $pluginOperatingSystem, \PDO::PARAM_STR);
		$statement->bindValue(':pluginArchitecture', $pluginArchitecture, \PDO::PARAM_STR);
		$statement->bindValue(':pluginDate', $pluginDate, \PDO::PARAM_STR);
		$statement->bindValue(':pluginAppMinVersion', $pluginAppMinVersion, \PDO::PARAM_STR);
		$statement->bindValue(':pluginRelease', $pluginRelease, \PDO::PARAM_STR);
		$statement->bindValue(':pluginConflictsWith', $pluginConflictsWith, \PDO::PARAM_INT);
		$statement->bindValue(':sha256sum', $sha256sum, \PDO::PARAM_STR);
		$statement->bindValue(':filenameBasename', $filenameBasename, \PDO::PARAM_STR);
		$statement->bindValue(':filenameFull', $filenameFull, \PDO::PARAM_STR);

		if (!$statement->execute()) {
			throw new ServerErrorHttpException("Cannot insert plugin to database.");
		}
	}
}
