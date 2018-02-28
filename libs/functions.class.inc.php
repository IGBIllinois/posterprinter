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
}







?>
