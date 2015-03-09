<?php

namespace Syncany\Api\Util;

use Syncany\Api\Exception\ConfigException;

class StringUtil
{
	public static function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}

		return $randomString;
	}

	public static function replace($format, array $variables)
	{
		$resultStr = $format;

		foreach ($variables as $name=>$value) {
			$resultStr = str_replace("{" . $name . "}", $value, $resultStr);
		}

		if (preg_match('/\{(\w)+\}/', $resultStr, $match)) {
			throw new ConfigException("Format string still contains variables");
		}

		return $resultStr;
	}

}
