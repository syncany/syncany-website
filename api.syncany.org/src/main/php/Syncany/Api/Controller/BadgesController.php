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
    const COLOR_BLUE = "#78bdf2";

    public function getLines(array $methodArgs, array $requestArgs)
    {
        $clocXmlFile = Config::get("paths.badges.lines");
        $lines = $this->parseForPattern($clocXmlFile, "/\<total.+code=\"([.0-9]+)\"/i", 0, 4096);

        $linesText = ($lines !== false) ? round(intval($lines)/1000) . " k" : "n/a";

        $this->printBadgeSvg("src code", $linesText, self::COLOR_GREEN);
        exit;
    }

    public function getTests(array $methodArgs, array $requestArgs)
    {
        $testsIndexHtmlFile = Config::get("paths.badges.tests");
        $percentage = $this->parseForPattern($testsIndexHtmlFile, "/class=\"percent\">([.0-9]+)\%\</i", 0, 4096);

        $percentageText = ($percentage !== false) ? intval($percentage) . "%" : "n/a";
        $color = ($percentage == 100) ? self::COLOR_GREEN : ($percentage > 90) ? self::COLOR_YELLOW : self::COLOR_RED;

        $this->printBadgeSvg("unit tests", $percentageText, $color);
        exit;
    }

    public function getCoverage(array $methodArgs, array $requestArgs)
    {
        $coverageXmlFile = Config::get("paths.badges.coverage");
        $missedAndCovered = $this->parseForPattern($coverageXmlFile, "/\<counter\s+type=\"INSTRUCTION\"\s+missed=\"([0-9]+)\"\s+covered=\"([0-9]+)\"/i", -400, 400);
        
        $percentage = ($missedAndCovered !== false) ? round($missedAndCovered[1]/($missedAndCovered[0]+$missedAndCovered[1])*100) : false;
        $percentageText = ($percentage !== false) ? $percentage . "%" : "n/a";
        $color = ($percentage > 80) ? self::COLOR_GREEN : ($percentage > 70) ? self::COLOR_YELLOW : self::COLOR_RED;

        $this->printBadgeSvg("coverage", $percentageText, $color);
        exit;
    }

    public function getTips(array $methodArgs, array $requestArgs)
    {
        $uglyBadgeUrl = Config::get("paths.badges.tips-url");

        $tempTipsDir = $this->createOrGetTempDir("tips");
        $tempUglyBadgeFile = $this->downloadOrGetTempSvgFile($tempTipsDir, $uglyBadgeUrl);

        $tips = $this->parseForPattern($tempUglyBadgeFile, "/([\d.]+)\sɃ/", 0, 10240);
        $tipsText = ($tips !== false) ?  sprintf("%.2f cɃ", floatval($tips)*100) : "n/a";

        $this->printBadgeSvg("tip4commit", $tipsText, self::COLOR_GREEN, 125, 0.4);
        exit;
    }

    public function getWaffle(array $methodArgs, array $requestArgs)
    {
        $uglyBadgeUrl = Config::get("paths.badges.waffle-url");

        $tempTipsDir = $this->createOrGetTempDir("waffle");
        $tempUglyBadgeFile = $this->downloadOrGetTempSvgFile($tempTipsDir, $uglyBadgeUrl);

        $needsHelpCount = $this->parseForPattern($tempUglyBadgeFile, '/y="13">(\d+)<\/text>/', 0, 10240);
        $needsHelpCountText = ($needsHelpCount !== false) ? $needsHelpCount : "n/a";

        $this->printBadgeSvg("Needs your help", $needsHelpCountText, self::COLOR_BLUE, 125, 0.19);
        exit;
    }

    public function getJavadoc(array $methodArgs, array $requestArgs)
    {
        $classCount = 0;
        $classHtmlFile = Config::get("paths.badges.javadoc");

        $handle = @fopen($classHtmlFile, "r");

        if ($handle) {
            while (!feof($handle)) {
                $line = fgets($handle);

                if (preg_match('/<li><a/', $line)) {
                    $classCount++;
                }
            }

            fclose($handle);
        }

        $color = ($classCount > 0) ? self::COLOR_GREEN : self::COLOR_RED;
        $this->printBadgeSvg("javadoc", $classCount . " classes", $color, 125, 0.58);
        exit;
    }

    private function parseForPattern($file, $pattern, $offset, $maxBytes) {
        $searchValue = false;

        $handle = @fopen($file, "r");

        if ($handle) {
            $readBytes = 0;

            if ($offset > 0) {
                fseek($handle, $offset, SEEK_SET);
            } else if ($offset < 0) {
                fseek($handle, $offset, SEEK_END);
            }
            
            while (($buffer = fgets($handle, 4096)) !== false) {
                $readBytes += strlen(trim($buffer));

                if (preg_match($pattern, $buffer, $m)) {
                    array_shift($m);
                    
                    if (count($m) === 1) {
                        $searchValue = $m[0];
                    } else {
                        $searchValue = $m;
                    }
                    
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

    private function createOrGetTempDir($subDir)
    {
        if (!defined('UPLOAD_PATH')) {
            throw new ConfigException("Upload path not set via CONFIG_PATH.");
        }

        $tempDir = UPLOAD_PATH . "/badges/" . $subDir;

        if (!is_dir($tempDir) && !mkdir($tempDir, 0777, true)) {
            throw new ConfigException("Cannot create temporary tips directory");
        }

        return $tempDir;
    }

    private function downloadOrGetTempSvgFile($tempTipsDir, $uglyBadgeUrl)
    {
        $tempUglyBadgeFile = $tempTipsDir . "/badge.svg";
        $notFoundOrOutdated = !file_exists($tempUglyBadgeFile) || filemtime($tempUglyBadgeFile) < time() - 3600;

        if ($notFoundOrOutdated) {
            file_put_contents($tempUglyBadgeFile, file_get_contents($uglyBadgeUrl));
        }

        return $tempUglyBadgeFile;
    }
}

