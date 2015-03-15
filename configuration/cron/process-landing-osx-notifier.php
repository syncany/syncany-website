#!/usr/bin/php
<?php

$LANDINGDIR = "/silv/ftp/syncanyosxnotifierupload";
$TARGETDISTDIR = "/silv/www/syncany.org/syncany.org/html/dist/universe/osxnotifier";

# Tests

if (!file_exists($LANDINGDIR) || !is_writable($LANDINGDIR)) {
	echo "Landing directory does not exist or not writable: $LANDINGDIR";
	exit(1);
}

if (!file_exists($TARGETDISTDIR) || !is_writable($TARGETDISTDIR)) {
	echo "Target directory does not exist or not writable: $TARGETDISTDIR";
	exit(1);
}

if (!chdir($LANDINGDIR)) {
	echo "Invalid landing directory $LANDINGDIR.";
	exit(1);
}

if (!file_exists("$LANDINGDIR/syncany.ftpok")) {
	exit(0);
}

# Change to landing dir!
chdir($LANDINGDIR); 

# dist
$distFiles = glob("$LANDINGDIR/*.zip");

foreach ($distFiles as $distFile) {
	$isRelease = strpos($distFile, "SNAPSHOT") === false;

	if ($isRelease) {
		$targetDir = "releases";
		$targetLinkName = "syncany-osx-notifier-latest.app.zip";
	}
	else {
		$targetDir = "snapshots";
		$targetLinkName = "syncany-osx-notifier-latest-snapshot.app.zip";
	}
	
	# Go for it
	@mkdir("$TARGETDISTDIR/$targetDir", 0777, true);
	
	$distFileBasename = basename($distFile);
	$newDistFile = "$TARGETDISTDIR/$targetDir/$distFileBasename";
	$linkDistFile = "$TARGETDISTDIR/$targetDir/$targetLinkName";
		
	if (!rename($distFile, $newDistFile)) {
		echo "Cannot move file $distFile to $newDistFile. EXITING.\n";
		exit(1);
	}
		
	@unlink($linkDistFile);
	symlink($distFileBasename, $linkDistFile);

	# Calculate checksums
	chdir("$TARGETDISTDIR/$targetDir/");
	`sha256sum syncany* 2> /dev/null > CHECKSUMS`;
}

# Delete ftpok file
unlink("$LANDINGDIR/syncany.ftpok");

?>
