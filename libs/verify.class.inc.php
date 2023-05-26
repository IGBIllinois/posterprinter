<?php


class verify {


        public static function verify_email($email) {
		$email = strtolower($email);
                $hostname = "";
                if (strpos($email,"@")) {
                        list($username,$hostname) = explode("@",$email);
                }

                $valid = 1;
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $valid = 0;
                }
                elseif (($hostname != "") && (!checkdnsrr($hostname,"ANY"))) {
                        $valid = 0;
                }
                return $valid;

        }

	public static function verify_cc_emails($cc_emails) {
		$valid = 1;
		if (strlen($cc_emails)) {
			$email_array=explode(",",$cc_emails);
			if (count($email_array)) {
				foreach ($email_array as $email) {
					if (!self::verify_email($email)) {
						$valid = 0;
					}
				}
			}
		}
	
		return $valid;	


	}
	public static function verify_name($name) {
		$name = trim(rtrim($name));
		if ($name == "") {
			return false;
		}
		if (count(explode(" ",$name)) != 2) {
			return false;
		}
		if (!preg_match("/^[a-zA-Z ]+$/",$name)) {
			return false;
		}
		return true;


	}
	public static function verify_cost($cost) {
		$errors = false;
		$valid = false;
		if ($cost == "") {
			$errors = true;
		}
		if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/',$cost)) {
			$errors = true;

		}

		if (!$errors) {
			$valid = true;
        	}
		return $valid;
	}


	public static function verify_filetype($filename) {
		$filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$valid_filetypes = settings::get_valid_filetypes();
		if (in_array($filetype,$valid_filetypes)) {
			return true;
		}
		return false;

	}

	public static function verify_poster_size($filename,$submitted_width,$submitted_length) {
		$poster_size = poster::get_poster_size($filename);
		$poster_width = $poster_size['width'];
		$poster_length = $poster_size['length'];
		error_log('width: ' . $poster_width . " length: " . $poster_length);
		$valid= true;
		if (($poster_width < $submitted_width - 1) || ($poster_width > $submitted_width)) {
			$valid = false;
		}
		elseif (($poster_length < $submitted_length -1) || ($poster_width > $submitted_width)) {
			$valid = false;

		}
		$result = array('valid'=>$valid,
			'width'=>$poster_width,
			'length'=>$poster_length
		);
		return $result;

	}
}

?>
