# Posterprinter


[![Build Status](https://github.com/IGBIllinois/posterprinter/actions/workflows/main.yml/badge.svg)](https://github.com/IGBIllinois/posterprinter/actions/workflows/main.yml)

Poster Printer Submission and Accounting Program


## Installation

### Prerequisites
- PHP Composer
- PHP Mysql
- PHP LDAP
- PHP Imagick

### Installation
1.  Add apache config to apache configuration to point to html directory
```
Alias /posterprinter /var/www/posterprinter/html
<Directory /var/www/posterprinter/html>
	AllowOverride None
	Require all granted
</Directory>
```
2.  Create mysql database
```
CREATE DATABASE posterprinter CHARACTER SET utf8;
```
3.  Run the sql/posterprinter.sql on the mysql server.
```
mysql -u root -p posterprinter < sql/posterprinter.sql
```
4.  Create a user/password on the mysql server which has select/insert/delete/update permissions on the posterprinter database.
```
CREATE USER 'posterprinter'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT,INSERT,UPDATE ON posterprinter.* to 'posterprinter'@'localhost';
```
5.  Copy conf/settings.inc.php.dist to conf/settings.inc.php
```
cp conf/settings.inc.php.dist conf/settings.inc.php
```
6.  Edit conf/settings.inc.php with your mysql and ldap settings
7.  Create folder posters and set permssions so the apache user has write permissions
```
mkdir posters
chown apache.apache posters
```
8. Run composer install to install dependencies
```
composer install
```
9.  Edit the php.ini file so it has the following settings.
- file_uploads = On
- upload_max_filesize = 200M
- post_max_size = 200M
- memory_limit = 200M
- max_input_time = 100
- max_execution_time = 100
10.  To convert ppt/pptx files for preview, you need to install libreoffice and unoconv from
```
yum install libreoffice libreoffice-pyuno
```
11.  All Done!


