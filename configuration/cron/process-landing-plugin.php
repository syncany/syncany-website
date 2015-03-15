#!/usr/bin/php
<?php

require("db.inc.php");

$LANDINGDIR = "/silv/ftp/syncanypluginupload";
$TARGETDISTDIR = "/silv/www/syncany.org/syncany.org/html/dist/plugins";
$TARGETDISTDIRCORE = "/silv/www/syncany.org/syncany.org/html/dist";

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


# Process all plugins

$ftpOkFiles = glob("*/syncany.ftpok");

foreach ($ftpOkFiles as $ftpOkFile) {
	$pluginId = dirname($ftpOkFile);
	$pluginDistDir = "$pluginId/dist";	
	
	# Check folders and files
	
	if (!file_exists($pluginDistDir)) {
		echo "Plugin dist directory $pluginDistDir does not exist. IGNORING PLUGIN.\n";
		continue 2;
	}
	
	# Plugin JAR file(s)	
	$pluginJarFiles = glob("$pluginDistDir/*.jar");
	
	foreach ($pluginJarFiles as $pluginJarFile) {		
		# Read MANIFEST.MF
		
		$manifestFileContents = readZipFileEntry($pluginJarFile, "META-INF/MANIFEST.MF");
		
		if (!$manifestFileContents) {
			echo "Cannot read MANIFEST.MF from JAR file $pluginJarFile. IGNORING PLUGIN.\n";
			continue 2;
		}
	
	
		# Parse manifest
		
		$pluginManifest = parseJarManifest($manifestFileContents);
			
		if (!$pluginManifest) {
			echo "Plugin manifest cannot be parsed from $pluginJarFile. IGNORING PLUGIN.\n";
			continue 2;
		}
	
	
		# Check manifest
		
		if (!isset($pluginManifest['Plugin-Id']) || !isset($pluginManifest['Plugin-Name']) || !isset($pluginManifest['Plugin-Version'])
			|| !isset($pluginManifest['Plugin-Date']) || !isset($pluginManifest['Plugin-App-Min-Version']) 
			|| !isset($pluginManifest['Plugin-Release'])) {
		
			echo "Plugin manifest not valid. Missing arguments in file $pluginJarFile. IGNORING PLUGIN.\n";
			continue 2;
		}	

		if ($pluginManifest['Plugin-Id'] != $pluginId) {
			echo "Plugin ID in manifest does not match plugin directory: $pluginId != {$pluginManifest['Plugin-Id']}. IGNORING PLUGIN.\n";
			continue 2;
		}		
	
	
		# Create target folder
		$pluginIsRelease = $pluginManifest['Plugin-Release'] == "true";
		$pluginTargetFolder = ($pluginIsRelease) ? "$TARGETDISTDIR/releases/$pluginId" : "$TARGETDISTDIR/snapshots/$pluginId";
		$pluginTargetJarFile = "$pluginTargetFolder/" . basename($pluginJarFile);
	
		if (!file_exists($pluginTargetFolder)) {
			if (!mkdir($pluginTargetFolder, 0755, true)) {
				echo "Cannot create target plugin folder $pluginTargetFolder for plugin $pluginId. IGNORING PLUGIN.\n";
				continue 2;	
			}
		}

		if (!is_writable($pluginTargetFolder)) {
			echo "Cannot write to target plugin folder $pluginTargetFolder. IGNORING PLUGIN.\n";
			continue 2;		
		}


		# Checksum
	
		$sha256sum = hash_file('sha256', $pluginJarFile);
	
	
		# Move file

		echo "Processing plugin $pluginId ... ";
		
		if (!rename($pluginJarFile, $pluginTargetJarFile)) {
			echo "Cannot move JAR file from $pluginJarFile to $pluginTargetJarFile. IGNORING PLUGIN.\n";
			continue 2;		
		}
	
		echo "FILE_OK ";
		

		# Symlink for JAR file
		if (isset($pluginManifest['Plugin-Operating-System']) 
			&& $pluginManifest['Plugin-Operating-System'] != "all" 
			&& $pluginManifest['Plugin-Operating-System'] != "") {
			
			$targetLinkNameOperatingSystemSuffix = "-" . $pluginManifest['Plugin-Operating-System'];
		}
		else {
			$targetLinkNameOperatingSystemSuffix = "";
		}
		
		if (isset($pluginManifest['Plugin-Architecture']) 
			&& $pluginManifest['Plugin-Architecture'] != "all" 
			&& $pluginManifest['Plugin-Architecture'] != "") {
			
			$targetLinkNameArchitectureSuffix = "-" . $pluginManifest['Plugin-Architecture'];
		}
		else {
			$targetLinkNameArchitectureSuffix = "";
		}
					
		$targetLinkBasename = ($pluginIsRelease) ? "syncany-plugin-$pluginId-latest$targetLinkNameOperatingSystemSuffix$targetLinkNameArchitectureSuffix.jar" : "syncany-plugin-$pluginId-latest-snapshot$targetLinkNameOperatingSystemSuffix$targetLinkNameArchitectureSuffix.jar";
		$targetLinkFile = "$pluginTargetFolder/$targetLinkBasename";
		
		@unlink($targetLinkFile);
		symlink($pluginTargetJarFile, $targetLinkFile);
	
	
		# Add database entry
	
		$pluginRelease = ($pluginManifest['Plugin-Release'] == "true") ? 1 : 0;
		$pluginConflictsWith = (isset($pluginManifest['Plugin-Conflicts-With'])) ? $pluginManifest['Plugin-Conflicts-With'] : "";
		$pluginOperatingSystem = (isset($pluginManifest['Plugin-Operating-System'])) ? $pluginManifest['Plugin-Operating-System'] : "all";
		$pluginArchitecture = (isset($pluginManifest['Plugin-Architecture'])) ? $pluginManifest['Plugin-Architecture'] : "all";
		$filenameBasename = basename($pluginTargetJarFile);
		$filenameFull = substr($pluginTargetJarFile, strlen($TARGETDISTDIR)+1);
	
		$insertStatement = $databaseConnection->prepare(
			"insert into plugins (pluginId, pluginName, pluginVersion, pluginOperatingSystem, pluginArchitecture, pluginDate, pluginAppMinVersion, "
			. "                   pluginRelease, pluginConflictsWith, sha256sum, filenameBasename, filenameFull) "
			. "           values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
		);	
	
		$insertStatement->bind_param("sssssssdssss",
			$pluginId, $pluginManifest['Plugin-Name'], $pluginManifest['Plugin-Version'], 
			$pluginOperatingSystem, $pluginArchitecture, $pluginManifest['Plugin-Date'], 
			$pluginManifest['Plugin-App-Min-Version'], $pluginRelease, $pluginConflictsWith,
			$sha256sum, $filenameBasename, $filenameFull
		);
	
		if (!$insertStatement->execute()) {
			echo "Cannot insert plugin $pluginId to database. INSERT failed: " . $databaseConnection->errno . ", " . $databaseConnection->error . ". IGNORING PLUGIN.\n";
			continue 2;
		}

		echo "DB_OK\n";	
		
		@unlink($pluginJarFile);	
	}	
	
	# Plugin DEB file(s)	
	$pluginDebFiles = glob("$pluginDistDir/*.deb");

	foreach ($pluginDebFiles as $pluginDebFile) {		
		$pluginArchive = (strpos($pluginDebFile, "SNAPSHOT") !== false) ? "snapshot" : "release";
		
		# 1. Add deb file to APT repo
		
		#sudo -u debarchive reprepro --basedir /silv/www/syncany.org/archive.syncany.org/html/apt/snapshot/ --gnupghome /home/debarchive/.gnupg includedeb snapshot syncany-plugin-gui_0.1.12.alpha+SNAPSHOT.1410172358.git06c6bd0~1_amd64.deb
		$command = "sudo -u debarchive reprepro --basedir \"$TARGETDEBARCHIVEDIR/$pluginArchive/\" --gnupghome \"$GNUPGHOME\" includedeb $pluginArchive \"$pluginDebFile\"";
		$output = array();
		$returnvar = -1;
		
		exec($command, $output, $returnvar);		
		
		if ($returnvar != 0) {
			echo "FAILED to process $pluginDebFile: " . join("", $output) . "\n";
			continue 2;
		}
		
		# 2. Move deb file to dist folder
		
		$pluginTargetFolder = (strpos($pluginDebFile, "SNAPSHOT") !== false) ? "$TARGETDISTDIR/snapshots/$pluginId" : "$TARGETDISTDIR/releases/$pluginId";
		$pluginTargetDebFile = "$pluginTargetFolder/" . basename($pluginDebFile);
	
		if (!file_exists($pluginTargetFolder)) {
			if (!mkdir($pluginTargetFolder, 0755, true)) {
				echo "Cannot create target plugin folder $pluginTargetFolder for plugin $pluginId. IGNORING PLUGIN.\n";
				continue 2;	
			}
		}

		if (!is_writable($pluginTargetFolder)) {
			echo "Cannot write to target plugin folder $pluginTargetFolder. IGNORING PLUGIN.\n";
			continue 2;		
		}
				
		if (!rename($pluginDebFile, $pluginTargetDebFile)) {
			echo "Cannot move DEB file from $pluginDebFile to $pluginTargetDebFile. IGNORING PLUGIN.\n";
			continue 2;		
		}
		
		# 3. Symlink for DEB file
		if (strpos($pluginDebFile, "amd64") !== false) {
			$targetLinkNameArchitectureSuffix = "-amd64";
		}
		else if (strpos($pluginDebFile, "i386") !== false) {
			$targetLinkNameArchitectureSuffix = "-i386";
		}
		else {
			$targetLinkNameArchitectureSuffix = "";
		}
					
		$targetLinkBasename = ($pluginIsRelease) ? "syncany-plugin-$pluginId-latest$targetLinkNameArchitectureSuffix.deb" : "syncany-plugin-$pluginId-latest-snapshot$targetLinkNameArchitectureSuffix.deb";
		$targetLinkFile = "$pluginTargetFolder/$targetLinkBasename";
		
		@unlink($targetLinkFile);
		symlink($pluginTargetDebFile, $targetLinkFile);
		
		# 4. Delete landing dir .deb
		@unlink($pluginDebFile);
	}
	
	# Plugin EXE/APP.ZIP file(s)	
	if ($pluginId == "gui") {
		// .app.zip
		$pluginAppZipFiles = glob("$pluginDistDir/*.app.zip");
		
		foreach ($pluginAppZipFiles as $pluginAppZipFile) {	
			$targetLinkNameArchitectureSuffix = getArchitectureSuffixByFilename($pluginAppZipFile);
			$isRelease = isReleaseByFilename($pluginAppZipFile);

			if ($isRelease) {
				$targetDir = "releases";
				$targetLinkName = "syncany-latest$targetLinkNameArchitectureSuffix.app.zip";
			}
			else {
				$targetDir = "snapshots";
				$targetLinkName = "syncany-latest-snapshot$targetLinkNameArchitectureSuffix.app.zip";
			}
			
			// Move to final location
			$distFileBasename = basename($pluginAppZipFile);
		
			$newDistFile = "$TARGETDISTDIRCORE/$targetDir/$distFileBasename";
			$linkDistFile = "$TARGETDISTDIRCORE/$targetDir/$targetLinkName";
		
			if (!rename($pluginAppZipFile, $newDistFile)) {
				echo "Cannot move file $pluginAppZipFile to $newDistFile. EXITING.\n";
				exit(1);
			}
		
			// Create symlink
			@unlink($linkDistFile);
			symlink($distFileBasename, $linkDistFile);
		}
			
		// .exe	
		$pluginExeFiles = glob("$pluginDistDir/*.exe");

		foreach ($pluginExeFiles as $pluginExeFile) {	
			$targetLinkNameArchitectureSuffix = getArchitectureSuffixByFilename($pluginExeFile);
			$isRelease = isReleaseByFilename($pluginExeFile);
			
			if ($isRelease) {
				$targetDir = "releases";
				$targetLinkName = "syncany-latest$targetLinkNameArchitectureSuffix.exe";
			}
			else {
				$targetDir = "snapshots";
				$targetLinkName = "syncany-latest-snapshot$targetLinkNameArchitectureSuffix.exe";
			}
			
			// Move to final location
			$distFileBasename = basename($pluginExeFile);
		
			$newDistFile = "$TARGETDISTDIRCORE/$targetDir/$distFileBasename";
			$linkDistFile = "$TARGETDISTDIRCORE/$targetDir/$targetLinkName";
		
			if (!rename($pluginExeFile, $newDistFile)) {
				echo "Cannot move file $pluginExeFile to $newDistFile. EXITING.\n";
				exit(1);
			}
		
			// Create symlink
			@unlink($linkDistFile);
			symlink($distFileBasename, $linkDistFile);
		}
	}

		
	# Cleanup
	
	unlink($ftpOkFile);	
	rmdir($pluginDistDir);
}

function getArchitectureSuffixByFilename($filename) {
	if (strpos($filename, "x86_64") !== false) {
		return "-x86_64";
	}
	else if (strpos($filename, "x86") !== false) {
		return "-x86";
	}
	else {
		return "";
	}
}

function isReleaseByFilename($filename) {
	return strpos($filename, "SNAPSHOT") === false;
}

function readZipFileEntry($zipFileName, $searchEntryName) {
	$zip = zip_open($zipFileName);

	if ($zip) {
		while ($zipEntry = zip_read($zip)) {
			$entryName = zip_entry_name($zipEntry);
			
			if ($entryName == $searchEntryName) {
				if (zip_entry_open($zip, $zipEntry, "r")) {
					$searchFileContents = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));

					zip_entry_close($zipEntry);
					zip_close($zip);
					
					return $searchFileContents;
				}			
			}
		}

		zip_close($zip);
	}
	
	return false;
}

function parseJarManifest($manifestFileContents) {
	$manifest = array();	
	$lines = explode("\n", $manifestFileContents);
	
	foreach ($lines as $line) {
		if (preg_match("/^([^:]+):\s*(.*)$/", $line, $m)) {
			$manifest[$m[1]] = trim($m[2]);
		}
	}
	
	return $manifest;
}

?>
