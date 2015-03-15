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

namespace Syncany\Api\Util;

use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;

/**
 * Utility class for file-specific methods.
 *
 * <p>Most of the methods in this class can only be used if a bootstrap
 * class has been run before, namely if the following global constants
 * have been defined: RESOURCES_PATH, UPLOAD_PATH, CONFIG_PATH.
 *
 * @author Philipp Heckel <philipp.heckel@gmail.com>
 */
class FileUtil
{
    /**
     * Maximum accepted file size for this API (security measure).
     */
    const MAX_WRITE_FILE_SIZE = 52428800; // 50 MB

    /**
     * Read a key/value properties file from the config directory and
     * return a corresponding array.
     *
     * <p>The properties file will be read from
     * <tt>CONFIG_PATH/[configContext]/[configName]</tt>.
     *
     * @param string $configContext Config path directory, e.g. "keys", "database", or "config"
     * @param string $configName Config file name (basename), e.g. "keys" (for keys.properties)
     * @return array Associative array representing the properties file
     * @throws ConfigException If the given config context or name is invalid
     */
    public static function readPropertiesFile($configContext, $configName)
    {
        $propertiesFile = self::getConfigFileName($configContext, $configName);

        $properties = array();
        $lines = explode("\n", file_get_contents($propertiesFile));

        foreach ($lines as $line) {
            if (!preg_match('/^\s*#/', $line) && preg_match('/^([^=]+)\s*=\s*(.*)$/', $line, $m)) {
                $key = trim($m[1]);
                $value = trim($m[2]);

                $properties[$key] = $value;
            }
        }

        return $properties;
    }

    public static function readResourceFile($namespace, $relativePath)
    {
        if (!defined('RESOURCES_PATH')) {
            throw new ConfigException("Resources path not set via RESOURCES_PATH.");
        }

        $absoluteFilePath = RESOURCES_PATH . '/' . str_replace('\\', '/', $namespace) . '/' . $relativePath;

        if (!file_exists($absoluteFilePath)) {
            throw new ConfigException("File file does not exist: " . $absoluteFilePath);
        }

        return file_get_contents($absoluteFilePath);
    }

    public static function createTempDir($uploadContext)
    {
        if (!defined('UPLOAD_PATH')) {
            throw new ConfigException("Upload path not set via CONFIG_PATH.");
        }

        if (!preg_match('/^[-_\/a-z0-9]+$/', $uploadContext)) {
            throw new ConfigException("Invalid upload context passed. Illegal characters.");
        }

        $tempDir = UPLOAD_PATH . "/" . $uploadContext . "/" . time() . "-" . StringUtil::generateRandomString(7);

        if (!mkdir($tempDir, 0777, true)) {
            throw new ConfigException("Cannot create upload directory");
        }

        return new TempFile($tempDir);
    }

    public static function writeToTempFile(FileHandle $sourceFileInputStream, TempFile $targetDir, $suffix = "")
    {
        if (!preg_match('/^[.a-z0-9]*$/i', $suffix)) {
            throw new ConfigException("Invalid suffix passed. Illegal characters.");
        }

        $tempFile = new TempFile($targetDir->getFile() . "/" . StringUtil::generateRandomString(5) . $suffix);

        Log::info(__CLASS__, __METHOD__, "Writing temp file " . $tempFile->getFile() . " ...");
        return self::writeToFile($sourceFileInputStream, $tempFile);
    }

    public static function writeToFile(FileHandle $sourceFileInputStream, TempFile $tempFile)
    {
        $tempFileHandle = new FileHandle(fopen($tempFile->getFile(), "w"));
        $writtenBytes = 0;

        while (!feof($sourceFileInputStream->getHandle())) {
            $buffer = fread($sourceFileInputStream->getHandle(), 8192);
            $writtenBytes += fwrite($tempFileHandle->getHandle(), $buffer);

            if ($writtenBytes > self::MAX_WRITE_FILE_SIZE) {
                throw new ConfigException("Uploaded file to big. For security reasons, we do not accept so large files.");
            }
        }

        fclose($sourceFileInputStream->getHandle());
        fclose($tempFileHandle->getHandle());

        return $tempFile;
    }

    public static function calculateChecksum(TempFile $file)
    {
        if (!file_exists($file->getFile())) {
            throw new ConfigException("Cannot calculate checksum. File does not exist.");
        }

        return hash_file("sha256", $file->getFile());
    }

    public static function extractZipArchive(TempFile $tempFile, TempFile $tempDir)
    {
        Log::info(__CLASS__, __METHOD__, "Extracting uploaded ZIP archive to " . $tempDir->getFile() . " ...");

        $zip = new \ZipArchive();

        if ($zip->open($tempFile->getFile()) === true) {
            $zip->extractTo($tempDir->getFile() . "/");
            $zip->close();
        } else {
            throw new ConfigException("Cannot extract ZIP archive.");
        }
    }

    public static function readZipFileEntry($zipFileName, $searchEntryName)
    {
        $zip = zip_open($zipFileName);

        if ($zip) {
            while ($zipEntry = zip_read($zip)) {
                $entryName = zip_entry_name($zipEntry);

                if ($entryName == $searchEntryName) {
                    if (zip_entry_open($zip, $zipEntry, "r")) {
                        $searchFileContents = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));

                        zip_entry_close($zipEntry);
                        zip_close($zip);

                        return $searchFileContents;
                    }
                }
            }

            zip_close($zip);
        }

        return false;
    }

    public static function parseJarManifest($manifestFileContents)
    {
        $manifest = array();
        $lines = explode("\n", $manifestFileContents);

        foreach ($lines as $line) {
            if (preg_match('/^([^:]+):\s*(.*)$/', $line, $m)) {
                $manifest[$m[1]] = trim($m[2]);
            }
        }

        return $manifest;
    }

    private static function getConfigFileName($configContext, $configName)
    {
        if (!defined('CONFIG_PATH')) {
            throw new ConfigException("Config path not set via CONFIG_PATH.");
        }

        if (!preg_match('/^[-_a-z0-9]+$/', $configContext) || !preg_match('/^[-_a-z0-9]+$/', $configName)) {
            throw new ConfigException("Invalid config context passed. Illegal characters.");
        }

        $configFile = CONFIG_PATH . "/" . $configContext . "/" . $configName . ".properties";

        FileUtil::checkLockInDirMustExist(CONFIG_PATH, $configFile);

        if (!file_exists($configFile)) {
            throw new ConfigException("Config file not found for context $configContext.");
        }

        return $configFile;
    }

    public static function deleteTempDir(TempFile $tempDir)
    {
        if (!defined('UPLOAD_PATH')) {
            throw new ConfigException("Upload path not set via CONFIG_PATH.");
        }

        self::deleteDir(UPLOAD_PATH, $tempDir->getFile());
    }

    public static function deleteDir($lockInDir, $dir)
    {
        if (is_dir($dir)) {
            self::checkLockInDirMustExist($lockInDir, $dir);

            Log::info(__CLASS__, __METHOD__, "Deleting directory '$dir' recursively ...");

            $directoryIterator = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
            $iteratorIterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($iteratorIterator as $path) {
                if ($path->isDir() && !$path->isLink()) {
                    rmdir($path->getPathname());
                } else {
                    unlink($path->getPathname());
                }
            }

            rmdir($dir);
        } else {
            Log::info(__CLASS__, __METHOD__, "Directory '$dir' is not a directory. Doing nothing.");
        }
    }

    public static function deleteFile($lockInDir, $file)
    {
        if (is_file($file)) {
            self::checkLockInDirMustExist($lockInDir, $file);

            Log::info(__CLASS__, __METHOD__, "Deleting '$file' ...");
            unlink($file);
        } else {
            Log::info(__CLASS__, __METHOD__, "File '$file' is not a file. Doing nothing.");
        }
    }

    public static function moveFile($sourceLockInDir, $sourceFile, $targetLockInDir, $targetFile)
    {
        self::checkLockInDirMustExist($sourceLockInDir, $sourceFile);
        self::checkLockInDirMustNotExist($targetLockInDir, $targetFile);

        Log::info(__CLASS__, __METHOD__, "Move '$sourceFile' to '$targetFile' ...");

        if (!rename($sourceFile, $targetFile)) {
            Log::info(__CLASS__, __METHOD__, "Move failed. Cannot move file to target $targetFile.");
            throw new ConfigException("Cannot move file to target folder");
        }
    }

    public static function checkLockInDirMustNotExist($lockInDir, $file)
    {
        self::checkLockInDir($lockInDir, $file);
    }

    public static function checkLockInDirMustExist($lockInDir, $file)
    {
        self::checkLockInDir($lockInDir, $file);

        $canonicalFile = FileUtil::canonicalize($file);

        if (!file_exists($canonicalFile)) {
            throw new ConfigException("File does not exist");
        }
    }

    private static function checkLockInDir($lockInDir, $file)
    {
        $canonicalFile = FileUtil::canonicalize($file);

        if (!$lockInDir || !is_dir($lockInDir) || $lockInDir == "/") {
            throw new ConfigException("Invalid lock-in directory");
        }

        if (!$canonicalFile || !is_string($canonicalFile)) {
            throw new ConfigException("Invalid file.");
        }

        if (substr($canonicalFile, 0, strlen($lockInDir)) != $lockInDir) {
            throw new ConfigException("Invalid file. Must reside in upload folder.");
        }
    }

    public static function makeParentDirs($file)
    {
        $parentDir = dirname($file);

        if (!is_dir($parentDir)) {
            Log::info(__CLASS__, __METHOD__, "Creating directories $parentDir ...");

            if (!mkdir($parentDir, 0755, true)) {
                throw new ConfigException("Creating parent directory failed");
            }
        }
    }

    public static function canonicalize($path)
    {
        $out = array();
        $parts = explode('/', $path);

        foreach ($parts as $index => $fold) {
            if ($fold == '' || $fold == '.') {
                continue;
            }

            if ($fold == '..' && $index > 0 && end($out) != '..') {
                array_pop($out);
            } else {
                $out[] = $fold;
            }
        }

        return ($path{0} == '/' ? '/' : '') . join('/', $out);
    }
}
