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
<body style='padding-top: 65px;'>
<nav class="navbar fixed-top navbar-dark bg-dark">
	<a class='navbar-brand' href='#'><?php echo settings::get_title(); ?></a>
	<div class='collapse navbar-collapse' id='navbarText'>
		<ul class="navbar-nav mr-auto">
		</ul>
		<span class='navbar-text'>
	        <p class='navbar-text pull-right'>Version <?php echo settings::get_version(); ?></p>
		</span>

	</div>
</nav>

<div class='container'>
        <div class='col-md-12 col-lg-12 col-xl-12'>

