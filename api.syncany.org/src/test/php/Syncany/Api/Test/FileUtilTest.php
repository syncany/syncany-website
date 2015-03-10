<?php

namespace Syncany\Api\Test;

use Syncany\Api\Util\FileUtil;

class FileUtilTest extends \PHPUnit_Framework_TestCase {
    public function testFileCanonicalize()
    {
        // Normal cases
        $this->assertEquals("/", FileUtil::canonicalize("/"));
        $this->assertEquals("/some/abs", FileUtil::canonicalize("/some/abs/path/.."));
        $this->assertEquals("/some/abs", FileUtil::canonicalize("/some/abs/path/../"));
        $this->assertEquals("/some/abs/path", FileUtil::canonicalize("/some/abs/path/../path"));
        $this->assertEquals("/some/abs/path", FileUtil::canonicalize("/some/abs/path/../path/"));

        // Unusual cases
        $this->assertEquals("/some/path", FileUtil::canonicalize("///some/path"));
        $this->assertEquals("/", FileUtil::canonicalize("///some/path/../../../"));
        $this->assertEquals("/some", FileUtil::canonicalize("/some/path/.."));
        $this->assertEquals("/some/path", FileUtil::canonicalize("///some/path"));
        $this->assertEquals("/some/path", FileUtil::canonicalize("///some/path"));
        $this->assertEquals("/some/path", FileUtil::canonicalize("///some/path///"));
        $this->assertEquals("/some/path", FileUtil::canonicalize("///some/path///"));
        $this->assertEquals("path", FileUtil::canonicalize("some/../path///"));
        $this->assertEquals("path", FileUtil::canonicalize("some/../../path///"));
    }
}
