<?php

namespace Syncany\Api\Model;

use Syncany\Api\Exception\ConfigException;
use Syncany\Api\Util\FileUtil;

class TempFile
{
	private $file;

	public function __construct($file) {
		if (!defined('UPLOAD_PATH')) {
			throw new ConfigException("Upload path not set via CONFIG_PATH.");
		}

        $this->file = FileUtil::canonicalize($file);
        FileUtil::checkLockInDirMustNotExist(UPLOAD_PATH, $file);
	}

	public function getFile() {
		return $this->file;
	}
}
