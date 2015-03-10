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

use Syncany\Api\Config\Config;
use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Exception\Http\ServerErrorHttpException;
use Syncany\Api\Model\FileHandle;
use Syncany\Api\Model\TempFile;

class RepreproUtil
{
	const REPREPRO_COMMAND_FORMAT = 'reprepro --basedir "{basedir}/" --gnupghome "{gnupghome}" --component main includedeb {codename} "{debfile}"';

	public static function includeDeb($codename, TempFile $debFile)
	{
		if ($codename != "snapshot" && $codename != "release") {
			throw new ConfigException("Codename has to be 'release' or 'snapshot'");
		}

		$baseDir = Config::get("paths.apt.repo-$codename");
		$gnupgHomeDir = Config::get("paths.gnupg");

		$command = StringUtil::replace(self::REPREPRO_COMMAND_FORMAT, array(
			"basedir" => $baseDir,
			"gnupghome" => $gnupgHomeDir,
			"codename" => $codename,
			"debfile" => $debFile->getFile()
		));

		$output = array();
		$exitCode = -1;

		Log::info(__CLASS__, __METHOD__, "Calling reprepro with command: $command");
		exec($command, $output, $exitCode);

		Log::info(__CLASS__, __METHOD__, "Exit code = {code}, command output: {output}", array(
			"code" => $exitCode,
			"output" => join("\n", $output)
		));

		if ($exitCode != 0) {
			throw new ServerErrorHttpException("Calling reprepro failed with exit code $exitCode.");
		}
	}
}
