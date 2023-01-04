<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';


?>

<h3>About</h3>
<div class='col-md-8 col-lg-8 col-xl-8'>
<table class='table table-bordered table-sm'>
<tr><td>Code Website</td></td><td><a href='<?php echo settings::get_website_url(); ?>' target='_blank'><?php echo settings::get_website_url(); ?></a></td></tr>
<tr><td>App Version</td><td><?php echo settings::get_version(); ?></td></tr>
<tr><td>Webserver Version</td><td><?php echo \IGBIllinois\Helper\functions::get_webserver_version(); ?></td></tr>
<tr><td>MySQL Version</td><td><?php echo $db->get_version(); ?></td>
<tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
<tr><td>PHP Extensions</td><td><?php 
$extensions_string = "";
foreach (\IGBIllinois\Helper\functions::get_php_extensions() as $row) {
	$extensions_string .= implode(", ",$row) . "<br>";
}
echo $extensions_string;
 ?></td></tr>

</table>
</div>


<?php require_once 'includes/footer.inc.php'; ?>
