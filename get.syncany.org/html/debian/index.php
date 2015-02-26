<?php

header("Content-Type: text/plain");

?>
#!/bin/bash
set -e
#
# This script will add the Syncany APT repository and install 
# Syncany. It is meant for quick & easy install via:
#
#   'curl -sSL https://get.syncany.org/debian/ | sh'
#
# or
#
#   'wget -qO- https://get.syncany.org/debian/ | sh'
#

echo 
echo This script will add the Syncany APT repository and install 
echo Syncany. This requires root privileges. 
echo 
echo Adding APT repository http://archive.syncany.org/apt/release/ ...
echo 

sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys A3002F0613D342687D70AEEE3F6B7F13651D12BD
sudo sh -c "echo deb http://archive.syncany.org/apt/release/ release main > /etc/apt/sources.list.d/syncany.list"

sleep 3

echo
echo Updating APT cache ...
echo 
sudo apt-get update

echo
echo Installing Syncany ...
echo 
sudo apt-get install -y syncany

if [ -n "$(which sy)" ]; then
	echo 
	echo Successfully installed Syncany.
	echo 
	echo Run 'sy --help' to check out what Syncany can do or navigate
	echo to the user guide for some first steps: https://www.syncany.org/r/howto
	echo 
else
	echo 
	echo Installation FAILED.
	echo
fi
