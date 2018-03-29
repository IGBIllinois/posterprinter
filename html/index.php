<?php
//////////////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	index.php
//
//	Page to allow the user to submit poster files
//
//	David Slater
//	April 2007
//
//////////////////////////////////////////////////////

//include files for the script to run
require_once 'includes/main.inc.php';

$paperTypes = functions::getPaperTypes($db);
$paperTypes_html = "";
foreach ($paperTypes as $paperType) {
        $paperTypes_html .= "<tr>";
        $paperTypes_html .= "<td class='right'>$" . $paperType['cost'] . "</td>";
        $paperTypes_html .= "<td class='right'>" .  $paperType['name'] . "</td>";
        $paperTypes_html .= "<td class='left'>" . $paperType['width'] . "''</td>";
        $paperTypes_html .= "</tr>";
}

$finishOptions = functions::getFinishOptions($db);
$finishOptions_html = "";
foreach ($finishOptions as $finishOption) {
	$finishOptions_html .= "<tr>";
	$finishOptions_html .= "<td class='right'>$" . $finishOption['cost'] . "</td>\n";
	$finishOptions_html .= "<td class='center'>" . $finishOption['name'] . "</td>\n";
	$finishOptions_html .= "</tr>";
}

$filetypes = settings::get_valid_filetypes();
$filetypes_html = "";
foreach ($filetypes as $filetype) {
        $filetypes_html .= "<tr>";
        $filetypes_html .= "<td>" . $filetype . "</td>\n";
        $filetypes_html .= "</tr>";
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" type="text/css"
        href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">

<link rel='stylesheet' href='vendor/components/jqueryui/themes/base/jquery-ui.css'>
<script src='vendor/twbs/bootstrap/dist/js/bootstrap.min.js'></script>
<script src='vendor/components/jquery/jquery.min.js' type='text/javascript'></script>
<script src='vendor/components/jqueryui/jquery-ui.min.js' type='text/javascript'></script>
<script src='includes/poster.inc.js' type='text/javascript'></script>

<title><?php echo settings::get_title(); ?></title>

</head>
<body style='padding-top: 70px;'>
<nav class="navbar fixed-top navbar-dark bg-dark">
        <a class='navbar-brand' href='#'><?php echo settings::get_title(); ?></a>
                <span class='navbar-text'>Version <?php echo settings::get_version(); ?>&nbsp;
                <a class='btn btn-danger btn-sm' role='button' href='admin/'>Admin</a></span>
</nav>

<div class='container'>
        <div class='col-md-12 col-lg-12 col-xl-12'>
<div class='jumbotron'>
	<h1 class='display-3'><img src='images/imark_bw.gif' alt='University of Illinois at Urbana-Champaign'>&nbspIGB Poster Order Form</h1>
	<p class='lead'>Welcome to the IGB Poster Order Form.</p>
	<p class='lead'><a class='btn btn-primary btn-lg' href='step1.php' role='button'>Start Order</a></p>
</div>
<div class='row'>
	<div class='col-md-6 col-lg-6 col-xl-6'>
		<h2>Contact Us</h2>
		<p>If you have any questions about our poster printing service, please email us at 
		<a href='mailto:<?php echo settings::get_admin_email(); ?>'><?php echo settings::get_admin_email(); ?></a></p>
	</div>
        <div class='col-md-6 col-lg-6 col-xl-6'>
		<h2>Fequently Asked Questions</h2>
		<p>Check out the <a href='faq.php'>Frequently Asked Questions</a> for answers to common questions</p>
        </div>
</div>

<hr>

<div class='row'>
<div class='col-md-6 col-lg-6 col-xl-6'>
<table class='table table-bordered table-sm'>
<thead>
<tr><th colspan='3'>Available Paper Types</th></tr>
<tr><td colspan='3'><em>Below are the available paper types along with the maximum width for that type of paper. The cost is per an inch.</em></td></tr>
</thead>
<?php echo $paperTypes_html; ?>
</table>
</div>
<div class='col-md-6 col-lg-6 col-xl-6'>
<table class='table table-bordered table-sm  col-md-4 col-lg-4 col-xl-4'>
<thead>
<tr><th colspan='3'>Available Finish Options</th></tr>
<tr><td colspan='3'><em>Below are the available finish options along with the maximum width for that type of paper. The cost is a flat fee.</em></td></tr>
</thead>
<?php echo $finishOptions_html; ?>
</table>
</div>
</div>
<div class='row'>
<div class='col-md-6 col-lg-6 col-xl-6'>
<table class='table table-bordered table-sm'>
<thead>
<tr><th colspan='3'>Allowed File Types</th></tr>
<tr><td colspan='3'><em>Below are the list of allowed file types.  If your file type is not on the list, please email us.</em></td></tr>
</thead>
<?php echo $filetypes_html; ?>
</table>
</div>
<div class='col-md-6 col-lg-6 col-xl-6'>
<table class='table table-bordered table-sm'>
<thead>
	<tr><th colspan='2'>Other Options</th></tr>
	<tr><td colspan='2'><em>Below are the list of other options we provide</em></td></tr>
</thead>
	<tr><td>Rush Order</td><td>$<?php echo rush_order::getRushOrderCost($db); ?></td></tr>
	<tr><td>Poster Tube</td><td>$<?php echo poster_tube::getPosterTubeCost($db); ?></td></tr>
</table>
</div>
</div>
<hr>
<div class='row justify-content-center'>
<a class='btn btn-primary' href='step1.php' role='button'>Start Order</a>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
