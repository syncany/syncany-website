<?php

namespace Syncany\Api\Controller;

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
            return Parser::parse($givenAppVersion);
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
}