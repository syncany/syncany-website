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

use Syncany\Api\Config\Config;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\StringUtil;

class BadgesController extends Controller
{
    const COLOR_GREEN = "#4c1";
    const COLOR_YELLOW = "#db2";
    const COLOR_RED = "#f33";

    private $fileLinesOfCode;
    private $fileTests;
    private $fileCoverage;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->fileLinesOfCode = Config::get("paths.badges.lines");
        $this->fileTests = Config::get("paths.badges.tests");
        $this->fileCoverage = Config::get("paths.badges.coverage");
    }

    public function getLines(array $methodArgs, array $requestArgs)
    {
        $lines = $this->getLinesOfCode($this->fileLinesOfCode);

        $this->printBadgeSvg("src code", $lines, self::COLOR_GREEN, " k");
        exit;
    }

    public function getTests(array $methodArgs, array $requestArgs)
    {
        $percentage = $this->getTestPercentage($this->fileTests);
        $color = ($percentage == 100) ? self::COLOR_GREEN : ($percentage > 90) ? self::COLOR_YELLOW : self::COLOR_RED;

        $this->printBadgeSvg("unit tests", $percentage, $color);
        exit;
    }

    public function getCoverage(array $methodArgs, array $requestArgs)
    {
        $percentage = $this->getCoveragePercentage($this->fileCoverage);
        $color = ($percentage > 80) ? self::COLOR_GREEN : ($percentage > 70) ? self::COLOR_YELLOW : self::COLOR_RED;

        $this->printBadgeSvg("coverage", $percentage, $color);
        exit;
    }

    private function getLinesOfCode($clocXmlFile) {
        $lines = -1;

        $handle = @fopen($clocXmlFile, "r");

        if ($handle) {
            $bytecount = 0;
            $bytemax = 4096;

            while (($buffer = fgets($handle, 4096)) !== false) {
                $bytecount += strlen(trim($buffer));

                if (preg_match("/\<total.+code=\"([.0-9]+)\"/i", $buffer, $m)) {
                    $lines = round($m[1]/1000);
                    break;
                }

                if ($bytecount >= $bytemax) {
                    break;
                }
            }

            @fclose($handle);
        }

        return $lines;
    }

    private function getTestPercentage($testIndexHtmlFile) {
        $coverage = -1;

        $handle = @fopen($testIndexHtmlFile, "r");

        if ($handle) {
            $bytecount = 0;
            $bytemax = 320000;

            while (($buffer = fgets($handle, 4096)) !== false) {
                $bytecount += strlen(trim($buffer));

                if (preg_match("/class=\"percent\">([.0-9]+)\%\</i", $buffer, $m)) {
                    $coverage = round($m[1]);
                    break;
                }

                if ($bytecount >= $bytemax) {
                    break;
                }
            }

            @fclose($handle);
        }

        return $coverage;
    }

    private function getCoveragePercentage($coverageXmlFile) {
        $coverage = -1;

        $handle = @fopen($coverageXmlFile, "r");
        if ($handle) {
            $bytecount = 0;
            $bytemax = 4096;

            while (($buffer = fgets($handle, 4096)) !== false) {
                $bytecount += strlen(trim($buffer));

                if (preg_match("/\<coverage line-rate=\"([.0-9]+)\"/i", $buffer, $m)) {
                    $coverage = round($m[1]*100);
                    break;
                }

                if ($bytecount >= $bytemax) {
                    break;
                }
            }

            @fclose($handle);
        }

        return $coverage;
    }

    private function printBadgeSvg($labelText, $percentage, $color, $suffix = "%")
    {
        $percentageText = $percentage < 0 ? "n/a" : intval($percentage) . $suffix;

        // No caching
        $now = gmdate("D, d M Y H:i:s") . " GMT";

        header("Expires: $now");
        header("Last-Modified: $now");
        header("Pragma: no-cache");
        header("Cache-Control: no-cache, must-revalidate");

        // Dump SVG
        header('Content-type: image/svg+xml');

        $svgSkeleton = FileUtil::readResourceFile(__NAMESPACE__, "badges.skeleton.svg");
        $svgSource = StringUtil::replace($svgSkeleton, array(
            "percentageText" => $percentageText,
            "labelText" => $labelText,
            "color" => $color
        ));

        echo $svgSource;
    }
}