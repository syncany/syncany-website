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
use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Util\FileUtil;
use Syncany\Api\Util\StringUtil;

class BadgesController extends Controller
{
    const COLOR_GREEN = "#4c1";
    const COLOR_YELLOW = "#db2";
    const COLOR_RED = "#f33";

    public function getLines(array $methodArgs, array $requestArgs)
    {
        $clocXmlFile = Config::get("paths.badges.lines");
        $lines = $this->parseForPattern($clocXmlFile, "/\<total.+code=\"([.0-9]+)\"/i", 4096);

        $linesText = ($lines !== false) ? round(intval($lines)/1000) . " k" : "n/a";

        $this->printBadgeSvg("src code", $linesText, self::COLOR_GREEN);
        exit;
    }

    public function getTests(array $methodArgs, array $requestArgs)
    {
        $testsIndexHtmlFile = Config::get("paths.badges.tests");
        $percentage = $this->parseForPattern($testsIndexHtmlFile, "/class=\"percent\">([.0-9]+)\%\</i", 4096);

        $percentageText = ($percentage !== false) ? intval($percentage) . "%" : "n/a";
        $color = ($percentage == 100) ? self::COLOR_GREEN : ($percentage > 90) ? self::COLOR_YELLOW : self::COLOR_RED;

        $this->printBadgeSvg("unit tests", $percentageText, $color);
        exit;
    }

    public function getCoverage(array $methodArgs, array $requestArgs)
    {
        $coverageXmlFile = Config::get("paths.badges.coverage");
        $percentage = $this->parseForPattern($coverageXmlFile, "/\<coverage line-rate=\"([.0-9]+)\"/i", 4096);

        $percentageText = ($percentage !== false) ? round(floatval($percentage)*100) . "%" : "n/a";
        $color = ($percentage > 80) ? self::COLOR_GREEN : ($percentage > 70) ? self::COLOR_YELLOW : self::COLOR_RED;

        $this->printBadgeSvg("coverage", $percentageText, $color);
        exit;
    }

    public function getTips(array $methodArgs, array $requestArgs)
    {
        $tempTipsDir = $this->createOrGetTempTipsDir();
        $tempUglyBadgeFile = $this->downloadOrGetTempTipsFile($tempTipsDir);

        $tips = $this->parseForPattern($tempUglyBadgeFile, "/([\d.]+)\sɃ/", 10240);
        $tipsText = ($tips !== false) ?  sprintf("%.2f cɃ", floatval($tips)*100) : "n/a";

        $this->printBadgeSvg("tip4commit", $tipsText, self::COLOR_GREEN, 125, 0.4);
        exit;
    }

    private function parseForPattern($file, $pattern, $maxBytes) {
        $searchValue = false;

        $handle = @fopen($file, "r");

        if ($handle) {
            $readBytes = 0;

            while (($buffer = fgets($handle, 4096)) !== false) {
                $readBytes += strlen(trim($buffer));

                if (preg_match($pattern, $buffer, $m)) {
                    $searchValue = $m[1];
                    break;
                }

                if ($readBytes >= $maxBytes) {
                    break;
                }
            }

            @fclose($handle);
        }

        return $searchValue;
    }

    private function printBadgeSvg($leftText, $rightText, $color = self::COLOR_GREEN, $width = 99, $relativeBoxSize = 0.35)
    {
        // No caching
        $now = gmdate("D, d M Y H:i:s") . " GMT";

        header("Expires: $now");
        header("Last-Modified: $now");
        header("Pragma: no-cache");
        header("Cache-Control: no-cache, must-revalidate");

        // Dump SVG
        header('Content-type: image/svg+xml');

        $colorBoxWidth = floor($width * $relativeBoxSize);
        $colorBoxLeft = $width - $colorBoxWidth;
        $colorBoxTextCenter = ($width - $colorBoxWidth) + $colorBoxWidth/2;
        $labelTextCenter = floor($width * (1 - $relativeBoxSize) / 2);

        $svgSkeleton = FileUtil::readResourceFile(__NAMESPACE__, "badges.skeleton.svg");
        $svgSource = StringUtil::replace($svgSkeleton, array(
            "width" => $width,
            "color" => $color,
            "colorBoxWidth" => $colorBoxWidth,
            "colorBoxLeft" => $colorBoxLeft,
            "colorBoxTextCenter" => $colorBoxTextCenter,
            "colorBoxText" => $rightText,
            "labelText" => $leftText,
            "labelTextCenter" => $labelTextCenter
        ));

        echo $svgSource;
    }

    private function createOrGetTempTipsDir()
    {
        if (!defined('UPLOAD_PATH')) {
            throw new ConfigException("Upload path not set via CONFIG_PATH.");
        }

        $tempDir = UPLOAD_PATH . "/badges/tips";

        if (!is_dir($tempDir) && !mkdir($tempDir, 0777, true)) {
            throw new ConfigException("Cannot create temporary tips directory");
        }

        return $tempDir;
    }

    private function downloadOrGetTempTipsFile($tempTipsDir)
    {
        $uglyBadgeUrl = Config::get("paths.badges.tips-url");

        $tempUglyBadgeFile = $tempTipsDir . "/badge.svg";
        $notFoundOrOutdated = !file_exists($tempUglyBadgeFile) || filemtime($tempUglyBadgeFile) < time() - 3600;

        if ($notFoundOrOutdated) {
            file_put_contents($tempUglyBadgeFile, file_get_contents($uglyBadgeUrl));
        }

        return $tempUglyBadgeFile;
    }
}