<?php

chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}


include_once '../includes/settings.inc.php';
#date_default_timezone_set(__TIMEZONE__);

$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
        echo "Error: This script can only be run from the command line.\n";
}
else {
	//connects to database
	$db = new db(mysql_host,mysql_database,mysql_user,mysql_password);
	$order = new order($db,$argv[1]);
	
	//$filename = $order->get_filename();
	$filename = "/var/www/html/eclipse/posterprinter/" . settings::get_poster_dir() . "/" . $order->get_order_id() . "." . $order->get_filetype();
	//poster::create_image($filename);
	$size = poster::get_poster_size($filename);
	print_r($size);

}






?>
