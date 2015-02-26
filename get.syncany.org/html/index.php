<?php

function getOperatingSystem() { 
	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	$os_array = array(
		'/windows/i' => 'windows',
		'/arch linux/i' => 'arch',
		'/ubuntu/i' => 'debian',
		'/debian/i' => 'debian',
		'/mac osx/i' => 'osx',
	);

	foreach ($os_array as $regex => $value) { 
		if (preg_match($regex, $user_agent)) {
			return $value;
		}
	}

	return false;
}

$os = getOperatingSystem();

if ($os == "windows") {
	header("Location: windows/");
	exit;
}
else if ($os == "arch") {
	header("Location: arch/");
	exit;
}
else if ($os == "debian") {
	header("Location: debian/");
	exit;
}
else if ($os == "osx") {
	header("Location: homebrew/");
	exit;
}
else {
	header("Location: other/");
	exit;
}

?>
