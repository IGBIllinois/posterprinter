<?php

require_once 'includes/main.inc.php';

if (isset($_POST['step4'])) {

        $posterFileName = $_POST['posterFileName'];
        $posterFileTmpName = $_POST['posterFileTmpName'];

        $thumb_posterFileTmpName = "thumb_" . pathinfo($_POST['posterFileTmpName'],PATHINFO_FILENAME) . ".jpg";
        $fullsize_posterFileTmpName = "fullsize_" . pathinfo($_POST['posterFileTmpName'],PATHINFO_FILENAME) . ".jpg";

        $orderId = functions::create_order($db,$_POST);


        //gets the file type (ie .jpg, .bmp) of the uploaded poster file.
        $fileType = poster::get_filetype($posterFileName);
        $filename = $orderId . "." . $fileType;
        $thumb_filename = "thumb_" . $orderId . ".jpg";
        $fullsize_filename = "fullsize_" . $orderId . ".jpg";

        //renames the temporary file to its permanent file name which is the orderId number plus the filetype extensions.
        if (file_exists(poster_dir . "/" . $posterFileTmpName)) {
                rename(poster_dir . "/" . $posterFileTmpName,poster_dir . "/" . $filename);
        }
        if (file_exists(poster_dir . "/" . $thumb_posterFileTmpName)) {
                rename(poster_dir . "/" . $thumb_posterFileTmpName,poster_dir . "/" . $thumb_filename);
        }
        if (file_exists(poster_dir . "/" . $fullsize_posterFileTmpName)) {
                rename(poster_dir . "/" . $fullsize_posterFileTmpName,poster_dir . "/" . $fullsize_filename);
        }


        $order = new order($db,$orderId);
        $order->mailNewOrder();
}

require_once 'includes/header.inc.php';

?>
 
<table class='table table-bordered table-sm'>
<tr><th colspan='2'>Order Information</td></tr>
<tr><td colspan='2'><em>Thank you for your order.  Your order will be completed within <strong><?php echo settings::get_order_timeframe(); ?> business hours</strong>. 
If it is a rush order, it will be completed within <strong><?php echo settings::get_rush_order_timeframe(); ?> business hours</strong>
An email has been sent to you at <?php echo $order->get_email(); ?> with this information. We will email you when the poster is completed printing.</em></td></tr>

<?php

if ($widthSwitched == 1) {
	echo "<tr><td colspan='2'><em>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</em></td></tr>";
}
?>

<tr><td>Full Name</td><td><?php echo $order->get_name(); ?></td></tr>
<tr><td>Email</td><td><?php echo $order->get_email(); ?></td></tr>
<tr><td>Additional Emails</td><td><?php echo $order->get_cc_emails(); ?></td></tr>
<tr><td>Order Number</td><td><?php echo $orderId; ?></td></tr>
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




<?php if (isset($message)) { echo $message; } ?>

<?php require_once 'includes/footer.inc.php'; ?>

