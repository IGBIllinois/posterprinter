<?php

include_once 'db.class.inc.php';
include_once 'orders.inc.php';

class order {

////////////////Private Variables//////////

	private $db; //mysql database object
	private $id;
	private $email; 
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
	private $status_id;

////////////////Public Functions///////////

	public function __construct($db,$order_id) {
		$this->db = $db;
		$this->id = $order_id;
		$this->get_order();
	}
	public function __destruct() { }
	public function get_order_id() { return $this->id; }
	public function get_email() { return $this->email; }
	public function get_name() { return $this->name; }
	public function get_filename() { return $this->filename; }
	public function get_filetype() { return end(explode(".",$this->get_filename())); }
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
	public function get_status_id() { return $this->status_id; }
	
	public function set_status($status_id) {
	
		$time_finished = date( 'Y-m-d H:i:s');
		$sql = "UPDATE tbl_orders ";
		$sql .= "SET orders_statusId='" . $status_id . "', ";
		$sql .= "orders_timeFinished='" . $time_finished . "' ";
		$sql .= "WHERE orders_id='" . $this->get_order_id() . "' LIMIT 1";
		$this->db->non_select_query($sql);
		$this->time_finished = $time_finished;
		$this->status_id = $status_id;
		$this->status_name = getStatusName($this->db,$status_id);
		
	}
	/////////////////Private Functions///////////
	
	private function get_order() {
	
		$sql = "SELECT tbl_orders.*, tbl_status.*,tbl_paperTypes.*,tbl_finishOptions.*,tbl_posterTube.*,tbl_rushOrder.* FROM tbl_orders ";
		$sql .= "LEFT JOIN tbl_status ON tbl_orders.orders_statusId=tbl_status.status_id ";
		$sql .= "LEFT JOIN tbl_paperTypes ON tbl_orders.orders_paperTypesId=tbl_paperTypes.paperTypes_id ";
		$sql .= "LEFT JOIN tbl_finishOptions ON tbl_orders.orders_finishOptionsId=tbl_finishOptions.finishOptions_id ";
		$sql .= "LEFT JOIN tbl_posterTube ON tbl_orders.orders_posterTubeId=tbl_posterTube.posterTube_id ";
		$sql .= "LEFT JOIN tbl_rushOrder ON tbl_orders.orders_rushOrderId=tbl_rushOrder.rushOrder_id ";
		$sql .= "WHERE orders_id='" . $this->get_order_id() . "'";
		$result = $this->db->query($sql);
		$this->email = $result[0]["orders_email"];
		$this->name = $result[0]["orders_name"];
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
		$this->status = $result[0]["status_name"];
		$this->status_id = $result[0]["status_id"];
        	
	}


}

?>
