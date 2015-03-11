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
use Syncany\Api\Model\TempFile;

/**
 * Utility class for the Debian/APT archive management tool 'reprepro'.
 *
 * <p>Requirements to use this class:
 * <ul>
 *   <li>The managed repositories must be initialized.</li>
 *   <li>The managed repositories have to be writable by the user that
 *       calls the function (no sudo-ing).</li>
 *   <li>The repository path is expected to be configured using the
 *       {@link Config} class, with the config key 'paths.repo-{codename}'</li>
 *   <li>The GnuPG keyring is expected to have no password,  must be
 *       configured using the {@link Config} class, with the config
 *       key 'paths.gnupg'.</li>
 * </ul>
 *
 * @author Philipp Heckel <philipp.heckel@gmail.com>
 */
class RepreproUtil
{
    const REPREPRO_COMMAND_INCLUDEDEB_FORMAT = 'reprepro --basedir "{basedir}/" --gnupghome "{gnupghome}" --component main includedeb {codename} "{debfile}"';

    const REPREPRO_GNUPG_CONFIG_KEY = 'paths.gnupg';
    const REPREPRO_REPO_CONFIG_KEY_FORMAT = 'paths.apt.repo-{codename}';

    /**
     * Calls the 'reprepro includedeb' command for the given Debian archive (.deb)
     * in the upload folder. The method expects the environment to be set up as described
     * in the class documentation (see above).
     *
     * @param $codename Repository codename, e.g. snapshot or release
     * @param TempFile $debFile Temporary Debian archive
     * @throws ConfigException If called with the wrong parameters
     * @throws ServerErrorHttpException If calling reprepro fails
     */
    public static function includeDeb($codename, TempFile $debFile)
    {
        if ($codename != "snapshot" && $codename != "release") {
            throw new ConfigException("Codename has to be 'release' or 'snapshot'");
        }

        $repoConfigKey = StringUtil::replace(self::REPREPRO_REPO_CONFIG_KEY_FORMAT, array(
            "codename" => $codename
        ));

        $baseDir = Config::get($repoConfigKey);
        $gnupgHomeDir = Config::get(self::REPREPRO_GNUPG_CONFIG_KEY);

        $command = StringUtil::replace(self::REPREPRO_COMMAND_INCLUDEDEB_FORMAT, array(
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
