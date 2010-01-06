<?php
//////////////////////////////////////////////////////////////////////////////
//																			//
//	Poster Printer Order Submittion											//
//	mail.inc.php															//
//																			//
//	Functions to mail the user the order information when order is 			//
//	submitted and completed and email admins when a new order is 			//
//	submitted																//
//																			//
//	David Slater															//
//	April 2007																//
//																			//
//////////////////////////////////////////////////////////////////////////////



function mailAdminsNewOrder($orderInfo) {

	$requestUri = substr($_SERVER["REQUEST_URI"],0,strrpos($_SERVER["REQUEST_URI"], "/")+1);
	$urlAddress = "http://" . $_SERVER["SERVER_NAME"] . $requestUri; 
	$subject = "New Poster To Print. Order #" . $orderInfo['orderID'];
	$to = $orderInfo['adminEmail'];
	$message = "<br>New Poster Printer Order From " . $orderInfo['email'] . "\r\n" .
			"<p>Full Name: " . $orderInfo['name'] . "\r\n" . 
			"<p>Order Number: " . $orderInfo['orderID'] . "\r\n" . 
			"<br>Email: " . $orderInfo['email'] . "\r\n" .
			"<br>Poster File: " . $orderInfo['fileName'] . "\r\n" .
			"<br>Poster Length: " . $orderInfo['posterLength'] . " inches \r\n" .
			"<br>Poster Width: " . $orderInfo['posterWidth'] . " inches \r\n" .
			"<br>CFOP: " . $orderInfo['cfop'] . "\r\n" .
			"<br>Paper Type: " . $orderInfo['paperType'] . "\r\n" .
			"<br>Finish Option: " . $orderInfo['finishOption'] . "\r\n" .
			"<br>Poster Tube: " . $orderInfo['posterTube'] . "\r\n" .
			"<br>Rush Order: " . $orderInfo['rushOrder'] . "\r\n" .
			"<br>Comments: " . $orderInfo['comments'] . "\r\n" .
			"<br>Total Cost: $" . $orderInfo['totalCost'] . "\r\n" .
			"<br>To view the order <a href='" . $urlAddress . "admin/orders.php?orderId=" . $orderInfo['orderID'] . "'>click here</a>" . "\r\n";
	
	$headers = "From: " . $orderInfo['email'] . "\r\n" .
				"Content-Type: text/html; charset=iso-8859-1" . "\r\n";
	mail($to,$subject,$message,$headers);

}

function mailUserNewOrder($orderInfo) {

	$to = $orderInfo['email'];
	$subject = "Poster Order #" . $orderInfo['orderID'];
	
	$message = "<br>Thank you for your order.  Your order will be processed as soon as possible.   It could take up to three days.  We will email you when the poster is completed printing.\r\n" .
			"<p>For your reference\r\n" .
			"<br>Full Name: " . $orderInfo['name'] . "\r\n" .
			"<br>Order Number: " . $orderInfo['orderID'] . "\r\n" .
			"<br>Poster File: " . $orderInfo['fileName'] . "\r\n" .
			"<br>Poster Length: " . $orderInfo['posterLength'] . " inches \r\n" .
			"<br>Poster Width: " . $orderInfo['posterWidth'] . " inches \r\n" .
			"<br>CFOP: " . $orderInfo['cfop'] . "\r\n" .
			"<br>Paper Type: " . $orderInfo['paperType'] . "\r\n" .
			"<br>Finish Option: " . $orderInfo['finishOption'] . "\r\n" .
			"<br>Poster Tube: " . $orderInfo['posterTube'] . "\r\n" .
			"<br>Rush Order: " . $orderInfo['rushOrder'] . "\r\n" .
			"<br>Comments: " . $orderInfo['comments'] . "\r\n" .
			"<br>Total Cost: $" . $orderInfo['totalCost'] . "\r\n";
	
	$headers = "From: " . $orderInfo['adminEmail'] . "\r\n" .
				"Content-Type: text/html; charset=iso-8859-1" . "\r\n";
	mail($to,$subject,$message,$headers);
}

function mailUserOrderComplete($orderInfo) {

	$to = $orderInfo['email'];
	$subject = "Poster Order #" . $orderInfo['orderID'] . " Completed";
	
	$message = "<br>Your Poster Order #" . $orderInfo['orderID'] . " is now completed.\r\n" .
			"<br>You can come to Room 2626 to pick up your poster.\r\n" .
			"<p>Order Number: " . $orderInfo['orderID'] . "\r\n" .
			"<br>Poster File: " . $orderInfo['fileName'] . "\r\n" .
			"<br>Poster Length: " . $orderInfo['posterLength'] . " inches \r\n" .
			"<br>Poster Width: " . $orderInfo['posterWidth'] . " inches \r\n" .
			"<br>CFOP: " . $orderInfo['cfop'] . "\r\n" .
			"<br>Paper Type: " . $orderInfo['paperType'] . "\r\n" .
			"<br>Finish Option: " . $orderInfo['finishOption'] . "\r\n" . 
			"<br>Poster Tube: " . $orderInfo['posterTube'] . "\r\n" .
			"<br>Rush Order: " . $orderInfo['rushOrder'] . "\r\n" .
			"<br>Comments: " . $orderInfo['comments'] . "\r\n" .
			"<br>Total Cost: $" . $orderInfo['totalCost'] . "\r\n";
	
	$headers = "From: " . $orderInfo['adminEmail'] . "\r\n" .
				"Cc: " . $orderInfo['adminEmail'] . "\r\n" .
				"Content-Type: text/html; charset=iso-8859-1" . "\r\n";
	mail($to,$subject,$message,$headers);

}
?>
