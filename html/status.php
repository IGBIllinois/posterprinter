<?php
//////////////////////////////////////////////////////
//
//  Poster Printer - status.php
//  Order status page. Also serves as the post-submit
//  confirmation page (review.php redirects here with
//  &new=1 after creating the order).
//
//  Fixes vs. original: missing-params guard actually
//  exits (was fatal on PHP 8), user-controlled fields
//  escaped on output.
//
//////////////////////////////////////////////////////

require_once 'includes/main.inc.php';

if (isset($_GET['order_id']) && is_numeric($_GET['order_id']) && isset($_GET['key'])) {
    $order = new order($db, $_GET['order_id']);
    if (!$order->get_key() || ($order->get_key() != $_GET['key'])) {
        exit;
    }
} else {
    // Original fell through with $order undefined -> fatal. Now it doesn't.
    header('Location: index.php');
    exit;
}

$isNew  = isset($_GET['new']);
// Rush is identified by the rushOrder row name 'Yes', the same way the
// rest of the app (rush_order class) identifies it.
$isRush = ($order->get_rush_order_name() === 'Yes');
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);

require_once 'includes/header.inc.php';
?>
<?php if ($isNew) { ?>
<div class='alert alert-success'>
	<strong>Thank you for your order.</strong>
	<?php if ($isRush) { ?>
	This is a <strong>rush order</strong> and will be completed within <strong><?php echo $e(settings::get_rush_order_timeframe()); ?> business hours</strong>.
	<?php } else { ?>
	Your order will be completed within <strong><?php echo $e(settings::get_order_timeframe()); ?> business hours</strong>.
	<?php } ?>
	An email has been sent to you at <?php echo $e($order->get_email()); ?> with this information.
	We will email you when the poster is completed printing.
	You can bookmark this page to check your order status.
</div>
<?php } ?>
<h2>Order Status</h2>
<div class='row'>
<table class='table table-bordered table-sm'>
<tr><td>Order Number</td><td><?php echo $e($order->get_order_id()); ?></td></tr>
<tr><td>Submission Time</td><td><?php echo $e($order->get_time_created()); ?></td></tr>
<tr><td>Full Name</td><td><?php echo $e($order->get_name()); ?></td></tr>
<tr><td>Email</td><td><?php echo $e($order->get_email()); ?></td></tr>
<tr><td>Additional Emails</td><td><?php echo $e($order->get_cc_emails()); ?></td></tr>
<tr><td>File</td><td><?php echo $e($order->get_filename()); ?></td></tr>
<tr><td>Length</td><td><?php echo $e($order->get_length()); ?></td></tr>
<tr><td>Width</td><td><?php echo $e($order->get_width()); ?></td></tr>
<tr><td>Paper Type</td><td><?php echo $e($order->get_paper_type_name()); ?></td></tr>
<tr><td>Finish Option</td><td><?php echo $e($order->get_finish_option_name()); ?></td></tr>
<tr><td>Poster Tube</td><td><?php echo $e($order->get_poster_tube_name()); ?></td></tr>
<tr><td>Rush Order</td><td><?php echo $e($order->get_rush_order_name()); ?></td></tr>
<tr><td>CFOP</td><td><?php echo $e($order->get_cfop()); ?></td></tr>
<tr><td>Activity Code</td><td><?php echo $e($order->get_activity_code()); ?></td></tr>
<tr><td>Comments</td><td><?php echo $order->get_wordwrap_comments(); /* escaped inside, keeps line breaks */ ?></td></tr>
<tr><td>Total Cost</td><td>$<?php echo $e($order->get_total_cost()); ?></td></tr>
<tr><td>Status</td><td><?php echo $e($order->get_status()); ?></td></tr>
<?php if (file_exists($order->get_thumbnail())) {
    echo "<tr><td colspan='2'>";
    echo "<a href='image.php?image_path=" . urlencode($order->get_fullsize()) . "'><img class='img-thumbnail mx-auto d-block' src='image.php?image_path=" . urlencode($order->get_thumbnail()) . "'></a>";
    echo "</td></tr>";
}
?>
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
