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

use Naneau\SemVer\Parser;
use Syncany\Api\Exception\Http\BadRequestHttpException;

class ControllerHelper
{
    public static function validateAppVersion(array $methodArgs)
    {
        if (!isset($methodArgs['appVersion'])) {
            throw new BadRequestHttpException("Invalid request. appVersion is required.");
        }

        try {
            $givenAppVersion = $methodArgs['appVersion'];
            Parser::parse($givenAppVersion); // throws InvalidArgumentException!

            return $givenAppVersion; // Do NOT return result of parser (differs!)
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid request. appVersion is invalid.");
        }
    }

    public static function validateFileName($methodArgs)
    {
        if (!isset($methodArgs['filename']) || !preg_match('/^[-.+_~a-z0-9]+$/i', $methodArgs['filename'])) {
            throw new BadRequestHttpException("No or invalid filename argument given.");
        }

        return $methodArgs['filename'];
    }

    public static function validateChecksum($methodArgs)
    {
        if (!isset($methodArgs['checksum']) || !preg_match('/^[a-f0-9]+$/i', $methodArgs['checksum'])) {
            throw new BadRequestHttpException("No or invalid checksum argument given.");
        }

        return $methodArgs['checksum'];
    }

    public static function validateIsSnapshot($methodArgs)
    {
        return isset($methodArgs['snapshot']) && $methodArgs['snapshot'] == "true";
    }

    public static function validateWithSnapshots($methodArgs)
    {
        return isset($methodArgs['snapshots']) && $methodArgs['snapshots'] == "true";
    }

    public static function validateOperatingSystem($methodArgs)
    {
        $os = (isset($methodArgs['os'])) ? $methodArgs['os'] : "all";
        $os = ($os == "mac") ? "macosx" : $os; // Hack for Mac OSX

        if (!in_array($os, array("all", "linux", "windows", "macosx"))) {
            throw new BadRequestHttpException("Invalid request. Operating System (os) invalid.");
        }

        return $os;
    }

    public static function validateArchitecture($methodArgs)
    {
        $arch = (isset($methodArgs['arch'])) ? $methodArgs['arch'] : "all";

        if (!in_array($arch, array("all", "x86", "x86_64"))) {
            throw new BadRequestHttpException("Invalid request. Architecture (arch) invalid.");
        }

        return $arch;
    }

    public static function validateAppDate($methodArgs)
    {
        return (isset($methodArgs['date'])) ? $methodArgs['date'] : "";
    }
}