
<?php
/////////////////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	mail.inc.php
//
//	Functions to mail the user the order information when order is
//	submitted and completed and email admins when a new order is
//	submitted
//
//	David Slater
//	April 2007
//
////////////////////////////////////////////////////////


//mailNewOrder()
//$db - database object
//$orderId - integer - order number
//emails the user and admins that a new order has been made
function mailNewOrder($db,$orderId) {
	$order = new order($db,$orderId);
	mailAdminsNewOrder($db,$order);
	mailUserNewOrder($db,$order);

}

//mailAdminsNewOrer()
//$db - database object
//$order - order object
//emails admin of the new order
function mailAdminsNewOrder($db,$order) {
	$boundary = uniqid('np');
	$requestUri = substr($_SERVER["REQUEST_URI"],0,strrpos($_SERVER["REQUEST_URI"], "/")+1);
	$urlAddress = "http://" . $_SERVER["SERVER_NAME"] . $requestUri; 
	$subject = "New Poster To Print - Order #" . $order->get_order_id();
	$to = settings::get_admin_email();

	//html email
	$message = "\r\n\r\n--" . $boundary . "\r\n";
	$message .= "Content-type:text/html;charset='utf-8'\r\n";
	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$message .= "<br>New Poster Printer Order From " . $order->get_email() . "\r\n";
	$message .= "<br>\r\n";
	$message .= "<br>" . nl2br($order->get_job_info(),false);
	$message .= "<br>To view the order <a href='" . $urlAddress . "admin/orders.php?orderId=" . $order->get_order_id() . "'>click here</a>" . "\r\n";

	//plain text email
        $message .= "\r\n\r\n--" . $boundary . "\r\n";
	$message .= "Content-type:text/plain;charset='utf-8'\r\n";
	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= "New Poster Printer Order From " . $order->get_email() . "\r\n\r\n";
	$message .= $order->get_job_info();
        $message .= "To view the order: " . $urlAddress . "admin/orders.php?orderId=" . $order->get_order_id() . "\r\n";
	$message .= "\r\n\r\n--" . $boundary . "--\r\n";
	
	//headers
	$headers = "MIME-Version: 1.0\r\n"; 	
	$headers .= "From: " . $order->get_email() . "\r\n";
	$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";
	mail($to,$subject,$message,$headers," -f " . $order->get_email());

}

//mailUserNewOrder()
//$db - database object
//$order - order object
//Emails User order confirmation
function mailUserNewOrder($db,$order) {
	$boundary = uniqid('np');
	$to = $order->get_email();
	$subject = "Poster Order #" . $order->get_order_id();
	
	//html email
        $message = "\r\n\r\n--" . $boundary . "\r\n";
	$message .= "Content-type:text/html;charset='utf-8'\r\n";
	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$message .= "<br>Thank you for your poster order.\r\n";
	$message .= "For regular orders, we guarantee within 72 hours, excluding weekends.\r\n";
	$message .= "For rush orders, we guarantee within 24 hours, excluding weekends.\r\n";
	$message .= "We will email you when the poster is completed printing.\r\n";
	$message .= "<p>For your reference\r\n";
	$message .= "<br>\r\n";
	$message .= "<br>" . nl2br($order->get_job_info(),false);

	///plain text email
	$message .= "\r\n\r\n--" . $boundary . "\r\n";
	$message .= "Content-type:text/plain;charset='utf-8'\r\n";
	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$message .= "Thank you for your poster order.\r\n";
        $message .= "For regular orders, we guarantee within 72 hours, excluding weekends.\r\n";
        $message .= "For rush orders, we guarantee within 24 hours, excluding weekends.\r\n";
        $message .= "We will email you when the poster is completed printing.\r\n";
        $message .= "For your reference\r\n\r\n";
	$message .= $order->get_job_info();
	$message .= "\r\n\r\n--" . $boundary . "--\r\n";

	//headers
	$headers = "MIME-Version: 1.0\r\n";     
        $headers .= "From: " . settings::get_admin_email() . "\r\n";
	if ($order->get_cc_emails() != "") {
                $headers .= "Cc: " . $order->get_cc_emails() . "\r\n";
        }
        $headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";
	
	mail($to,$subject,$message,$headers, " -f " . settings::get_admin_email());
}

//mailuserOrderComplete()
//$db - database object
//$orderId - integer - order number
//emails user that the order is complete
function mailUserOrderComplete($db,$orderId) {
	$boundary = uniqid('np');	
	$order = new order($db,$orderId);
	$to = $order->get_email();

	$subject = "Poster Order #" . $order->get_order_id() . " Completed";

	//HTML Email
	$message = "\r\n\r\n--" . $boundary . "\r\n";
	$message .= "Content-type:text/html;charset='utf-8'\r\n";
	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$message .= "<br>Your Poster Order #" . $order->get_order_id() . " is now completed.\r\n";
	$message .= "<br>You can come to Room 2626 to pick up your poster.\r\n";
	$message .= "<br>\r\n";
	$message .= "<br>" . nl2br($order->get_job_info(),false);
	
	//Plain Text email
	$message .= "\r\n\r\n--" . $boundary . "\r\n";
	$message .= "Content-type:text/plain;charset='utf-8'\r\n";
	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= "Your Poster Order #" . $order->get_order_id() . " is now completed.\r\n";
        $message .= "You can come to Room 2626 to pick up your poster.\r\n\r\n";
        $message .= $order->get_job_info();
	$message .= "\r\n\r\n--" . $boundary . "--\r\n";


	//Headers
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "From: " . settings::get_admin_email() . "\r\n";
	$headers .= "Cc: " . settings::get_admin_email() . "\r\n";
	if ($order->get_cc_emails() != "") {
                $headers .= "Cc: " . $order->get_cc_emails() . "\r\n";
        }
	$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";

	mail($to,$subject,$message,$headers, " -f " . settings::get_admin_email());

}




?>
