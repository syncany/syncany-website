#!/bin/bash

IFS=$'\n'
redirects='
# 1
latest.deb
latest.tar.gz
latest.zip

# 2
latest-snapshot.deb
latest-snapshot.tar.gz
latest-snapshot.zip

# 3
latest-x86.exe
latest-x86_64.exe
latest-x86.app.zip
latest-x86_64.app.zip

# 4
latest-snapshot-x86.exe
latest-snapshot-x86_64.exe
latest-snapshot-x86.app.zip
latest-snapshot-x86_64.app.zip

# 5
cli-latest.exe

# 6
plugin-ftp-latest.jar
plugin-ftp-latest.deb

# 7
plugin-ftp-latest-snapshot.jar
plugin-ftp-latest-snapshot.deb

# 8
# No plugins yet.

# 9
plugin-gui-latest-i386.deb
plugin-gui-latest-amd64.deb

# 10
# No plugins yet.

# 11
plugin-gui-latest-snapshot-i386.deb
plugin-gui-latest-snapshot-amd64.deb

# 12 
plugin-gui-latest-windows-x86.jar
plugin-gui-latest-windows-x86_64.jar
plugin-gui-latest-linux-x86.jar
plugin-gui-latest-linux-x86_64.jar
plugin-gui-latest-macosx-x86.jar
plugin-gui-latest-macosx-x86_64.jar

# 13
plugin-gui-latest-snapshot-windows-x86.jar
plugin-gui-latest-snapshot-windows-x86_64.jar
plugin-gui-latest-snapshot-linux-x86.jar
plugin-gui-latest-snapshot-linux-x86_64.jar
plugin-gui-latest-snapshot-macosx-x86.jar
plugin-gui-latest-snapshot-macosx-x86_64.jar

# 14
osx-notifier-latest.app.zip
osx-notifier-latest-snapshot.app.zip
'

test_url() {
	# test_url <redirect-path>
	
	url=https://www.syncany.org/r/$1
	spaces=$((90 - ${#url}))
	
	echo -n "- $url ... "
	printf %${spaces}s
	
	http_response_code=$(curl --location --silent --output /dev/null -w "%{http_code}" "$url")
	
	if [ "$http_response_code" != "200" ]; then
		echo "ERROR: HTTP $http_response_code"
	else
		echo "OK"
	fi		
}

for line in $redirects; do 
	if [ -z "$line" -o "${line:0:1}" == '#' ]; then
		continue
	fi
	
	test_url "$line"	
done

for line in $redirects; do 
	if [ -z "$line" -o "${line:0:1}" == '#' ]; then
		continue
	fi
	
	test_url "syncany-$line"	
done
