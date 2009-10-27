<?php
//////////////////////////////////////////////////////////
//														//
//	Poster Printer Order Submittion						//
//	settings.inc.php									//
//														//
//	Settings for the scripts.							//
//														//
//	David Slater										//
//	April 2007											//
//														//
//////////////////////////////////////////////////////////

//$adminEmail = "posterorders@igb.uiuc.edu";
$adminEmail = "dslater@igb.uiuc.edu";

$maxPrinterWidth = 44;
$mysqlSettings = array(
		'host' => 'localhost',
		'username' => 'posteruser',
		'password' => 'm5qEGaQacAsp',
		'database' => 'posterprinter'
		);

/*
$mysqlSettings = array(
		'host' => 'www-app.igb.uiuc.edu',
		'username' => 'posterremote',
		'password' => 'goillini',
		'database' => 'posterprinter'
		);
*/
$posterDirectory = "posterfiles";

$authenticationSettings = array(
					'host' => 'authen.igb.uiuc.edu',
					'baseDN' => 'dc=igb,dc=uiuc,dc=edu',
					'peopleOU' => 'ou=people',
					'groupOU' => 'ou=group',
					'ssl' => '0',
					'port' => '389',
					'group' => 'posterprinter'
					);
			

$enable = TRUE;
$version = "1.1.0";


?>
