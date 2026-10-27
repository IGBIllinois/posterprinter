<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';


if ((isset($_GET['order_id'])) && is_numeric($_GET['order_id'])) {
	$order_id = $_GET['order_id'];

	$order = new order($db,$order_id);
}
else {
	exit;
}
// Where to go after an order is closed (Completed/Cancel): back to the page
// the admin came from. Only known pages are allowed (no open redirect).
$from = $_GET['from'] ?? '';
if ($from === 'billing') {
	$return_url = 'billing.php?month=' . (int) ($_GET['month'] ?? date('m')) . '&year=' . (int) ($_GET['year'] ?? date('Y'));
	$self_query = 'order_id=' . (int) $order_id . '&from=billing&month=' . (int) ($_GET['month'] ?? date('m')) . '&year=' . (int) ($_GET['year'] ?? date('Y'));
} else {
	$return_url = 'index.php';
	$self_query = 'order_id=' . (int) $order_id;
}

if (isset($_POST['changeStatus'])) {
	
	//updates the order to the new status
	$old_status = $order->get_status();
	$result = $order->set_status($_POST['status']);
	$log->send_log("Order " . (int) $order_id . " status changed from '" . $old_status . "' to '" . $_POST['status']
		. "' by " . $session->get_var('username'));
	
	$message = "<div class='alert alert-success' role='alert'>" . $result['MESSAGE'] . "</div>";
	//if status is set to "Complete", then it will email the user saying to come pick up the poster
	if ($_POST['status'] == order::STATUS['COMPLETED']) {
					
		// A mail failure must not hide the status change (already saved).
		try {
			$order->mailUserOrderComplete();
		} catch (\Throwable $mailError) {
			error_log('poster admin/order.php: COMPLETED email failed for order ' . $order_id . ': ' . $mailError->getMessage());
			$log->send_log("Order " . (int) $order_id . ": 'poster completed' email failed: " . $mailError->getMessage(), \IGBIllinois\log::ERROR);
		}
		header("Location: " . $return_url);
		exit;
	}
	//else if status is set to "Cancel"
	elseif ($_POST['status'] == order::STATUS['CANCEL']) { header("Location: " . $return_url); exit; }
	
}
//get last day of previous month
$startDate = date('Y/m',strtotime('-1 month')) . "/01";
$previous_date = strtotime('-1 second', strtotime($startDate));

if (strtotime($order->get_time_created()) < $previous_date) {
	$status_html = htmlspecialchars($order->get_status(), ENT_QUOTES);
	$status_dialog_html = "";
	$edit_order_html = "";
}
else {
	// Status shows as plain text with a Change button on the right.
	// The button opens a small dialog listing the statuses; picking one and
	// saving posts the same 'status' + 'changeStatus' fields as before.
	$current_status = $order->get_status();
	$status_html  = "<div class='d-flex align-items-center justify-content-between'>";
	$status_html .= "<span>" . htmlspecialchars($current_status, ENT_QUOTES) . "</span>";
	$status_html .= "<button type='button' class='btn btn-primary btn-sm' onclick=\"document.getElementById('statusDialog').showModal()\">Change</button>";
	$status_html .= "</div>";

	$status_dialog_html  = "<dialog id='statusDialog' class='border rounded shadow p-0' style='width: 540px; max-width: 95vw; font-size: 1.25rem;'>";
	$status_dialog_html .= "<form class='p-4' action='" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?" . htmlspecialchars($self_query, ENT_QUOTES) . "' method='post'>";
	$status_dialog_html .= "<h5 class='mb-4'>Change status of order #" . (int) $order_id . "</h5>";
	foreach (order::get_all_statuses() as $possible_status) {
		$safe   = htmlspecialchars($possible_status, ENT_QUOTES);
		$id     = 'status_' . preg_replace('/[^a-z0-9]/i', '', $possible_status);
		$status_dialog_html .= "<div class='form-check mb-2'>";
		$status_dialog_html .= "<input class='form-check-input' type='radio' name='status' id='" . $id . "' value='" . $safe . "'"
		                     . ($possible_status == $current_status ? " checked" : "") . ">";
		$status_dialog_html .= "<label class='form-check-label' for='" . $id . "'>" . $safe . "</label>";
		$status_dialog_html .= "</div>";
	}
	// Cancel and Save: equal width, with open space between them.
	$status_dialog_html .= "<div class='d-flex justify-content-around mt-4'>";
	$status_dialog_html .= "<button type='button' class='btn btn-secondary' style='width: 38%;' onclick=\"this.closest('dialog').close()\">Cancel</button>";
	$status_dialog_html .= "<input type='submit' class='btn btn-primary' style='width: 38%;' name='changeStatus' value='Save'>";
	$status_dialog_html .= "</div></form></dialog>";

	$edit_order_html = "<form method='get' action='editOrder.php'>";
	$edit_order_html .= "<input type='hidden' name='order_id' value='" . $order->get_order_id() . "'>";
	$edit_order_html .= "<button class='btn btn-primary' type='submit'>Edit Order</button>";
	$edit_order_html .= "</form>";
}

$file_link = "<a href='download.php?orderId=" . $order->get_order_id() . "'>" . $order->get_filename() . "</a>";

require_once 'includes/header.inc.php';

?>
<div class='row'>
<div class='col-lg-7'>
<table class='table table-bordered table-striped table-sm detail-table'>
<thead>
<tr><th colspan='2'>Order Information</th></tr>
</thead>
<tr><td class='text-end'>Order Number</td><td><?php echo $order->get_order_id(); ?></td></tr>
<tr><td class='text-end'>Email </td><td><?php echo $order->get_email() ?></td></tr>
<tr><td class='text-end'>Additional Emails </td><td><?php echo $order->get_cc_emails() ?></td></tr>
<tr><td class='text-end'>Full Name </td><td><?php echo $order->get_name() ?></td></tr>
<tr><td class='text-end'>File</td><td><?php echo $file_link; ?></td></tr>
<tr><td class='text-end'>File Size</td><td><?php echo $order->get_filesize(); ?>MB</td></tr>
<tr><td class='text-end'>CFOP</td><td><?php echo $order->get_cfop(); ?></td></tr>
<tr><td class='text-end'>Activity Code</td><td><?php echo $order->get_activity_code(); ?></td></tr>
<tr><td class='text-end'>Time Created</td><td><?php echo $order->get_time_created(); ?></td></tr>
<tr><td class='text-end'>Total Cost</td><td>$<?php echo $order->get_total_cost(); ?></td></tr>
<tr><td class='text-end'>Width</td><td><?php echo $order->get_width(); ?>''</td></tr>
<tr><td class='text-end'>Length</td><td><?php echo $order->get_length(); ?>''</td></tr>
<tr><td class='text-end'>Paper Type</td><td><?php echo$order->get_paper_type_name(); ?></td></tr>
<tr><td class='text-end'>Finish Option</td><td><?php echo $order->get_finish_option_name(); ?></td></tr>
<tr><td class='text-end'>Poster Tube</td><td><?php echo $order->get_poster_tube_name(); ?></td></tr>
<tr><td class='text-end'>Rush Order</td><td><?php echo $order->get_rush_order_name(); ?></td></tr>
<tr><td class='text-end'>Comments</td><td><?php echo $order->get_wordwrap_comments(); ?></td></tr>
<tr><td class='text-end' style='vertical-align:middle;'>Status</td><td><?php echo $status_html; ?></td></tr>

</table>
<?php echo $edit_order_html; ?>
</div>
<div class='col-lg-5'>
<?php if (file_exists($order->get_thumbnail())) {
	// Thumbnail beside the details on wide screens; stacks below on narrow ones.
	// Clicking opens the full-size image in an in-page viewer (closes on a
	// click outside the image or Esc). The link still works for middle-click
	// / open in new tab.
	$fullsize_url = "image.php?image_path=" . urlencode($order->get_fullsize());
	echo "<div class='position-sticky' style='top: 80px;'>";
	echo "<a href='" . $fullsize_url . "' onclick=\"event.preventDefault(); document.getElementById('imageDialog').showModal();\">";
	echo "<img class='img-thumbnail d-block w-100' src='image.php?image_path=" . urlencode($order->get_thumbnail()) . "' alt='Poster preview'></a>";
	echo "<div class='form-text text-center'>Click to view full size</div>";
	echo "</div>";
	echo "<dialog id='imageDialog' class='p-0 border-0 bg-transparent' style='max-width: 95vw; max-height: 95vh;'>";
	echo "<img src='" . $fullsize_url . "' alt='Full size poster' style='display:block; max-width: 95vw; max-height: 95vh; object-fit: contain;'>";
	echo "</dialog>";
} else {
	echo "<div class='text-muted'>No preview available</div>";
}
?>
</div>
</div>
<?php echo $status_dialog_html; ?>
<style>
	/* Details tables: fixed layout so long unbroken text (e.g. a pasted URL
	   or "qqqq...") wraps inside its cell instead of widening the table
	   underneath the preview column. */
	.detail-table { table-layout: fixed; width: 100%; }
	.detail-table td { overflow-wrap: anywhere; word-break: break-word; }
	.detail-table td:first-child { width: 9.5rem; }
	dialog::backdrop { background: rgba(0, 0, 0, 0.6); }
</style>
<script>
// Close any dialog when clicking outside its content (on the dimmed backdrop).
// A click on the backdrop reports the <dialog> element itself as the target.
document.querySelectorAll('dialog').forEach(function (d) {
	d.addEventListener('click', function (e) {
		if (e.target === d) { d.close(); }
	});
});
</script>
<br>
<?php if (isset($message)) { echo $message; } ?>
<?php require_once '../includes/footer.inc.php'; ?>
