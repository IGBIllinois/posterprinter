# Posterprinter

Poster Printer Submission and Accounting Program


## Installation

### Prerequisites
- PHP Composer
- PHP Mysql
- PHP LDAP
- PHP Imagick

### Installation
1.  Create alias in apache config that points to the html folder
```
Alias /posterprinter /var/www/posterprinter/html
```
2.  Run the sql/posterprinter.sql on the mysql server.
```
mysql -u root -p posterprinter < sql/posterprinter.sql
```
3.  Create a user/password on the mysql server which has select/insert/delete/update permissions on the posterprinter database.
```
CREATE USER 'posterprinter'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT,INSERT,UPDATE ON posterprinter.* to 'posterprinter'@'localhost';
```
4.  Copy conf/settings.inc.php.dist to conf/settings.inc.php
```
cp conf/settings.inc.php.dist conf/settings.inc.php
```
5.  Edit conf/settings.inc.php with your mysql and ldap settings
6.  Create folder posters and set permssions so the apache user has write permissions
```
mkdir posters
chown apache.apache posters
```
7. Run composer install to install dependencies
```
composer install
```
8.  Edit the php.ini file so it has the following settings.
- file_uploads = On
- upload_max_filesize = 200M
- post_max_size = 200M
- memory_limit = 200M
- max_input_time = 100
- max_execution_time = 100
7.  Reload the webserver.
8.  All Done!


