<?php


class functions {

	//Possible errors when you upload a file
        private static $upload_errors = array(
                1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                3 => 'The uploaded file was only partially uploaded.',
                4 => 'No file was uploaded.',
                6 => 'Missing a temporary folder.',
                7 => 'Failed to write file to disk.',
                8 => 'File upload stopped by extension.'
        );

        public static function get_upload_error($value) {
                return self::$upload_errors[$value];

        }

	public static function authenticate($username,$password,$ldaphost,$base_dn,$people_ou,$group_ou,$group,$ssl,$port) {

	        $connect;
        	if ($ssl == 1) { $connect = ldap_connect("ldaps://" . $ldaphost,$port); }
	        elseif ($ssl == 0) { $connect = ldap_connect("ldap://" . $ldaphost,$port); }
        	$bindDN = "uid=" . $username . "," . $people_ou;
	        $bind_success = @ldap_bind($connect, $bindDN, $password);
        	$success = 0;
	        if ($bind_success) {
        	        $filter = "(&(cn=" . $group . ")(memberUid=" . $username . "))";
                	$search = ldap_search($connect,$group_ou,$filter);
	                $result = ldap_get_entries($connect,$search);
        	        if ($result["count"]) { $success = 1; }
	        }
        	ldap_unbind($connect);
	        return $success;
	}

	public static function alert($message, $success = 1) {
		$alert = "";
		if ($success) {
			$alert = "<div class='alert alert-success' role='alert'>" . $message . "</div>";

		}
		else {
			$alert = "<div class='alert alert-danger' role='alert'>" . $message . "</div>";
		}
		return $alert;

	}



	public static function create_order($db,$order_info) {

		$data = array('orders_email'=>$order_info['email'],
			'orders_cc_emails'=>$order_info['additional_emails'],
			'orders_fileName'=>$order_info['posterFileName'],
			'orders_totalCost'=>$order_info['totalCost'],
			'orders_cfop'=>$order_info['cfop'],
			'orders_activityCode'=>$order_info['activityCode'],
			'orders_width'=>$order_info['posterWidth'],
			'orders_length'=>$order_info['posterLength'],
			'orders_status'=>'NEW',
			'orders_paperTypesId'=>$order_info['paperTypesId'],
			'orders_finishOptionsId'=>$order_info['finishOptionsId'],
			'orders_comments'=>$order_info['comments'],
			'orders_posterTubeId'=>$order_info['posterTubeId'],
			'orders_rushOrderId'=> $order_info['rushOrderId'],
			'orders_widthSwitched'=>$order_info['widthSwitched'],
			'orders_name'=>$order_info['name']
			);
		return $db->build_insert('orders',$data);

	}


	//getPreviousOrders()
	//$db - database object
	//$month - integer - month of the year
	//$year - intenger - year
	//returns array of previous orders for given month and year.
	public static function getPreviousOrders($db,$month,$year) {

        	$sql = "SELECT orders.*, paperTypes.*,finishOptions.* ";
	        $sql .= "FROM orders ";
        	$sql .= "LEFT JOIN paperTypes ON orders.orders_paperTypesId=paperTypes.paperTypes_id ";
	        $sql .= "LEFT JOIN finishOptions ON orders.orders_finishOptionsId=finishOptions.finishOptions_id ";
        	$sql .= "WHERE (YEAR(orders_timeCreated)='" . $year ."' AND month(orders_timeCreated)='" . $month ."') ";
	        $sql .= "AND (orders.orders_status='Completed' OR orders.orders_status='Cancel') ";
        	$sql .= "ORDER BY orders_id ASC";
	        return $db->query($sql);
	}

	//getCurrentOrders()
	//$db - database object
	//returns array of all the current orders.
	public static function getCurrentOrders($db) {

        	$sql = "SELECT orders.*, rushOrder.* ";
	        $sql .= "FROM orders ";
        	$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
	        $sql .= "WHERE NOT (orders.orders_status='Completed' OR orders.orders_status='Cancel') ";
        	$sql .= "ORDER BY orders_id ASC";
	        return $db->query($sql);


	}

	//getOrdersReport()
	//$db - database object
	//$month - integer - month of the year
	//$year - integer - year
	//returns array of previous orders for given month and year with array keys having pretty names
	//this is ment to pass to the report functions to build monthly reports
	public static function getOrdersReport($db,$month,$year) {

        	$sql = "SELECT orders.orders_id as 'Order Number', orders.orders_email as 'Email', ";
	        $sql .= "orders.orders_name as 'Full Name', orders.orders_timeCreated as 'Date', ";
        	$sql .= "orders.orders_cfop as 'CFOP', orders.orders_activityCode as 'Activity Code', ";
	        $sql .= "orders.orders_totalCost as 'Cost', ";
        	$sql .= "paperTypes.paperTypes_name as 'Paper Type', paperTypes.paperTypes_cost as 'Paper Type Cost (per Inch)', ";
	        $sql .= "finishOptions.finishOptions_name as 'Finish Option', finishOptions.finishOptions_cost as 'Finish Option Cost', ";
        	$sql .= "rushOrder.rushOrder_name as 'Rush Order', rushOrder.rushOrder_cost as 'Rush Order Cost', ";
	        $sql .= "posterTube.posterTube_name as 'Poster Tube', posterTube.posterTube_cost as 'Poster Tube Cost' ";
        	$sql .= "FROM orders ";
	        $sql .= "LEFT JOIN paperTypes ON orders.orders_paperTypesId=paperTypes.paperTypes_id ";
        	$sql .= "LEFT JOIN finishOptions ON orders.orders_finishOptionsId=finishOptions.finishOptions_id ";
	        $sql .= "LEFT JOIN posterTube ON orders.orders_posterTubeId=posterTube.posterTube_id ";
        	$sql .= "LEFT JOIN rushOrder ON orders.orders_rushOrderId=rushOrder.rushOrder_id ";
	        $sql .= "WHERE (YEAR(orders_timeCreated)='" . $year . "' AND month(orders_timeCreated)='" . $month . "') ";
        	$sql .= "AND orders.orders_status='Completed' ";
	        $sql .= "ORDER BY orders_id ASC";
        	return $db->query($sql);
	}


}







?>
