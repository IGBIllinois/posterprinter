<?php


class verify {


	public static function verify_cfop($cfop) {
                if (preg_match('^[1-9]{1}-[0-9]{6}-[0-9]{6}-[0-9]{6}$^',$cfop)) {
                        return true;
                }
                return false;
        }

        public static function verify_activity_code($activity) {
                if ((strlen($activity) == 0) || (preg_match('^[a-zA-Z0-9]^',$activity)
                                && (strlen($activity) <= 6))) {
                        return true;
                }
                return false;
        }


        public static function verify_email($email) {
                $email = strtolower($email);
                $hostname = "";
                if (strpos($email,"@")) {
                        list($prefix,$hostname) = explode("@",$email);
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

	public static function verify_cost($cost) {
		$errors = false;
		$valid = false;
		if ($cost == "") {
			$errors = true;
		}
		if (!preg_match('/\$(\d+\.\d+)/',$cost)) {
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
}

?>
