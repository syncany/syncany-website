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

namespace Syncany\Api\Model;

class Plugin
{
	private $id;
	private $name;
	private $version;
	private $operatingSystem;
	private $architecture;
	private $date;
	private $appMinVersion;
	private $release;
	private $conflictsWith;
	private $sha256sum;
	private $filenameBasename;
	private $filenameFull;

	public static function fromArray(array $pluginArray) {
		$plugin = new Plugin();

		$plugin->id = $pluginArray['pluginId'];
		$plugin->name = $pluginArray['pluginName'];
		$plugin->version = $pluginArray['pluginVersion'];
		$plugin->operatingSystem = $pluginArray['pluginOperatingSystem'];
		$plugin->architecture = $pluginArray['pluginArchitecture'];
		$plugin->date = $pluginArray['pluginDate'];
		$plugin->appMinVersion = $pluginArray['pluginAppMinVersion'];
		$plugin->release = $pluginArray['pluginRelease'];
		$plugin->conflictsWith = $pluginArray['pluginConflictsWith'];
		$plugin->sha256sum = $pluginArray['sha256sum'];
		$plugin->filenameBasename = $pluginArray['filenameBasename'];
		$plugin->filenameFull = $pluginArray['filenameFull'];

		return $plugin;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return mixed
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @return mixed
	 */
	public function getOperatingSystem()
	{
		return $this->operatingSystem;
	}

	/**
	 * @return mixed
	 */
	public function getArchitecture()
	{
		return $this->architecture;
	}

	/**
	 * @return mixed
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @return mixed
	 */
	public function getAppMinVersion()
	{
		return $this->appMinVersion;
	}

	/**
	 * @return mixed
	 */
	public function getRelease()
	{
		return $this->release;
	}

	/**
	 * @return mixed
	 */
	public function getConflictsWith()
	{
		return $this->conflictsWith;
	}

	/**
	 * @return mixed
	 */
	public function getSha256sum()
	{
		return $this->sha256sum;
	}

	/**
	 * @return mixed
	 */
	public function getFilenameBasename()
	{
		return $this->filenameBasename;
	}

	/**
	 * @return mixed
	 */
	public function getFilenameFull()
	{
		return $this->filenameFull;
	}
}