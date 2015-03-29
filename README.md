# Syncany Website

In this repository lives the code for the Syncany website, API, APT archive, documentation, reports and the download page.

API and website setup
=====================
For these instructions, we'll assume that you are running a Debian-based operating system. First, clone this repository to `/silv/www/syncany.org/` (as `root`):

```bash
mkdir -p /silv/www; cd /silv/www
git clone https://github.com/syncany/syncany-website syncany.org
```

Required software 
-----------------
We need a couple of things:

```bash
apt-get install \
   mysql-server \
   apache2 \
   libapache2-mod-php5 \
   php5-curl \
   reprepro

a2enmod php5
a2enmod rewrite

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  # Urgh. But, yes ...
```

API Configuration
-----------------
The API has a couple of config files that need to be properly edited:

```bash
cd /silv/www/syncany.org/api.syncany.org/config
cp database/database.properties.example database/database.properties
cp keys/keys.properties.example keys/keys.properties

vi database/database.properties
  # Edit the file! Don't forget this :)
  
vi keys/keys.properties
  # Edit the file! Don't forget this :)
```

Then, download the dependencies via composer:

```bash
cd /silv/www/syncany.org/api.syncany.org
composer install
```

Apache
------
The subdomains are configured in separate .conf-files in the [sites-available/](configuration/apache2/sites-available) directory. They need to be copied or linked to the `/etc/apache2/sites-enabled` directory:

```bash
for cfg in /silv/www/syncany.org/configuration/apache2/sites-available/*.conf; do 
   ln -s $cfg /etc/apache2/sites-enabled/$(basename $cfg); 
done

apache2ctl -S
service apache2 restart
```

MySQL
-----
The Syncany API needs a simple database to store the plugins and syncany:// shortlinks. 

First, copy and edit the grant file to match the previously defined user passwords:
```bash
cd /silv/www/syncany.org/configuration/mysql
cp grant-users.skeleton.sql grant-users.sql

vi grant-users.sql
  # Edit this file! Don't forget this :)
```

Then, create the database, tables and the user privileges:
```bash
mysql> create database syncanyapi;
mysql> source create-tables.sql;
mysql> source grant-users.skeleton.sql;
```

Reprepro and GnuPG
------------------
Reprepro is used to manage the Syncany Debian/APT archive. The repositories are located at `/silv/www/syncany.org/get.syncany.org/html/apt/release` and `/silv/www/syncany.org/get.syncany.org/html/apt/snapshot`. They are empty by default.

To include new .deb file in the archive, a GnuPG keyring (with the signing key for the Debian/APT archive) is needed. The GnuPG home for the this purpose is located at `/silv/www/syncany.org/api.syncany.org/config/keys/gnupg`. First, we need to generate a keyring and a keypair whcih will be used to sign our releases:

```bash
cd /silv/www/syncany.org/api.syncany.org/config/keys/gnupg
gpg --homedir . # Generates a keyring
gpg --homedir . --gen-key

  # Select: RSA/RSA; 4096 bits, 'does not expire', and no passphrase!
  
gpg --homedir . --list-keys
./pubring.gpg
-------------
pub   4096R/651D12BD 2014-05-24
uid                  Syncany Team <hello@syncany.org>
sub   4096R/C587DF8B 2014-05-24
  
```

In this case, the key "651D12BD" will be used to sign the archive. Before `reprepro` is used, this key needs to be added to the configuration:

```bash
vi /silv/www/syncany.org/get.syncany.org/html/apt/snapshot/conf/distributions
   # Set "SignWith: 651D12BD"

vi /silv/www/syncany.org/get.syncany.org/html/apt/release/conf/distributions
   # Set "SignWith: 651D12BD"
```

The command that's run by the API to include new .deb files is `reprepro includedeb` (see [here](blob/develop/api.syncany.org/src/main/php/Syncany/Api/Util/RepreproUtil.php)). It should look somethin glike this:

```bash
reprepro \
  --basedir /silv/www/syncany.org/get.syncany.org/html/apt/release/ \
  --gnupghome /silv/www/syncany.org/api.syncany.org/config/keys/gnupg/ \
  --component main \
  includedeb release syncany-0.4.3_all.deb

reprepro \
  --basedir /silv/www/syncany.org/get.syncany.org/html/apt/release/ \
  list release
release|main|i386: syncany 0.4.3.alpha
release|main|amd64: syncany 0.4.3.alpha
```

A good resource for the setup is [this tutorial](https://wiki.debian.org/SettingUpSignedAptRepositoryWithReprepro).

Logrotate
---------
The API log file is located in `/silv/www/syncany.org/api.syncany.org/log/api.log`. To rotate the log file, copy or link the logrotate config file:

```bash
ln -s /silv/www/syncany.org/configuration/logrotate/syncanyapi /etc/logrotate.d/syncanyapi
```

Adjust permissions
------------------
A couple of directories need to be writable by the web server user (we assume Apache, so that'd be `www-data`):

```bash
chown -R www-data:www-data /silv/www/syncany.org/get.syncany.org/html/dist
chown -R www-data:www-data /silv/www/syncany.org/get.syncany.org/html/apt
chown -R www-data:www-data /silv/www/syncany.org/api.syncany.org/upload
chown -R www-data:www-data /silv/www/syncany.org/api.syncany.org/log
chown -R www-data:www-data /silv/www/syncany.org/api.syncany.org/config/keys/gnupg
chown -R www-data:www-data /silv/www/syncany.org/docs.syncany.org/html
chown -R www-data:www-data /silv/www/syncany.org/reports.syncany.org/html
chmod 700 /silv/www/syncany.org/api.syncany.org/config/keys/gnupg
```
