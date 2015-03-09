<?php

namespace Syncany\Api\Model;

class ApplicationLink
{
	private $id;
	private $date;
	private $longLink;

	public static function fromArray(array $applicationLinkArray) {
		$applicationLink = new ApplicationLink();

		$applicationLink->id = $applicationLinkArray['id'];
		$applicationLink->date = $applicationLinkArray['date'];
		$applicationLink->longLink = $applicationLinkArray['longlink'];

		return $applicationLink;
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
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @return mixed
	 */
	public function getLongLink()
	{
		return $this->longLink;
	}
}