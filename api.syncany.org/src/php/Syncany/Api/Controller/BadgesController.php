<?php

namespace Syncany\Api\Controller;

class BadgesController
{
    const COLOR_GREEN = "#4c1";
    const COLOR_YELLOW = "#db2";
    const COLOR_RED = "#f33";

    const FILE_LINES_OF_CODE = "/silv/www/syncany.org/reports.syncany.org/html/cloc.xml";
    const FILE_TESTS = "/silv/www/syncany.org/reports.syncany.org/html/tests/index.html";
    const FILE_COVERAGE = "/silv/www/syncany.org/reports.syncany.org/html/coverage/coverage.xml";

    public function getLines(array $methodArgs, array $requestArgs)
    {
        $lines = $this->getLinesOfCode(self::FILE_LINES_OF_CODE);

        $this->printBadgeSvg("src code", $lines, self::COLOR_GREEN, " k");
        exit;
    }

    public function getTests(array $methodArgs, array $requestArgs)
    {
        $percentage = $this->getTestPercentage(self::FILE_TESTS);
        $color = ($percentage == 100) ? self::COLOR_GREEN : ($percentage > 90) ? self::COLOR_YELLOW : self::COLOR_RED;

        $this->printBadgeSvg("unit tests", $percentage, $color);
        exit;
    }

    public function getCoverage(array $methodArgs, array $requestArgs)
    {
        $percentage = $this->getCoveragePercentage(self::FILE_COVERAGE);
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

        echo <<<EOD
<svg xmlns="http://www.w3.org/2000/svg" width="90" height="18">
<linearGradient id="a" x2="0" y2="100%">
    <stop offset="0" stop-color="#fff" stop-opacity=".7"/>
    <stop offset=".1" stop-color="#aaa" stop-opacity=".1"/>
    <stop offset=".9" stop-opacity=".3"/>
    <stop offset="1" stop-opacity=".5"/>
</linearGradient>
<rect rx="4" width="90" height="18" fill="#555"/>
<rect rx="4" x="57" width="33" height="18" fill="$color"/>
<path fill="$color" d="M57 0h4v18h-4z"/>
<rect rx="4" width="90" height="18" fill="url(#a)"/>
<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">
    <text x="28.5" y="13" fill="#010101" fill-opacity=".3">$labelText</text>
    <text x="28.5" y="12">$labelText</text>
    <text x="72.5" y="13" fill="#010101" fill-opacity=".3">$percentageText</text>
    <text x="72.5" y="12">$percentageText</text>
</g>
</svg>
EOD;

    }

}