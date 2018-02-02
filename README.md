# Posterprinter

Poster Printer Submission and Accounting Program


## Installation

### Prerequisites -
- PHP Composer

### Installation
1.  Move the files into the a directory the webserver can see.
2.  Run the /admin/sql/posterprinter.sql on the mysql server.
3.  Create a user/password on the mysql server which has select/insert/delete/update permissions on the posterprinter database.
4.  Edit /includes/settings.inc.php to reflect your settings.
5.  Set the permissions on /posterfiles so the user running the apache server has write permissions.
6.  Edit the php.ini file so it has the following settings.
        a. file_uploads = On
        b. upload_max_filesize = 200M
        c. post_max_size = 200M
        d. memory_limit = 200M
        e. max_input_time = 100
        f. max_execution_time = 100
7.  Reload the webserver.
8.  All Done!


