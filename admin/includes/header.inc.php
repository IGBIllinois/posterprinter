<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="../includes/bootstrap-3.3.5-dist/css/bootstrap.min.css">

<title><?php echo settings::get_title(); ?> Administration</title>

</HEAD>

<body style='padding-top: 60px; padding-bottom: 60px;'>
<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class='container-fluid'>
         <div class='navbar-header'>
                        <a class='navbar-brand' href='#'><?php echo settings::get_title(); ?> Administration</a>
	</div>
	<p class='navbar-text pull-right'>Version <?php echo app_version; ?></p>
	</div>
		
</nav>

<div class='container-fluid'>
	<div class="col-md-2 sidebar">	
		<nav>
		<ul class='nav nav-stacked'>
			<li><a href='index.php'>Current Orders</a></li>
			<li><a href='previousOrders.php'>Previous Orders</a></li>
			<li><a href='paperTypes.php'>Paper Types</a></li>
			<li><a href='finishOptions.php'>Finish Options</a></li>
			<li class='active'><a href='otherOptions.php'>Other Options</a></li>
			<li><a href='stats_monthly.php'>Monthly Statistics</a></li>
			<li><a href='stats_yearly.php'>Yearly Statistics</a></li>
			<li><a href='stats_fiscal.php'>Fiscal Statistics</a></li>
			<li><a href='stats_OrdersPerMonth.php'>Orders Per Month</a></li>
			<li><a href='stats_avg.php'>Monthly Averages</a></li></a>
			<li><a href='logout.php'>Log Out</a></li>
			
		</ul>
		</nav>
	</div>
	<div class="col-md-10">
