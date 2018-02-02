<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'mail.inc.php';
require_once 'orders.inc.php';


if ((isset($_GET['orderId'])) && is_numeric($_GET['orderId'])) {
	$orderId = $_GET['orderId'];

	$order = new order($db,$orderId);
}
else {
	exit;
}
if (isset($_POST['changeStatus'])) {
	
	//updates the order to the new status
	$result = $order->set_status($_POST['status']);
	
	$message = "<div class='alert alert-success' role='alert'>" . $result['MESSAGE'] . "</div>";
	//if status is set to "Complete", then it will email the user saying to come pick up the poster
	if ($_POST['status'] == 'Completed') {
					
		mailUserOrderComplete($db,$orderId,admin_email);
		header("Location: index.php");
	}
	//else if status is set to "Cancel"
	elseif ($_POST['status'] == 'Cancel') { header("Location: index.php"); }
	
}
//get last day of previous month
$startDate = date('Y/m',strtotime('-1 month')) . "/01";
$previous_date = strtotime('-1 second', strtotime($startDate));

if (strtotime($order->get_time_created()) < $previous_date) {
	$status_html = $order->get_status();
	$edit_order_html = "";
}
else {
	//gets the different possible status options
	$status_html = "<form class='form-inline' action='orders.php?orderId=" . $orderId . "' method='post'>";
	$status_html . "<div class='form-group'><div class='col-md-3'>";
	$status_html .= "<select class='form-control' name='status'>";

	foreach (settings::get_status() as $possible_status) {
		
		//used to have the current status of the order be the one selected in the drop down box
		$status_html .= "<option value='" . $possible_status . "'";
		if ($possible_status == $order->get_status()) {
			$status_html .= " selected='selected'";
		}
		$status_html .= ">" . $possible_status . "</option>";
		
	}
	$status_html .= "</select>";
	$status_html .= "</div>";
	$status_html .= "<input class='btn btn-primary btn-sm' type='submit' value='Change' name='changeStatus'>";
	$status_html .= "</div></form>";
	
	$edit_order_html = "<form method='get' action='editOrder.php'>";
	$edit_order_html .= "<input type='hidden' name='orderId' value='" . $order->get_order_id() . "'>";
	$edit_order_html .= "<button class='btn btn-primary' type='submit'>Edit Order</button>";
	$edit_order_html .= "</form>";
}

$file_link = "<a href='download.php?orderId=" . $order->get_order_id() . "'>" . $order->get_filename() . "</a>";

require_once 'includes/header.inc.php';

?>
<div class='col-sm-8 col-md-4'>
<table class='table table-bordered table-condensed'>
<tr><th colspan='2'>Order Information</th></tr>
<tr><td class='text-right'>Order Number</td><td><?php echo $orderId; ?></td></tr>
<tr><td class='text-right'>Email </td><td><?php echo $order->get_email() ?></td></tr>
<tr><td class='text-right'>Additional Emails </td><td><?php echo $order->get_cc_emails() ?></td></tr>
<tr><td class='text-right'>Full Name </td><td><?php echo $order->get_name() ?></td></tr>
<tr><td class='text-right'>File</td><td><?php echo $file_link; ?></td></tr>
<tr><td class='text-right'>File Size</td><td><?php echo $order->get_filesize(); ?>MB</td></tr>
<tr><td class='text-right'>CFOP</td><td><?php echo $order->get_cfop(); ?></td></tr>
<tr><td class='text-right'>Activity Code</td><td><?php echo $order->get_activity_code(); ?></td></tr>
<tr><td class='text-right'>Time Created</td><td><?php echo $order->get_time_created(); ?></td></tr>
<tr><td class='text-right'>Total Cost</td><td>$<?php echo $order->get_total_cost(); ?></td></tr>
<tr><td class='text-right'>Width</td><td><?php echo $order->get_width(); ?>''</td></tr>
<tr><td class='text-right'>Length</td><td><?php echo $order->get_length(); ?>''</td></tr>
<tr><td class='text-right'>Paper Type</td><td><?php echo$order->get_paper_type_name(); ?></td></tr>
<tr><td class='text-right'>Finish Option</td><td><?php echo $order->get_finish_option_name(); ?></td></tr>
<tr><td class='text-right'>Poster Tube</td><td><?php echo $order->get_poster_tube_name(); ?></td></tr>
<tr><td class='text-right'>Rush Order</td><td><?php echo $order->get_rush_order_name(); ?></td></tr>
<tr><td class='text-right'>Comments</td><td><?php echo $order->get_comments(); ?></td></tr>
<tr><td class='text-right' style='vertical-align:middle;'>Status</td><td><?php echo $status_html; ?></td></tr>
<?php if (file_exists($order->get_thumbnail())) {
        echo "<tr><td colspan='2'>";
        echo "<a href='" . $order->get_fullsize() . "'><img class='img-thumbnail' src='" . $order->get_thumbnail() . "'></a>";

        echo "</td></tr>";
}
?>
</table>
</div>
<div class='col-md-12'>
<br>		
<?php echo $edit_order_html; ?>
</div>
<br>
<?php if (isset($message)) { echo $message; } ?>
<?php require_once 'includes/footer.inc.php'; ?>
