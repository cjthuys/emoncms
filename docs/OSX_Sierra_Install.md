# Install Emoncms on OSX Sierra

This guide should work on OSX Sierra Using Apache and PHP delivered as part of the OS. It assumes you are running emonhub for data collection and MQTT elsewhere. An alternative would be to use MAMP (www.mamp.info)

The information presented here comes from a variety of sources the main onces being
https://coolestguidesontheplanet.com/get-apache-mysql-php-and-phpmyadmin-working-on-macos-sierra/

http://macoperator.de/php-gettext-in-macos-sierra/

https://github.com/mgdm/Mosquitto-PHP

And also the EMONCMS Linux install doc from this site.

There are many ways of getting emoncms to work on OSX this method uses the preinstalled apache and PHP that is a part of OSX Sierra.
the prequistes for EMONCMS are:-
    Apache, PHP, gettext, MQTT client, and MySQL
    
To install these you will also need some extra tools:- 
    Xcode, Xcode command line utilities, autoconf and homebrew package manager



## Install dependencies

Install Xcode from https://developer.apple.com/xcode/

Install developer command line utilities. 

From a terminal window run `xcode-select --install`

###   Install autoconf

```
  mkdir /tmp/source
  cd /tmp/source
  curl http://ftp.gnu.org/gnu/autoconf/autoconf-latest.tar.gz > autoconf.tgz
  tar -zxf autoconf.tgz 
  cd autoconf-*/
  ./configure 
  make
  make install
  ```
###   Install  homebrew package install utility

  `ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"`

### Install Mosquitto

  `brew install mosquitto`
  
### Install  mosquitto PHP module
  
  Download Mosquitto php wrapper https://github.com/mgdm/Mosquitto-PHP/archive/master.zip

```
  cd /tmp/source
  curl https://github.com/mgdm/Mosquitto-PHP/archive/master.zip > Mosquitto-PHP-master.zip 
  unzip Mosquitto-PHP-master.zip 
  cd Mosquitto-PHP-master 
  phpize
  ./configure --with-mosquitto=/path/to/libmosquitto
  make
  make install
  ```
  
  Make install will fail with an error trying to copy files to protected directories. This can be ignored.
  
  Create a extensions directory to store custom php extensions and copy the mosquitto extension to it.
  
```
  mkdir /usr/local/macoperator/lib/php/extensions/
  cp ./modules/mosquitto.so /usr/local/macoperator/lib/php/extensions/
  ```
  
### Install gettext PHP extension

```
  cd /tmp/source
  curl http://ftp.gnu.org/pub/gnu/gettext/gettext-latest.tar.gz > gettext-latest.tar.gz
  tar -zxf gettext-latest.tar.gz
  cd gettest-*/
  ./configure
  make
  make install
  ```
  Download and unzip the version of PHP installed on your machine (This is only required to allow the build of the gettext extension and can be deleted afterwards)
  http://php.net/downloads.php . Your install verion of PHP can be determined by:-
  
  `php -ver`
  
  Build getext PHP extension
  ```
  cd /tmp/source
  tar -zxf php-5.6.30.tar.gz
  cd php-5.6.30/ext/gettext
  phpize
  ./configure
  make
  make install
  ```
  
  Make install will fail trying to copy files into /usr/libexec/php/extensions  ignore this and copy the extension
  
  `cp modules/gettext.so /usr/local/macoperator/lib/php/extensions`
  

### Configure Apache   - Assumes you are not currently running apache on your iMac

replace <username> with your mac logon username

```
  mkdir /etc/apache2/Sites
  cat > /etc/apache2/Sites/emoncms.conf <<EOF
  <Directory "/Users/<username>/Sites/emoncms">
    AllowOverride All
    Options Indexes MultiViews FollowSymLinks
    Require all granted
  </Directory>
  EOF
  chmod 644 /etc/apache2/Sites/emoncms.conf
  ```
Edit /etc/apache2/httpd.conf and make sure the following modules are uncommented.

```
LoadModule authz_core_module libexec/apache2/mod_authz_core.so
LoadModule authz_host_module libexec/apache2/mod_authz_host.so
LoadModule userdir_module libexec/apache2/mod_userdir.so
LoadModule include_module libexec/apache2/mod_include.so
LoadModule rewrite_module libexec/apache2/mod_rewrite.so
LoadModule php5_module libexec/apache2/libphp5.so
```
Also add the following line to /etc/apache2/httpd.conf

`Include /private/etc/apache2/Sites/*.conf`

Create a root folder for emoncms and a php test file

```
  mkdir /Users/<username>/Sites/emoncms
  echo "<?php phpinfo(); ?>" > /Users/<username>/Sites/emoncms/php-info.php
```
Remember to replace <username> with your username

Start or restart apache
`sudo apachectl restart`

Verify apache is running by got to http:\\localhost\emoncms  you should see the index for the folder emoncms which is currently empty.
Also try http:\\emoncms\php-info.php   This should detail the php installation information

Emoncms uses a front controller to route requests, modrewrite needs to be configured:
    
```
 cat > /etc/apache2/Sites/emoncms.conf <<EOF
 <Directory /Users/<username>/Sites/emoncms>
   Options FollowSymLinks
   AllowOverride All
   DirectoryIndex index.php
   Order allow,deny
   Allow from all
 </Directory>
 EOF
 sudo apachectl restart
```

### Install MySQL

  MySQL doesn’t come pre-loaded with macOS Sierra and needs to be dowloaded from http://dev.mysql.com/downloads/mysql/

  Once downloaded open the .dmg and run the installer.

  When it is finished installing you get a dialog box with a temporary mysql root password – that is a MySQL root password not a macOS admin password, copy and paste it so you can use it.

  Stop MySQL

  `sudo /usr/local/mysql/support-files/mysql.server stop`

  Start it in safe mode:

  `sudo mysqld_safe --skip-grant-tables`

  This will be an ongoing command until the process is finished so open another shell/terminal window, and log in without a password as root:

```
  mysql -u root
  FLUSH PRIVILEGES;
  ALTER USER 'root'@'localhost' IDENTIFIED BY 'MyNewPass';
  Change the lowercase ‘MyNewPass’ to what you want – and keep the single quotes.
  \q
```

Start MySQL

  `sudo /usr/local/mysql/support-files/mysql.server start`
  
Add mysql bin directory to Path in your bash profile 
  ```
  cd 
  echo export PATH="/usr/local/mysql/bin:$PATH" >> .bash_profile
  ```
    
## Install Emoncms

Git is a source code management and revision control system but at this stage we use it to just download and update the emoncms application.

First cd into the /Users/<username>/Sites/emoncms directory:

    `cd /Users/<username>/Sites/`

Set the permissions of the emoncms directory to be owned by your username:

    `sudo chown $USER emoncms`

Cd into emoncms directory

    `cd html`

Download emoncms using git:

**You may want to install one of the other branches of emoncms here, perhaps to try out a new feature set not yet available in the master branch. See the branch list and descriptions on the [start page](https://github.com/emoncms/emoncms)**

    git clone -b stable https://github.com/emoncms/emoncms.git
    
Once installed you can pull in updates with:

    cd /Users/<username>/Sites/emoncms
    git pull
    
## Create a MYSQL database

    mysql -u root -p

Enter the mysql password that you set above.
Then enter the sql to create a database:

    mysql> CREATE DATABASE emoncms DEFAULT CHARACTER SET utf8;
    
Then add a user for emoncms and give it permissions on the new database (think of a nice long password):

    mysql> CREATE USER 'emoncms'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD_HERE';
    mysql> GRANT ALL ON emoncms.* TO 'emoncms'@'localhost';
    mysql> flush privileges;

Exit mysql by:

    mysql> exit
    
### Create data repositories for emoncms feed engine's

    sudo mkdir /var/lib/phpfiwa
    sudo mkdir /var/lib/phpfina
    sudo mkdir /var/lib/phptimeseries

    sudo chown www-data:root /var/lib/phpfiwa
    sudo chown www-data:root /var/lib/phpfina
    sudo chown www-data:root /var/lib/phptimeseries

## Setup Emoncms settings

cd into the emoncms directory where the settings file is located

    cd /var/www/html/emoncms/

Make a copy of default.settings.php and call it settings.php

    cp default.settings.php settings.php

Open settings.php in an editor:

    nano settings.php

Update your database settings to use your new secure password:

    $username = "USERNAME";
    $password = "YOUR_SECURE_PASSWORD_HERE";
    $server   = "localhost";
    $database = "emoncms";
    
You will also want to modify SMTP settings and the password reset flag further down in the settings file.

Save (Ctrl-X), type Y and exit

### Install add-on emoncms modules (optional)
    
    cd /var/www/html/emoncms/Modules
    git clone https://github.com/emoncms/dashboard.git
    git clone https://github.com/emoncms/app.git
 
The 'modules' need to save their configurations in the emoncms database, so in your browser - update your emoncms database:
`Setup > Administration > Update database` (you may need to log out, and log back into emoncms to see the Administration menu).

See individual module readme's for further information on individual module installation.

## enable MQTT Inputs   : Assuming you are running MQTT else where.
https://github.com/emoncms/emoncms/blob/master/docs/RaspberryPi/MQTT.md

## Running Emoncms

[http://localhost/emoncms](http://localhost/emoncms)

The first time you run emoncms it will automatically setup the database and you will be taken straight to the register/login screen.

Create an account by entering your email and password and clicking register to complete.





#### Enable Multi lingual support using gettext

Follow the guide here step 4 onwards: [https://github.com/emoncms/emoncms/blob/master/docs/gettext.md](https://github.com/emoncms/emoncms/blob/master/docs/gettext.md#4-install-gettext)

#### Configure PHP Timezone

PHP 5.4.0 has removed the timezone guessing algorithm and now defaults the timezone to "UTC" on some distros (i.e. Ubuntu 13.10). To resolve this:

Open php.ini

    sudo nano /etc/php5/apache2/php.ini

and search for "date.timezone"

    [Date]
    ; Defines the default timezone used by the date functions.
    ; http://php.net/date.timezone
    ;date.timezone =

edit date.timezone to your appropriate timezone:

    date.timezone = "Australia/Perth"
    
PHP supported timezones are listed here: http://php.net/manual/en/timezones.php

Now save and close and restart your apache.

    sudo  apachectl restart
    
## Install Logger

   See: https://github.com/emoncms/emoncms/tree/master/scripts/logger
   

***

# Debugging

### Check log file

`sudo tail /var/log/apache2/error.log`

### /user/register.json cannot be found

If the login page loads but a user cannot be created and error `invalid` is displayed and console log shows error `/user/register.json` cannot be found this indicates an problem with apache mod_rewrite.

