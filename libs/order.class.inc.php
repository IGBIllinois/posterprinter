<?php


class order {

////////////////Private Variables//////////

	private $db; //mysql database object
	private $id;
	private $email;
	private $cc_emails; 
	private $name;
	private $filename;
	private $cfop;
	private $activity_code;
	private $time_created;
	private $time_finished;
	private $total_cost;
	private $width;
	private $length;
	private $paper_type_name;
	private $paper_type_id;
	private $finish_option_name;
	private $finish_option_id;
	private $poster_tube_name;
	private $poster_tube_id;
	private $rush_order_name;
	private $rush_order_id;
	private $comments;
	private $status;

////////////////Public Functions///////////

	public function __construct($db,$order_id) {
		$this->db = $db;
		$this->id = $order_id;
		$this->get_order();
	}
	public function __destruct() { }
	public function get_order_id() { return $this->id; }
	public function get_email() { return $this->email; }
	public function get_cc_emails() { return $this->cc_emails; }
	public function get_name() { return $this->name; }
	public function get_filename() { return $this->filename; }
	public function get_filetype() { 
		return strtolower(pathinfo($this->get_filename(), PATHINFO_EXTENSION));
	}

	public function get_filesize() {
		$full_path = "/var/www/html/eclipse/posterprinter/" . settings::get_poster_dir() . "/" . $this->get_order_id() . "." . $this->get_filetype();
		if (file_exists($full_path)) {
			$bytes = filesize($full_path);
			$megabytes = round($bytes / 1000000,2);
			return $megabytes;
		}
		return false;
	}	
	public function get_cfop() { return $this->cfop; }
	public function get_cfop_college() { return substr($this->get_cfop(),0,1); }
	public function get_cfop_fund() { return substr($this->get_cfop(),2,6); }
	public function get_cfop_organization() { return substr($this->get_cfop(),9,6); }
	public function get_cfop_program() { return substr($this->get_cfop(),16,6); }
	public function get_activity_code() { return $this->activity_code; }
	public function get_time_created() { return $this->time_created; }
	public function get_total_cost() { return $this->total_cost; }
	public function get_width() { return $this->width; }
	public function get_length() { return $this->length; }
	public function get_paper_type_name() { return $this->paper_type_name; }
	public function get_paper_type_id() { return $this->paper_type_id; }
	public function get_finish_option_name() { return $this->finish_option_name; }
	public function get_finish_option_id() { return $this->finish_option_id; }
	public function get_poster_tube_name() { return $this->poster_tube_name; }
	public function get_poster_tube_id() { return $this->poster_tube_id; }
	public function get_rush_order_name() { return $this->rush_order_name; }
	public function get_rush_order_id() { return $this->rush_order_id; }
	public function get_comments() { return $this->comments; }
	public function get_status() { return $this->status; }

	public function get_thumbnail() {
		$path = "../" . settings::get_poster_dir() . "/thumb_" . $this->get_order_id() . ".jpg";
		if (file_exists($path)) {
			return $path;
		}
	}
        public function get_fullsize() {
                $path = "../" . settings::get_poster_dir() . "/fullsize_" . $this->get_order_id() . ".jpg";
                if (file_exists($path)) {
                        return $path;
                }
        }	
	public function set_status($status) {
	
		$time_finished = date( 'Y-m-d H:i:s');
		$sql = "UPDATE orders ";
		$sql .= "SET orders_status='" . $status . "' ";
		if ($status == 'Completed') {
			$sql .= ",orders_timeFinished='" . $time_finished . "' ";
		}
		$sql .= "WHERE orders_id='" . $this->get_order_id() . "' LIMIT 1";
		$result = $this->db->non_select_query($sql);
		if ($result) {
			if ($status == 'Completed') {
				$this->time_finished = $time_finished;
			}
			$this->status = $status;
			return array('RESULT'=>true,'MESSAGE'=>'Status successfully updated to ' . $this->status);
		}
		return array('RESULT'=>false,'MESSAGE'=>'Status Update Failed');
			
	}
	
	public function edit($cfop, $activityCode, $finishOptionId, $paperTypeId, $posterTubeId, $rushOrderId, $totalCost) {
		
		$sql = "UPDATE orders SET orders_cfop='" . $cfop . "', ";
		$sql .= "orders_activityCode='" . $activityCode . "', ";
		$sql .= "orders_finishOptionsId='" . $finishOptionId . "', ";
		$sql .= "orders_paperTypesId='" . $paperTypeId . "', ";
		$sql .= "orders_posterTubeId='" . $posterTubeId . "', ";
		$sql .= "orders_rushOrderId='" . $rushOrderId . "', ";
		$sql .= "orders_widthSwitched='" . $widthSwitched . "', ";
		$sql .= "orders_totalCost='" . $totalCost . "' ";
		$sql .= "WHERE orders_id='" . $this->get_order_id() . "' LIMIT 1 ";
		$this->db->non_select_query($sql);
		$this->get_order();
		return true;
	}

	public function get_job_info() {

		$message = "Order Number: " . $this->get_order_id() . "\r\n";
        	$message .= "Full Name: " . $this->get_name() . "\r\n";
	        $message .= "Email: " . $this->get_email() . "\r\n";
        	$message .= "Poster File: " . $this->get_filename() . "\r\n";
	        $message .= "Poster Length: " . $this->get_length() . " inches \r\n";
        	$message .= "Poster Width: " . $this->get_width() . " inches \r\n";
	        $message .= "CFOP: " .  $this->get_cfop() . "\r\n";
        	$message .= "Activity Code: " . $this->get_activity_code() . "\r\n";
	        $message .= "Paper Type: " . $this->get_paper_type_name() . "\r\n";
        	$message .= "Finish Option: " . $this->get_finish_option_name() . "\r\n";
	        $message .= "Poster Tube: " . $this->get_poster_tube_name() . "\r\n";
        	$message .= "Rush Order: " . $this->get_rush_order_name() . "\r\n";
	        $message .= "Comments: " . $this->get_comments() . "\r\n";
        	$message .= "Total Cost: $" . $this->get_total_cost() . "\r\n";
		return $message;
	}

	//mailNewOrder()
	//emails the user and admins that a new order has been made
	public function mailNewOrder() {
	        mailAdminsNewOrder();
        	mailUserNewOrder();

	}

	//mailAdminsNewOrer()
	//$db - database object
	//$order - order object
	//emails admin of the new order
	public function mailAdminsNewOrder($db,$order) {
        	$boundary = uniqid('np');
	        $requestUri = substr($_SERVER["REQUEST_URI"],0,strrpos($_SERVER["REQUEST_URI"], "/")+1);
        	$urlAddress = "http://" . $_SERVER["SERVER_NAME"] . $requestUri;
	        $subject = "New Poster To Print - Order #" . $this->get_order_id();
        	$to = settings::get_admin_email();

	        //html email
        	$message = "\r\n\r\n--" . $boundary . "\r\n";
	        $message .= "Content-type:text/html;charset='utf-8'\r\n";
        	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	        $message .= "<br>New Poster Printer Order From " . $this->get_email() . "\r\n";
        	$message .= "<br>\r\n";
	        $message .= "<br>" . nl2br($this->get_job_info(),false);
        	$message .= "<br>To view the order <a href='" . $urlAddress . "admin/orders.php?orderId=" . $this->get_order_id() . "'>click here</a>" . "\r\n";

	        //plain text email
        	$message .= "\r\n\r\n--" . $boundary . "\r\n";
	        $message .= "Content-type:text/plain;charset='utf-8'\r\n";
        	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	        $message .= "New Poster Printer Order From " . $this->get_email() . "\r\n\r\n";
        	$message .= $this->get_job_info();
	        $message .= "To view the order: " . $urlAddress . "admin/orders.php?orderId=" . $this->get_order_id() . "\r\n";
        	$message .= "\r\n\r\n--" . $boundary . "--\r\n";

	        //headers
        	$headers = "MIME-Version: 1.0\r\n";
	        $headers .= "From: " . $this->get_email() . "\r\n";
        	$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";
	        mail($to,$subject,$message,$headers," -f " . $this->get_email());

	}

	//mailUserNewOrder()
	//Emails User order confirmation
	public function mailUserNewOrder() {
        	$boundary = uniqid('np');
	        $to = $this->get_email();
        	$subject = "Poster Order #" . $this->get_order_id();

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
	        $message .= "<br>" . nl2br($this->get_job_info(),false);

        	///plain text email
	        $message .= "\r\n\r\n--" . $boundary . "\r\n";
        	$message .= "Content-type:text/plain;charset='utf-8'\r\n";
	        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        	$message .= "Thank you for your poster order.\r\n";
	        $message .= "For regular orders, we guarantee within 72 hours, excluding weekends.\r\n";
        	$message .= "For rush orders, we guarantee within 24 hours, excluding weekends.\r\n";
	        $message .= "We will email you when the poster is completed printing.\r\n";
        	$message .= "For your reference\r\n\r\n";
	        $message .= $this->get_job_info();
        	$message .= "\r\n\r\n--" . $boundary . "--\r\n";

	        //headers
        	$headers = "MIME-Version: 1.0\r\n";
	        $headers .= "From: " . settings::get_admin_email() . "\r\n";
        	if ($this->get_cc_emails() != "") {
                	$headers .= "Cc: " . $this->get_cc_emails() . "\r\n";
	        }
        	$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";

	        mail($to,$subject,$message,$headers, " -f " . settings::get_admin_email());
	}

	//mailuserOrderComplete()
	//emails user that the order is complete
	function mailUserOrderComplete() {
        	$boundary = uniqid('np');
        	$to = $this->get_email();

	        $subject = "Poster Order #" . $this->get_order_id() . " Completed";

	        //HTML Email
        	$message = "\r\n\r\n--" . $boundary . "\r\n";
	        $message .= "Content-type:text/html;charset='utf-8'\r\n";
        	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	        $message .= "<br>Your Poster Order #" . $this->get_order_id() . " is now completed.\r\n";
        	$message .= "<br>You can come to Room 2626 to pick up your poster.\r\n";
	        $message .= "<br>\r\n";
        	$message .= "<br>" . nl2br($this->get_job_info(),false);

	        //Plain Text email
        	$message .= "\r\n\r\n--" . $boundary . "\r\n";
	        $message .= "Content-type:text/plain;charset='utf-8'\r\n";
        	$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	        $message .= "Your Poster Order #" . $this->get_order_id() . " is now completed.\r\n";
        	$message .= "You can come to Room 2626 to pick up your poster.\r\n\r\n";
	        $message .= $this->get_job_info();
        	$message .= "\r\n\r\n--" . $boundary . "--\r\n";


	        //Headers
        	$headers = "MIME-Version: 1.0\r\n";
	        $headers .= "From: " . settings::get_admin_email() . "\r\n";
        	$headers .= "Cc: " . settings::get_admin_email() . "\r\n";
	        if ($this->get_cc_emails() != "") {
        	        $headers .= "Cc: " . $this->get_cc_emails() . "\r\n";
	        }
        	$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";

	        mail($to,$subject,$message,$headers, " -f " . settings::get_admin_email());

	}

	
	/////////////////Private Functions///////////
	
	private function get_order() {
	
		$sql = "SELECT orders.*, paperTypes.*,finishOptions.*,posterTube.*,rushOrder.* FROM orders ";
		$sql .= "LEFT JOIN paperTypes ON orders.orders_paperTypesId=paperTypes.paperTypes_id ";
		$sql .= "LEFT JOIN finishOptions ON orders.orders_finishOptionsId=finishOptions.finishOptions_id ";
		$sql .= "LEFT JOIN posterTube ON orders.orders_posterTubeId=posterTube.posterTube_id ";
		$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
		$sql .= "WHERE orders_id='" . $this->get_order_id() . "'";
		$result = $this->db->query($sql);
		if (count($result)) {
			$this->email = $result[0]["orders_email"];
			$this->cc_emails = $result[0]['orders_cc_emails'];
			$this->name = ucwords(strtolower($result[0]["orders_name"]));
			$this->filename = $result[0]["orders_fileName"];
			$this->cfop = $result[0]["orders_cfop"];
			$this->activity_code = $result[0]['orders_activityCode'];
			$this->time_created = $result[0]["orders_timeCreated"];
			$this->time_finished = $result[0]["orders_timeFinished"];
			$this->total_cost = $result[0]["orders_totalCost"];
			$this->width = $result[0]["orders_width"];
			$this->length =  $result[0]["orders_length"];
			$this->paper_type_name = $result[0]["paperTypes_name"];
			$this->paper_type_id = $result[0]["paperTypes_id"];
			$this->finish_option_name = $result[0]["finishOptions_name"];
			$this->finish_option_id = $result[0]["finishOptions_id"];
			$this->poster_tube_name = $result[0]["posterTube_name"];
			$this->poster_tube_id = $result[0]["posterTube_id"];
			$this->rush_order_name = $result[0]["rushOrder_name"];
			$this->rush_order_id = $result[0]["rushOrder_id"];
			$this->comments = $result[0]["orders_comments"];
			$this->status = $result[0]["orders_status"];
		}
	}
	


}

?>
