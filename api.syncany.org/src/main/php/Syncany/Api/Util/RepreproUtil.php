<?php

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
