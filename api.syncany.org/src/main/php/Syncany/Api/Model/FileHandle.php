<?php

namespace Syncany\Api\Model;

class FileHandle
{
	private $file;

	public function __construct($file) {
		$this->file = $file;
	}

	public function getHandle() {
		return $this->file;
	}
}
