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

use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Persistence\Database;
use Syncany\Api\Util\Log;

abstract class AppReleaseUploadTask extends ReleaseUploadTask
{
    protected $appVersion;
    protected $date;

    public function __construct(FileHandle $fileHandle, $fileName, $checksum, $appVersion, $date, $snapshot)
    {
        parent::__construct($fileHandle, $fileName, $checksum, $snapshot, "all", "all");

        $this->appVersion = $appVersion;
        $this->date = $date;
    }

    protected function getTargetFile()
    {
        $targetSubFolder = ($this->snapshot) ? "snapshots" : "releases";
        $targetFolder = $this->pathDist . "/" . $targetSubFolder;

        return $targetFolder . "/" . basename($this->fileName);
    }

    protected function addDatabaseEntry($type)
    {
        $release = ($this->snapshot) ? 0 : 1;
        $basename = basename($this->fileName);
        $fullpath = substr($this->getTargetFile(), strlen($this->pathDist) + 1);

        $statement = Database::prepareStatementFromResource("app-write", __NAMESPACE__, "app.insert.sql");

        $statement->bindValue(':type', $type, \PDO::PARAM_STR);
        $statement->bindValue(':appVersion', $this->appVersion, \PDO::PARAM_STR);
        $statement->bindValue(':os', $this->os, \PDO::PARAM_STR);
        $statement->bindValue(':arch', $this->arch, \PDO::PARAM_STR);
        $statement->bindValue(':date', $this->date, \PDO::PARAM_STR);
        $statement->bindValue(':release', $release, \PDO::PARAM_INT); # Note: 'release' is a reserved MySQL keyword!
        $statement->bindValue(':checksum', $this->checksum, \PDO::PARAM_STR);
        $statement->bindValue(':basename', $basename, \PDO::PARAM_STR);
        $statement->bindValue(':fullpath', $fullpath, \PDO::PARAM_STR);

        if (!$statement->execute()) {
            $errorMessage = join("\n", $statement->errorInfo());
            Log::error(__CLASS__, __METHOD__, "Insert in database failed:\n\n$errorMessage");

            throw new ServerErrorHttpException("Cannot insert app to database.");
        }
    }
}
