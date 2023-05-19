<?php


class order {

////////////////Private Variables//////////

	private $db; //mysql database object
	private $id;
	private $email;
	private $cc_emails = array(); 
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
	private $rotated;
	private $key = false;

	const ORDER_PAGE = "order.php";
	const STATUS_PAGE = "status.php";
	const LINE_WIDTH = 80;
	const THUMB_PREFIX = "thumb_";
	const FULLSIZE_PREFIX = "fullsize_";
	const STATUS = array('NEW'=>'New',
			'IN_PROGRESS'=>'In Progress',
			'COMPLETED'=>'Completed',
			'CANCEL'=>'Cancel',
			'ON_HOLD'=>'On Hold'
		);
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
		$path = $this->get_file();
		if (file_exists($path)) {
			$bytes = filesize($path);
			$megabytes = round($bytes / 1000000,2);
			return $megabytes;
		}
		return 0;
	}
	public function get_file() {
		$path = $this->get_poster_path() . "/" . $this->get_order_id() . "." . $this->get_filetype();
		if (file_exists($path)) {
			return $path;
		}
		return false;
	}
	public function get_thumbnail() {
		$path = $this->get_poster_path() . "/" . self::THUMB_PREFIX . $this->get_order_id() . ".jpg";
		if (file_exists($path)) {
			return $path;
		}
		return false;
	}
	public function get_fullsize() {
                $path = $this->get_poster_path() . "/" . self::FULLSIZE_PREFIX . $this->get_order_id() . ".jpg";
                if (file_exists($path)) {
                        return $path;
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
	public function get_rotated() { return $this->rotated; }
	public function get_key() { return $this->key; }
	public function get_wordwrap_comments() { 
		return wordwrap($this->comments,self::LINE_WIDTH,"<br>");

	}
	public function get_status() { return $this->status; }

	public function set_status($status) {
	
		$time_finished = date( 'Y-m-d H:i:s');
		$parameters = array(
			':status'=>$status
		);
		$sql = "UPDATE orders ";
		$sql .= "SET orders_status=:status ";
		if ($status == self::STATUS['COMPLETED']) {
			$sql .= ",orders_timeFinished=:time_finished ";
			$parameters[':time_finished'] = $time_finished;
		}
		$sql .= "WHERE orders_id='" . $this->get_order_id() . "' LIMIT 1";
		$result = $this->db->non_select_query($sql,$parameters);
		if ($result) {
			if ($status == self::STATUS['COMPLETED']) {
				$this->time_finished = $time_finished;
			}
			$this->status = $status;
			return array('RESULT'=>true,'MESSAGE'=>'Status successfully updated to ' . $this->status);
		}
		return array('RESULT'=>false,'MESSAGE'=>'Status Update Failed');
			
	}
	
	public function edit($cfop, $activity_code, $finishOptionId, $paperTypeId, $posterTubeId, $rushOrderId, $totalCost) {
		
		$sql = "UPDATE orders SET orders_cfop=:cfop, ";
		$sql .= "orders_activityCode=:activity_code, ";
		$sql .= "orders_finishOptionsId=:finishoption_id, ";
		$sql .= "orders_paperTypesId=:papertype_id, ";
		$sql .= "orders_posterTubeId=:postertube_id, ";
		$sql .= "orders_rushOrderId=:rushorder_id, ";
		$sql .= "orders_totalCost=:totalcost ";
		$sql .= "WHERE orders_id=:order_id LIMIT 1 ";
		$parameters = array(
			':cfop'=>$cfop,
			':activity_code'=>$activity_code,
			':finishoption_id'=>$finishOptionId,
			':papertype_id'=>$paperTypeId,
			':postertube_id'=>$posterTubeId,
			':rushorder_id'=>$rushOrderId,
			':totalcost'=>$totalCost,
			':order_id'=>$this->get_order_id()
		);
		$this->db->non_select_query($sql,$parameters);
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
	        $this->mailAdminsNewOrder();
        	$this->mailUserNewOrder();

	}

	//mailAdminsNewOrer()
	//$db - database object
	//$order - order object
	//emails admin of the new order
	public function mailAdminsNewOrder() {
	        
	        $subject = "New Poster To Print - Order #" . $this->get_order_id();
        	$to = settings::get_admin_email();
		$loader = new \Twig\Loader\FilesystemLoader(settings::get_twig_dir());
		$twig = new \Twig\Environment($loader);

		if (file_exists(settings::get_twig_dir() . "/custom/order_new_admin.html.twig")) {
		$html_message = $twig->render('/custom/order_new_admin.html.twig',$this->get_twig_variables());
		$txt_message = $twig->render('/custom/order_new_admin.txt.twig',$this->get_twig_variables());
		}
		else {
			$html_message = $twig->render('/default/order_new_admin.html.twig',$this->get_twig_variables());
	                $txt_message = $twig->render('/default/order_new_admin.txt.twig',$this->get_twig_variables());
		}

		$email = new \IGBIllinois\email(settings::get_smtp_host(),
						settings::get_smtp_port(),
						settings::get_smtp_username(),
						settings::get_smtp_password()
		);
		$email->set_to_emails($to);

		try {
			$result = $email->send_email($this->get_email(),$subject,$txt_message,$html_message);
			$message = "Email successfully sent to " . $this->get_email();

		} catch (Exception $e) {
			throw new \Exception("Email Error sending mail. " . $e->getMessage());
			return false;;
		}
		return $result;
	}

	//mailUserNewOrder()
	//Emails User order confirmation
	public function mailUserNewOrder() {
	        
		$to = $this->get_email();
        	$subject = "Poster Order #" . $this->get_order_id();
		$loader = new \Twig\Loader\FilesystemLoader(settings::get_twig_dir());
                $twig = new \Twig\Environment($loader);

		if (file_exists(settings::get_twig_dir() . "/custom/order_new_user.html.twig")) {
	                $html_message = $twig->render('/custom/order_new_user.html.twig',$this->get_twig_variables());
        	        $txt_message = $twig->render('/custom/order_new_user.txt.twig',$this->get_twig_variables());
		}
		else {
			$html_message = $twig->render('/default/order_new_user.html.twig',$this->get_twig_variables());
			$txt_message = $twig->render('/default/order_new_user.txt.twig',$this->get_twig_variables());

		}
		$email = new \IGBIllinois\email(settings::get_smtp_host(),
                                                settings::get_smtp_port(),
                                                settings::get_smtp_username(),
                                                settings::get_smtp_password()
                );
		$email->set_to_emails($to);
		if ($this->get_cc_emails() != "") {
			$email->set_cc_emails($this->get_cc_emails());
		}
                try {
                        $result = $email->send_email(settings::get_admin_email(),$subject,$txt_message,$html_message);
                        $message = "Email successfully sent to " . $this->get_email();

                } catch (Exception $e) {
                        throw new \Exception("Email Error sending mail. " . $e->getMessage());
                        return false;;
                }
                return $result;
	}

	//mailuserOrderComplete()
	//emails user that the order is complete
	function mailUserOrderComplete() {
        	$to = $this->get_email();

	        $subject = "Poster Order #" . $this->get_order_id() . " Completed";

		$loader = new \Twig\Loader\FilesystemLoader(settings::get_twig_dir());

                $twig = new \Twig\Environment($loader);

		if (file_exists(settings::get_twig_dir() . "/custom/order_commplete_user.html.twig")) {
                $html_message = $twig->render('/custom/order_complete_user.html.twig',$this->get_twig_variables());
		$txt_message = $twig->render('/custom/order_complete_user.txt.twig',$this->get_twig_variables());
		}
		else {
			$html_message = $twig->render('/default/order_complete_user.html.twig',$this->get_twig_variables());
	                $txt_message = $twig->render('/default/order_complete_user.txt.twig',$this->get_twig_variables());

		}


                if ($this->get_cc_emails() != "") {
                        $extraheaders['Cc'] = $this->get_cc_emails();
                }
		$email = new \IGBIllinois\email(settings::get_smtp_host(),
                                                settings::get_smtp_port(),
                                                settings::get_smtp_username(),
                                                settings::get_smtp_password()
                );

		$email->set_to_emails($to);
                if ($this->get_cc_emails() != "") {
                        $email->set_cc_emails($this->get_cc_emails());
                }
                try {
                        $result = $email->send_email(settings::get_admin_email(),$subject,$txt_message,$html_message);
                        $message = "Email successfully sent to " . $this->get_email();

                } catch (Exception $e) {
                        throw new \Exception("Email Error sending mail. " . $e->getMessage());
                        return false;;
                }
                return $result;

	}

	public static function get_all_statuses() {
		$status_array = array_values(self::STATUS);	
		return $status_array;

	}	
	/////////////////Private Functions///////////
	
	private function get_order() {
	
		$sql = "SELECT orders.*, paperTypes.*,finishOptions.*,posterTube.*,rushOrder.* FROM orders ";
		$sql .= "LEFT JOIN paperTypes ON orders.orders_paperTypesId=paperTypes.paperTypes_id ";
		$sql .= "LEFT JOIN finishOptions ON orders.orders_finishOptionsId=finishOptions.finishOptions_id ";
		$sql .= "LEFT JOIN posterTube ON orders.orders_posterTubeId=posterTube.posterTube_id ";
		$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
		$sql .= "WHERE orders_id=:order_id";
		$parameters = array(
			':order_id'=>$this->get_order_id()
		);
		$result = $this->db->query($sql,$parameters);
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
			$this->rotated = $result[0]["orders_rotated"];
			if (!is_null($result[0]['orders_key'])) {
				$this->key = $result[0]['orders_key'];
			}
		}
	}
	
	private function get_twig_variables() {
		$requestUri = substr($_SERVER["REQUEST_URI"],0,strrpos($_SERVER["REQUEST_URI"], "/")+1);
                $order_url = "http://" . $_SERVER["SERVER_NAME"] . $requestUri . "admin/" . self::ORDER_PAGE . "?order_id=" . $this->get_order_id();
		$status_url = "http://" . $_SERVER["SERVER_NAME"] . $requestUri . self::STATUS_PAGE . "?order_id=" . $this->get_order_id() . "&key=" . $this->get_key();
                $twig_variables = array(
			'css' => file_get_contents(dirname(__DIR__) . "/vendor/twbs/bootstrap/dist/css/bootstrap.min.css"), 
			'order_id' => $this->get_order_id(),
                        'name' => $this->get_name(),
                        'email' => $this->get_email(),
                        'job_info' => $this->get_job_info(),
                        'regular_order' => settings::get_order_timeframe(),
                        'rush_order' => settings::get_rush_order_timeframe(),
			'admin_email' => settings::get_admin_email(),
			'order_url' => $order_url,
			'status_url' => $status_url,
			'thumbnail'=>$this->get_thumbnail(),
                );
		return $twig_variables;

	}

	private function get_poster_path() {
		$path = dirname(__DIR__) . "/" . settings::get_poster_dir() . "/" . $this->get_order_id();
		return $path;
	}
}

?>
