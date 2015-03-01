<?php

namespace Syncany\Api\Model;

use Syncany\Api\Exception\ConfigException;

class TempFile
{
	private $file;

	public function __construct($file) {
		if (!defined('UPLOAD_PATH')) {
			throw new ConfigException("Upload path not set via CONFIG_PATH.");
		}

		if (substr($file, 0, strlen(UPLOAD_PATH)) != UPLOAD_PATH) {
			throw new ConfigException("Invalid temporary file. Must reside in upload folder.");
		}

		$this->file = $file;
	}

	public function getFile() {
		return $this->file;
	}
}
