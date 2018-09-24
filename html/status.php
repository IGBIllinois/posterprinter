<?php
//////////////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	status.php
//
//	Page to allow to view the status of a poster
//
//	David Slater
//	April 2007
//
//////////////////////////////////////////////////////

//include files for the script to run
require_once 'includes/main.inc.php';


if (isset($_GET['order_id']) && is_numeric($_GET['order_id']) && isset($_GET['key'])) {
	$order = new order($db,$_GET['order_id']);
	if (!$order->get_key() || ($order->get_key() != $_GET['key'])) {
		//exit;
	}
}

require_once 'includes/header.inc.php';
?>
<h2>Order Status</h2>

<div class='row'>
<table class='table table-bordered table-sm'>
<thead>
</thead>

<tr><td>Order Number</td><td><?php echo $order->get_order_id(); ?></td></tr>
<tr><td>Submission Time</td><td><?php echo $order->get_time_created(); ?></td></tr>
<tr><td>Full Name</td><td><?php echo $order->get_name(); ?></td></tr>
<tr><td>Email</td><td><?php echo $order->get_email(); ?></td></tr>
<tr><td>Additional Emails</td><td><?php echo $order->get_cc_emails(); ?></td></tr>
<tr><td>File</td><td><?php echo $order->get_filename(); ?></td></tr>
<tr><td>Length</td><td><?php echo $order->get_length(); ?></td></tr>
<tr><td>Width</td><td><?php echo $order->get_width(); ?></td></tr>
<tr><td>Paper Type</td><td><?php echo $order->get_paper_type_name(); ?></td></tr>
<tr><td>Finish Option</td><td><?php echo $order->get_finish_option_name(); ?></td></tr>
<tr><td>Poster Tube</td><td><?php echo $order->get_poster_tube_name(); ?></td></tr>
<tr><td>Rush Order</td><td><?php echo $order->get_rush_order_name(); ?></td></tr>
<tr><td>CFOP</td><td><?php echo $order->get_cfop(); ?></td></tr>
<tr><td>Activity Code</td><td><?php echo $order->get_activity_code(); ?></td></tr>
<tr><td>Comments</td><td><?php echo $order->get_comments(); ?></td></tr>
<tr><td>Total Cost</td><td>$<?php echo $order->get_total_cost(); ?></td></tr>
<tr><td>Status</td><td><?php echo $order->get_status(); ?></td></tr>
</table>
</div>

<?php

if ($order->get_rotated()) {
        echo "<div class='alert alert-success'>Note: Your width and length have been flipped to save paper and money.  This won't affect the size or orientation of your poster.</div>";
}
?>

<div class='row'>
	<div class='mx-auto btn-toolbar'>
	<a class='btn btn-primary' href='index.php'>Main Page</a>
	</div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
