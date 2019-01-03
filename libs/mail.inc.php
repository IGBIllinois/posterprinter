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

require_once 'db.class.inc.php';
require_once 'order.class.inc.php';

//mailNewOrder()
//$db - database object
//$orderId - integer - order number
//$adminEmail - string - admin email
//emails the user and admins that a new order has been made
function mailNewOrder($db,$orderId,$adminEmail) {
	$order = new order($db,$orderId);
	mailAdminsNewOrder($db,$order,$adminEmail);
	mailUserNewOrder($db,$order,$adminEmail);

}

//mailAdminsNewOrer()
//$db - database object
//$order - order object
//$adminEmail - string - admin email
//emails admin of the new order
function mailAdminsNewOrder($db,$order,$adminEmail) {

	$requestUri = substr($_SERVER["REQUEST_URI"],0,strrpos($_SERVER["REQUEST_URI"], "/")+1);
	$urlAddress = "http://" . $_SERVER["SERVER_NAME"] . $requestUri; 
	$subject = "New Poster To Print. Order #" . $order->get_order_id();
	$to = $adminEmail;
	$message = "<br>New Poster Printer Order From " . $order->get_email() . "\r\n";
	$message .= "<p>Full Name: " . $order->get_name() . "\r\n";
	$message .= "<br>Order Number: " . $order->get_order_id() . "\r\n"; 
	$message .= "<br>Email: " . $order->get_email() . "\r\n";
	$message .= "<br>Poster File: " . $order->get_filename() . "\r\n";
	$message .= "<br>Poster Length: " . $order->get_length() . " inches \r\n";
	$message .= "<br>Poster Width: " . $order->get_width() . " inches \r\n";
	$message .= "<br>CFOP: " . $order->get_cfop() . "\r\n";
	$message .= "<br>Activity Code: " . $order->get_activity_code() . "\r\n";
	$message .= "<br>Paper Type: " . $order->get_paper_type_name() . "\r\n";
	$message .= "<br>Finish Option: " . $order->get_finish_option_name() . "\r\n";
	$message .= "<br>Poster Tube: " . $order->get_poster_tube_name() . "\r\n";
	$message .= "<br>Rush Order: " . $order->get_rush_order_name() . "\r\n";
	$message .= "<br>Comments: " . $order->get_comments() . "\r\n";
	$message .= "<br>Total Cost: $" . $order->get_total_cost() . "\r\n";
	$message .= "<br>To view the order <a href='" . $urlAddress . "admin/orders.php?orderId=" . $order->get_order_id() . "'>click here</a>" . "\r\n";
	
	$headers = "From: " . $order->get_email() . "\r\n";
	$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
	mail($to,$subject,$message,$headers," -f " . $order->get_email());

}

//mailUserNewOrder()
//$db - database object
//$order - order object
//$adminEmail - string - admin email
//Emails User order confirmation
function mailUserNewOrder($db,$order,$adminEmail) {

	$to = $order->get_email();
	$subject = "Poster Order #" . $order->get_order_id();
	
	$message = "<br>Thank you for your order.  Your order will be processed as soon as possible.   ";
	$message .= "It could take up to three days.  We will email you when the poster is completed printing.\r\n";
	$message .= "<p>For your reference\r\n";
	$message .= "<br>Full Name: " . $order->get_name() . "\r\n";
	$message .= "<br>Order Number: " . $order->get_order_id() . "\r\n";
	$message .= "<br>Poster File: " . $order->get_filename() . "\r\n";
	$message .= "<br>Poster Length: " . $order->get_length() . " inches \r\n";
	$message .= "<br>Poster Width: " . $order->get_width() . " inches \r\n";
	$message .= "<br>CFOP: " . $order->get_cfop() . "\r\n";
	$message .= "<br>Activity Code: " . $order->get_activity_code() . "\r\n";
	$message .= "<br>Paper Type: " . $order->get_paper_type_name() . "\r\n";
	$message .= "<br>Finish Option: " . $order->get_finish_option_name() . "\r\n";
	$message .= "<br>Poster Tube: " . $order->get_poster_tube_name() . "\r\n";
	$message .= "<br>Rush Order: " . $order->get_rush_order_name() . "\r\n";
	$message .= "<br>Comments: " . $order->get_comments() . "\r\n";
	$message .= "<br>Total Cost: $" . $order->get_total_cost() . "\r\n";
	
	$headers = "From: " . $adminEmail . "\r\n";
	$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
	mail($to,$subject,$message,$headers, " -f " . $adminEmail);
}

//mailuserOrderComplete()
//$db - database object
//$orderId - integer - order number
//$adminEmail - string - admin email
//emails user that the order is complete
function mailUserOrderComplete($db,$orderId,$adminEmail) {
	
	$order = new order($db,$orderId);
	$to = $order->get_email();
	$subject = "Poster Order #" . $order->get_order_id() . " Completed";
			
	$message = "<br>Your Poster Order #" . $order->get_order_id() . " is now completed.\r\n";
	$message .=	"<br>You can come to Room 2626 to pick up your poster.\r\n";
	$message .=	"<p>Order Number: " . $order->get_order_id() . "\r\n";
	$message .=	"<br>Poster File: " . $order->get_filename() . "\r\n";
	$message .=	"<br>Poster Length: " . $order->get_length() . " inches \r\n";
	$message .=	"<br>Poster Width: " . $order->get_width() . " inches \r\n";
	$message .=	"<br>CFOP: " .  $order->get_cfop() . "\r\n";
	$message .=	"<br>Activity Code: " . $order->get_activity_code() . "\r\n";
	$message .=	"<br>Paper Type: " . $order->get_paper_type_name() . "\r\n";
	$message .=	"<br>Finish Option: " . $order->get_finish_option_name() . "\r\n"; 
	$message .=	"<br>Poster Tube: " . $order->get_poster_tube_name() . "\r\n";
	$message .=	"<br>Rush Order: " . $order->get_rush_order_name() . "\r\n";
	$message .=	"<br>Comments: " . $order->get_comments() . "\r\n";
	$message .=	"<br>Total Cost: $" . $order->get_total_cost() . "\r\n";
	
	$headers = "From: " . $adminEmail . "\r\n";
	$headers .= "Cc: " . $adminEmail . "\r\n";
	$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
	mail($to,$subject,$message,$headers, " -f " . $adminEmail);

}
?>
