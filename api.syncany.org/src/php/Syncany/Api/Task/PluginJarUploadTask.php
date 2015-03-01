<?php

namespace Syncany\Api\Task;

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\Http\BadRequestHttpException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;
use Syncany\Api\Persistence\Database;
use Syncany\Api\Util\FileUtil;

class PluginJarUploadTask extends UploadTask
{
	private $pathPluginDist;
	private $pluginId;
	private $manifest;

	public function __construct(FileHandle $fileHandle, $fileName, $checksum, $snapshot, $pluginId)
	{
		parent::__construct($fileHandle, $fileName, $checksum, $snapshot);

		$this->pathPluginDist = Config::get("paths.plugindist");
		$this->pluginId = $pluginId;
	}

	public function execute()
	{
		$tempDirContext = "plugins/" . $this->pluginId . "/jar";

		$tempDir = FileUtil::createTempDir($tempDirContext);
		$tempFile = FileUtil::writeToTempFile($this->fileHandle, $tempDir, ".jar");

		$this->validateChecksum($tempFile);
		$this->readManifest($tempFile);

		$targetFile = $this->moveFile($tempFile);

		$this->createSymlinks($targetFile);
		$this->addDatabaseEntry($targetFile);
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

	private function moveFile(TempFile $tempFile)
	{
		$pluginTargetFolder = $this->getTargetFolder();
		$pluginTargetJarFile = $pluginTargetFolder . "/" . basename($this->fileName);

		if (!file_exists($pluginTargetFolder)) {
			if (!mkdir($pluginTargetFolder, 0755, true)) {
				throw new ServerErrorHttpException("Cannot create target plugin folder");
			}
		}

		if (!is_writable($pluginTargetFolder)) {
			throw new ServerErrorHttpException("Cannot write to target plugin folder");
		}

		if (!rename($tempFile->getFile(), $pluginTargetJarFile)) {
			throw new ServerErrorHttpException("Cannot move JAR file");
		}

		return $pluginTargetJarFile;
	}

	private function createSymlinks($targetFile)
	{
		if (isset($this->manifest['Plugin-Operating-System'])
			&& $this->manifest['Plugin-Operating-System'] != "all"
			&& $this->manifest['Plugin-Operating-System'] != "") {

			$targetLinkNameOperatingSystemSuffix = "-" . $this->manifest['Plugin-Operating-System'];
		}
		else {
			$targetLinkNameOperatingSystemSuffix = "";
		}

		if (isset($this->manifest['Plugin-Architecture'])
			&& $this->manifest['Plugin-Architecture'] != "all"
			&& $this->manifest['Plugin-Architecture'] != "") {

			$targetLinkNameArchitectureSuffix = "-" . $this->manifest['Plugin-Architecture'];
		}
		else {
			$targetLinkNameArchitectureSuffix = "";
		}

		if ($this->snapshot) {
			$targetLinkBasename = "syncany-plugin-" . $this->pluginId . "-latest-snapshot" . $targetLinkNameOperatingSystemSuffix . $targetLinkNameArchitectureSuffix . ".jar";
		}
		else {
			$targetLinkBasename = "syncany-plugin-" . $this->pluginId . "-latest" . $targetLinkNameOperatingSystemSuffix . $targetLinkNameArchitectureSuffix . ".jar";
		}

		$pluginTargetFolder = $this->getTargetFolder();
		$targetLinkFile = $pluginTargetFolder . "/" . $targetLinkBasename;

		@unlink($targetLinkFile);

		if (!symlink($targetFile, $targetLinkFile)) {
			throw new ServerErrorHttpException("Cannot create symlink");
		}
	}

	private function getTargetFolder()
	{
		$pluginTargetSubFolder = ($this->snapshot) ? "snapshots" : "releases";
		$pluginTargetFolder = $this->pathPluginDist . "/" . $pluginTargetSubFolder . "/" . $this->pluginId;

		return $pluginTargetFolder;
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
