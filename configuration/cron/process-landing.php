#!/usr/bin/php
<?php

require("db.inc.php");

$LANDINGDIR = "/silv/ftp/syncanybuildupload";
$TARGETDISTDIR = "/silv/www/syncany.org/syncany.org/html/dist";
$TARGETREPORTSDIR = "/silv/www/syncany.org/reports.syncany.org/html";
$TARGETJAVADOCDIR = "/silv/www/syncany.org/docs.syncany.org/html/javadoc";

$TARGETDEBARCHIVEDIR = "/silv/www/syncany.org/archive.syncany.org/html/apt";
$GNUPGHOME = "/home/debarchive/.gnupg";

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

# docs/javadoc
if (file_exists("$LANDINGDIR/javadoc.tar.gz")) {
	# Delete old landing dir stuff
	`rm -rf $LANDINGDIR/javadoc 2> /dev/null`;

	# Extract new 
	`tar zxf $LANDINGDIR/javadoc.tar.gz -C $LANDINGDIR`;

	# Copy to target dir
	`rm -rf $TARGETJAVADOCDIR 2> /dev/null`;
	`cp -a $LANDINGDIR/javadoc $TARGETJAVADOCDIR`;

	# Cleanup landing dir	
	unlink("$LANDINGDIR/javadoc.tar.gz");
	`rm -rf $LANDINGDIR/javadoc 2> /dev/null`;
}

# reports
if (file_exists("$LANDINGDIR/reports.tar.gz")) {
	# Delete old landing dir stuff
	`rm -rf $LANDINGDIR/reports 2> /dev/null`;

	# Extract new 
	`tar zxf $LANDINGDIR/reports.tar.gz -C $LANDINGDIR`;

	# Copy to target dir
	`rm -rf $TARGETREPORTSDIR/{cloc.xml,tests/,coverage/} 2> /dev/null`;
	`cp -a $LANDINGDIR/reports/* $TARGETREPORTSDIR/`;

	# Cleanup landing dir	
	unlink("$LANDINGDIR/reports.tar.gz");
	`rm -rf $LANDINGDIR/reports 2> /dev/null`;
}

# dist
$propertiesFile = "$LANDINGDIR/application.properties";

if (file_exists($propertiesFile)) {
	$properties = parse_ini_file($propertiesFile, false);

	if (!$properties) {
		echo "Properties file cannot be read from $propertiesFile. EXITING.\n";
		exit(1);
	}
	
	if (!isset($properties['applicationRelease']) || !isset($properties['applicationVersion']) || !isset($properties['applicationRevision'])
		|| !isset($properties['applicationDate'])) {
		
		echo "Properties files not valid. Missing arguments in file $propertiesFile. EXITING.\n";
		exit(1);
	}	
	
	$isRelease = $properties['applicationRelease'];

	if ($isRelease) {
		$targetDir = "releases";
		$targetLinkBasename = "syncany-latest";
		$aptCodename = "release";
	}
	else {
		$targetDir = "snapshots";
		$targetLinkBasename = "syncany-latest-snapshot";
		$aptCodename = "snapshot";
	}
	
	# Put DEB file in APT archive
	$debFiles = glob("$LANDINGDIR/dist/*.deb"); // Should be only one!

	foreach ($debFiles as $debFile) {	
		$command = "sudo -u debarchive reprepro --basedir \"$TARGETDEBARCHIVEDIR/$aptCodename/\" --gnupghome \"$GNUPGHOME\" includedeb $aptCodename \"$debFile\"";
		$output = array();
		$returnvar = -1;
	
		exec($command, $output, $returnvar);		
	
		if ($returnvar != 0) {
			echo "FAILED to process $debFile: " . join("", $output) . "\n";
			exit(1);
		}
	}	
	
	# Go for it; do DEB/TAR.GZ/ZIP/EXE
	@mkdir("$TARGETDISTDIR/$targetDir", 0777, true);
	
	foreach (glob("$LANDINGDIR/dist/*") as $distFile) {
		$distFileExt = (substr($distFile, -strlen("tar.gz")) === "tar.gz") ? "tar.gz" : pathinfo($distFile, PATHINFO_EXTENSION);
		$distFileBasename = basename($distFile);

		$targetLinkMainBasename = ($distFileExt == "exe") ? "syncany-cli" : "syncany";
		$targetLinkBasename = ($isRelease) ? "$targetLinkMainBasename-latest" : "$targetLinkMainBasename-latest-snapshot";

		$newDistFile = "$TARGETDISTDIR/$targetDir/$distFileBasename";
		$linkDistFile = "$TARGETDISTDIR/$targetDir/$targetLinkBasename.$distFileExt";
		
		if (!rename($distFile, $newDistFile)) {
			echo "Cannot move file $distFile to $newDistFile. EXITING.\n";
			exit(1);
		}
		
		@unlink($linkDistFile);
		symlink($distFileBasename, $linkDistFile);
	}

	# Calculate checksums
	chdir("$TARGETDISTDIR/$targetDir/");
	`sha256sum syncany* 2> /dev/null > CHECKSUMS`;
	
	# Remove stuff
	rmdir("$LANDINGDIR/dist");
	unlink("$LANDINGDIR/application.properties");
	
	# Trigger Docker
	# see https://index.docker.io/u/syncany/release/settings/triggers/
	
	if ($isRelease) {
		`curl --data "build=true" -X POST https://registry.hub.docker.com/u/syncany/release/trigger/80eed42e-ddde-11e3-b6a6-623451996e7a/`;
	}
	else {
		`curl --data "build=true" -X POST https://registry.hub.docker.com/u/syncany/snapshot/trigger/cc85cdc6-dddd-11e3-afc9-3ec1629e6ead/`;
	}
}

# Delete ftpok file
unlink("$LANDINGDIR/syncany.ftpok");

?>
