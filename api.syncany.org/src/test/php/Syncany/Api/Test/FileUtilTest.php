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
