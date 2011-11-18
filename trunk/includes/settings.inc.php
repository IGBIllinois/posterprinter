<?php
/////////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	settings.inc.php
//
//	Settings for the scripts.
//
//	David Slater
//	April 2007
//
////////////////////////////////////////////////

define("enable",TRUE);
define("app_version","1.1.5");
//define("admin_email","dslater@igb.uiuc.edu");
define("admin_email","posterorders@igb.uiuc.edu");
define("mysql_host","127.0.0.1");
define("mysql_user","posteruser");
define("mysql_password","m5qEGaQacAsp");
define("mysql_database","posterprinter");
//define("mysql_host","www-app.igb.uiuc.edu");
//define("mysql_user","posterremote");
//define("mysql_password","goillini");
//define("mysql_database","posterprinter");
define("max_printer_width",44);
define("poster_dir","posterfiles");
define("ldap_host",'auth.igb.uiuc.edu');
define("ldap_base_dn","dc=igb,dc=uiuc,dc=edu");
define("ldap_people_ou","ou=people,dc=igb,dc=uiuc,dc=edu");
define("ldap_group_ou","ou=group,dc=igb,dc=uiuc,dc=edu");
define("ldap_group","posterprinter");
define("ldap_ssl",FALSE);
define("ldap_port",389);

?>
