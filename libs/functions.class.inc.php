<?php


class functions {

	const BYTES_TO_MEGABYTES = 1048576;

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

	public static function alert($message, $success = 1) {
		$alert = "";
		if ($success) {
			$alert = "<div class='alert alert-success' role='alert'>" . $message . "</div>&nbsp;";

		}
		else {
			$alert = "<div class='alert alert-danger' role='alert'>" . $message . "</div>&nbsp;";
		}
		return $alert;

	}



	public static function create_order($db,$order_info) {
		$key = self::generate_key();	
		$data = array('orders_email'=>$order_info['email'],
			'orders_cc_emails'=>$order_info['additional_emails'],
			'orders_fileName'=>$order_info['posterFileName'],
			'orders_totalCost'=>$order_info['totalCost'],
			'orders_cfop'=>$order_info['cfop'],
			'orders_activityCode'=>$order_info['activityCode'],
			'orders_width'=>$order_info['width'],
			'orders_length'=>$order_info['length'],
			'orders_status'=>'NEW',
			'orders_paperTypesId'=>$order_info['paperTypesId'],
			'orders_finishOptionsId'=>$order_info['finishOptionsId'],
			'orders_comments'=>$order_info['comments'],
			'orders_posterTubeId'=>$order_info['posterTubeId'],
			'orders_rushOrderId'=> $order_info['rushOrderId'],
			'orders_rotated'=>$order_info['rotated'],
			'orders_name'=>$order_info['name'],
			'orders_key'=>$key
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
        	$sql .= "WHERE (YEAR(orders_timeCreated)=:year AND month(orders_timeCreated)=:month) ";
	        $sql .= "AND (orders.orders_status='Completed' OR orders.orders_status='Cancel') ";
		$sql .= "ORDER BY orders_id ASC";
		$parameters = array(
			':month'=>$month,
			':year'=>$year
		);
	        return $db->query($sql,$parameters);
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
	        $sql .= "WHERE (YEAR(orders_timeCreated)=:year AND month(orders_timeCreated)=:month) ";
        	$sql .= "AND orders.orders_status='Completed' ";
		$sql .= "ORDER BY orders_id ASC";
		$parameters = array(
			':month'=>$month,
			':year'=>$year
		);
        	return $db->query($sql,$parameters);
	}

        public static function get_boa_report($db,$month,$year) {

                $sql = "SELECT '' as 'DATE', ";
		$sql .= "orders.orders_email as 'NAME', ";
                $sql .= "orders.orders_cfop as 'CFOP', ";
		$sql .= "orders.orders_activityCode as 'ACTIVITY CODE', ";
                $sql .= "orders.orders_totalCost as 'COST' ";
                $sql .= "FROM orders ";
                $sql .= "WHERE (YEAR(orders_timeCreated)=:year AND month(orders_timeCreated)=:month) ";
                $sql .= "AND orders.orders_status='Completed' ";
		$sql .= "ORDER BY `CFOP` ASC, `ACTIVITY CODE` ASC";
		$parameters = array(
                        ':month'=>$month,
                        ':year'=>$year
                );

		$result = $db->query($sql,$parameters);

		$total_bill = 0;
		foreach ($result as $num => $values) {
			$total_bill += $values['COST'];
		}			
		$first_row = array(array('DATE'=>$month . "/" . $year,
					'NAME'=>'IGB Posterprinter Report',
					'CFOP'=>settings::get_boa_cfop(),
					'ACTIVITY CODE'=>settings::get_boa_activity_code(),
					'COST'=>"-" . $total_bill));

                return array_merge($first_row,$result);
        }

	//getPaperTypes()
        //$db - database object
        //returns array of all enabled paper types
        public static function getPaperTypes($db) {
                $sql = "SELECT paperTypes_id as id, paperTypes_name as name, ";
                $sql .= "paperTypes_cost as cost, paperTypes_width as width, ";
                $sql .= "paperTypes_default ";
                $sql .= "FROM paperTypes ";
                $sql .= "WHERE paperTypes_available=1 ";
                $sql .= "ORDER BY paperTypes_name ASC";
                return $db->query($sql);

        }

        //getValidPaperTypes()
        //$db - database object
        //$width - integer - width in inches
        //$length - integer - length in inches
        //returns array of paper types that fit the given dimensions
        public static function getValidPaperTypes($db,$width,$length) {

                $sql = "SELECT paperTypes_id as id, paperTypes_name as name, ";
                $sql .= "paperTypes_cost as cost, paperTypes_width as width, ";
                $sql .= "paperTypes_default ";
                $sql .= "FROM paperTypes ";
                $sql .= "WHERE paperTypes_available='1' ";
		$sql .= "AND (paperTypes_width>=:width OR paperTypes_width>=:length) ";
		$sql .= "ORDER BY paperTypes_cost ASC";
		$parameters = array(
			':width'=>$width,
			':length'=>$length
		);
                return $db->query($sql,$parameters);

        }

        //getFinishOptions()
        //$db - database object
        //returns array of all the enabled finish options.
        public static function getFinishOptions($db) {

                $sql = "SELECT finishOptions_id as id, finishOptions_name as name, ";
                $sql .= "finishOptions_cost as cost, finishOptions_maxWidth as maxWidth, ";
                $sql .= "finishOptions_maxLength as maxLength, finishOptions_default ";
                $sql .= "FROM finishOptions ";
                $sql .= "WHERE finishOptions_available=1 ";
                $sql .= "ORDER BY finishOptions_name ASC";
                return $db->query($sql);
        }

        //getValidFinishOptions()
        //$db - database object
        //$width - integer - width in inches
        //$length - intenger - length in inches
        //returns array of finish options that can be used on the poster based on the width and length.
        public static function getValidFinishOptions($db,$width,$length) {

                $sql = "SELECT finishOptions_id as id, finishOptions_name as name, ";
                $sql .= "finishOptions_cost as cost, finishOptions_maxWidth as maxWidth, ";
                $sql .= "finishOptions_maxLength as maxLength, finishOptions_default ";
                $sql .= "FROM finishOptions ";
                $sql .= "WHERE finishOptions_available='1' ";
                $sql .= "AND finishOptions_maxLength>=:length ";
                $sql .= "AND (finishOptions_maxWidth>=:width OR finishOptions_maxWidth>=:length) ";
		$sql .= "ORDER BY finishOptions_name ASC";
		$parameters = array(
                        ':width'=>$width,
                        ':length'=>$length
                );
                return $db->query($sql,$parameters);

        }

	public static function convert_bytes_to_megabytes($bytes) {
		return round($bytes/self::BYTES_TO_MEGABYTES,2);


	}

	public static function generate_key() {
		$key = uniqid (rand (),true);
		$hash = sha1($key);
		return $hash;

	}

	public static function debug($message,$log_level = 0) {
		
                if (settings::get_debug()) {
			switch ($log_level) {
				case 0:
					error_log("INFO: " . $message);
					break;
				case 1:
					error_log("ERROR: " . $message);
					break;
				default:
					error_log("INFO: " . $message);
					break;
			}

                }
        }

	public static function get_referral_url() {
		$pos = strpos($_SERVER['HTTP_REFERER'],"?");
		$url = $_SERVER['HTTP_REFERER'];
		if ($pos) {
			$url = substr($_SERVER['HTTP_REFERER'],0,$pos);
		}
		return $url;
	}

	public static function get_current_url_dir() {
		$dir_name = dirname($_SERVER['PHP_SELF']);
		$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'] . $dir_name . "/";
		return $url;


	}

	public static function get_minimal_year($db) {
		$sql = "SELECT MIN(YEAR(orders_timeCreated)) as year ";
		$sql .= "FROM orders ";
		$result = $db->query($sql);
		if (count($result)) {
			return $result[0]['year'];

		}
		return date("Y");



	}
}







?>
