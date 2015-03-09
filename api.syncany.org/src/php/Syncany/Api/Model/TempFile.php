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

        FileUtil::checkLockInDirMustNotExist(UPLOAD_PATH, $file);
		$this->file = $file;
	}

	public function getFile() {
		return $this->file;
	}
}
