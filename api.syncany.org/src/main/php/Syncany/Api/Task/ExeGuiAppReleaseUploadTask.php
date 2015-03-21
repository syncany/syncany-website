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
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\Log;
use Syncany\Api\Util\StringUtil;

class ExeGuiAppReleaseUploadTask extends GuiAppReleaseUploadTask
{
    public function execute()
    {
        Log::info(__CLASS__, __METHOD__, "Processing uploaded EXE app release file ...");

        $this->validateOperatingSystem();

        $tempDirContext = "plugins/gui/exe";
        $tempDir = FileUtil::createTempDir($tempDirContext);
        $tempFile = FileUtil::writeToTempFile($this->fileHandle, $tempDir, ".exe");

        $this->validateChecksum($tempFile);

        $targetFile = $this->moveFile($tempFile);
        $this->createLatestLink($targetFile);
        $this->addDatabaseEntry("gui", "exe");

        FileUtil::deleteTempDir($tempDir);
    }

    protected function getLatestLinkBasename()
    {
        $snapshotSuffix = ($this->snapshot) ? "-snapshot" : "";
        $archSuffix = (isset($this->arch) && $this->arch != "" && $this->arch != "all") ? "-" . $this->arch : "";

        return StringUtil::replace("syncany-latest{snapshot}{arch}.exe", array(
            "snapshot" => $snapshotSuffix,
            "arch" => $archSuffix
        ));
    }

    private function validateOperatingSystem()
    {
        if ($this->os != "windows") {
            throw new BadRequestHttpException("Invalid operating system. Exe file must be for Windows.");
        }
    }
}
