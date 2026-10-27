<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src='../vendor/components/jquery/jquery.min.js' type='text/javascript'></script>
<script src='../vendor/components/jqueryui/jquery-ui.min.js' type='text/javascript'></script>
<script src='../vendor/twbs/bootstrap/dist/js/bootstrap.min.js'></script>
<script src='../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js' type='text/javascript'></script>

<link rel="stylesheet" type="text/css" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
<link rel='stylesheet' type="text/css" href='../vendor/components/jqueryui/themes/base/jquery-ui.css'>
<link rel="stylesheet" type="text/css" href="../vendor/fortawesome/font-awesome/css/all.min.css">


<title><?php echo settings::get_title(); ?> Administration</title>

</head>

<body class='d-flex flex-column min-vh-100' style='padding-top: 70px; padding-bottom: 60px;'>
<?php require_once __DIR__ . '/about.inc.php'; ?>
<nav class="navbar fixed-top navbar-dark bg-dark">
        <a class='navbar-brand py-0' href='#'><?php echo settings::get_title(); ?> Administration</a>
		<span class='navbar-text py-0'>
		Version <?php echo settings::get_version(); ?>&nbsp;
		<button type='button' class='btn btn-sm btn-secondary' data-bs-toggle='modal' data-bs-target='#aboutModal'><i class='fas fa-info-circle'></i> About</button>
		<a class='btn btn-danger btn-sm' role="button" href='logout.php'><i class='fas fa-sign-out-alt'></i>Logout</a>
		</span>

</nav>


<div class='container-fluid'>
	<div class="row">
	<div class="col-md-2 col-lg-2 col-xl-2">
		<?php
		// Side menu as a stacked list of buttons; the current page is highlighted.
		$admin_menu = [
			'index.php'                => 'Current Orders',
			'billing.php'              => 'Billing',
			'paperTypes.php'           => 'Paper Types',
			'finishOptions.php'        => 'Finish Options',
			'otherOptions.php'         => 'Other Options',
			'stats_monthly.php'        => 'Monthly Statistics',
			'stats_yearly.php'         => 'Yearly Statistics',
			'stats_fiscal.php'         => 'Fiscal Statistics',
			'stats_OrdersPerMonth.php' => 'Orders Per Month',
			'stats_avg.php'            => 'Monthly Averages',
			'log.php'                  => 'View Log',
		];
		$current_page = basename($_SERVER['PHP_SELF']);
		?>
		<div class='list-group sidebar-nav mb-3'>
		<?php foreach ($admin_menu as $page => $label) { ?>
			<a class='list-group-item list-group-item-action<?php echo ($page === $current_page) ? " active" : ""; ?>' href='<?php echo $page; ?>'><?php echo $label; ?></a>
		<?php } ?>
		</div>
	</div>
	<div class="col-md-10 col-lg-10 col-xl-10">
