<?php

require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

if (isset($_POST['step4'])) {

        $posterFileName = $_POST['posterFileName'];
        $posterFileTmpName = $_POST['posterFileTmpName'];

        $thumb_posterFileTmpName = "thumb_" . pathinfo($_POST['posterFileTmpName'],PATHINFO_FILENAME) . ".jpg";
        $fullsize_posterFileTmpName = "fullsize_" . pathinfo($_POST['posterFileTmpName'],PATHINFO_FILENAME) . ".jpg";

        $orderId = functions::create_order($db,$_POST);


	poster::move_poster($orderId,$posterFileName,$posterFileTmpName,$thumb_posterFileTmpName,$fullsize_posterFileTmpName);

        $order = new order($db,$orderId);
        $order->mailNewOrder();
	$session->destroy_session();
}
else {
        $session->destroy_session();
        header('Location: index.php');

}

require_once 'includes/header.inc.php';

?>

<div class='row'> 
<table class='table table-bordered table-sm'>
<thead>
<tr><th colspan='2'>Order Information</td></tr>
<tr><td colspan='2'><em>Thank you for your order.  Your order will be completed within <strong><?php echo settings::get_order_timeframe(); ?> business hours</strong>. 
If it is a rush order, it will be completed within <strong><?php echo settings::get_rush_order_timeframe(); ?> business hours</strong>
An email has been sent to you at <?php echo $order->get_email(); ?> with this information. We will email you when the poster is completed printing.</em></td></tr>
</thead>

<?php

if ($order->get_rotated()) {
	echo "<tr><td colspan='2'><em>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</em></td></tr>";
}
?>

<tr><td>Full Name</td><td><?php echo $order->get_name(); ?></td></tr>
<tr><td>Email</td><td><?php echo $order->get_email(); ?></td></tr>
<tr><td>Additional Emails</td><td><?php echo $order->get_cc_emails(); ?></td></tr>
<tr><td>Order Number</td><td><?php echo $order->get_order_id(); ?></td></tr>
<tr><td>File</td><td><?php echo $order->get_filename(); ?></td></tr>
<tr><td>Length</td><td><?php echo $order->get_length(); ?></td></tr>
<tr><td>Width</td><td><?php echo $order->get_width(); ?></td></tr>
<tr><td>Paper Type</td><td><?php echo $order->get_paper_type_name(); ?></td></tr>
<tr><td>Finish Option</td><td><?php echo $order->get_finish_option_name(); ?></td></tr>
<tr><td>Poster Tube</td><td><?php echo $order->get_poster_tube_name(); ?></td></tr>
<tr><td>Rush Order</td><td><?php echo $order->get_rush_order_name(); ?></td></tr>
<tr><td>Comments</td><td><?php echo $order->get_comments(); ?></td></tr>
<tr><td>CFOP</td><td><?php echo $order->get_cfop(); ?></td></tr>
<tr><td>Activity Code</td><td><?php echo $order->get_activity_code(); ?></td></tr>
<tr><td>Total Cost</td><td>$<?php echo $order->get_total_cost(); ?></td></tr>
</table>
</div>

<?php

if ($order->get_rotated()) {
        echo "<div class='row'> ";
	echo functions::alert("Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.",1);
	echo "</div>";
}
?>


<div class='row'>
        <div class='mx-auto btn-toolbar'>
                <a class='btn btn-primary' role='button' href='index.php'>Start New Order</a>
        </div>

</div>
<?php require_once 'includes/footer.inc.php'; ?>

