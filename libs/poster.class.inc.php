<?php


class poster {


	const thumb_width = '800';
	const thumb_height = '800';
	const fullsize_width = '1000';
	const fullsize_height = '1000';

	public static function move_tmp_file($filename,$tmp_name) {

		//gets the file type (ie .jpg, .bmp) of the uploaded poster file.
		$filetype = self::get_filetype($filename);
		//creates a temp file name for the file
		$posterFileTmpName = "tmp_" . mt_rand(100000000,999999999) . "." . $filetype;
		//makes the path for the file
		$target_path = settings::get_poster_dir() . "/" . $posterFileTmpName;
	        //moves file to temporary location
		$result = 0;
		if (file_exists($tmp_name) && is_uploaded_file($tmp_name)) {
        		$result = move_uploaded_file($tmp_name,$target_path);
		}
		
		if ($result) {
			return $posterFileTmpName;
		}	
		return false;

	}


	public static function get_filetype($filename) {
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}

	public static function switch_dimensions($posterWidth,$posterLength,$paperTypeWidth) {


		//Switches around the poster width and length to make the length the shortest possible to save money.
		$widthSwitched = 0;
	        if (($posterWidth <= $paperTypeWidth) && ($posterLength <= $paperTypeWidth) && ($posterWidth < $posterLength)) {
        	        $widthSwitched = 1;
	        }
        	elseif (($posterWidth > $paperTypeWidth) && ($posterLength <= $paperTypeWidth)) {
                	$widthSwitched = 1;
	        }

		return $widthSwitched;
	}

	public static function verify_dimensions($db,$width,$length) {
		$max_poster_width = self::get_max_poster_width($db);
		$message = "";
		$valid = true;
	 	if (($width == "") || ($width % 1 != 0) || ($width <= 0)){
                	$message = "Please enter a valid poster width.";
                	$valid = false;
        	}
		elseif (($width > $max_poster_width) && ($length > $max_poster_width)) {
                	$message = "Width can't be greater than " . $max_poster_width . " inches";
			$valid = false;
	        }

        	if (($length == "") || ($length % 1 != 0) || ($length <=0)){
                	$message .= "Please enter a valid poster length.";
                	$valid = false;
        	}
		elseif ($length > settings::get_max_length()) {
                	$message .= "Length can't be greater than 200 inches.";
                	$valid = false;
        	}

                return array('RESULT'=>$valid,'MESSAGE'=>$message);



	}
	public static function create_image($filename) {
		if (!file_exists($filename)) {
			return array('RESULT'=>false);
		}

		$filetype = self::get_filetype($filename);

		switch ($filetype) {

			case 'pdf':
				$result = self::create_image_imagemagick($filename);
				break;
			case 'jpg':
				$result = self::create_image_imagemagick($filename);
				break;
                        case 'jpeg':
                                $result = self::create_image_imagemagick($filename);
                                break;
                        case 'tif':
                                $result = self::create_image_imagemagick($filename);
                                break;
                        case 'tiff':
                                $result = self::create_image_imagemagick($filename);
                                break;

			case 'ppt':
				$result = self::create_image_powerpoint($filename);
				break;
			case 'pptx':
				$result = self::create_image_powerpoint($filename);
				break;
			case 'ai':
				$result = self::create_image_imagemagick($filename);
                                break;

			default:
				$result = array('RESULT'=>false);
				break;	

		}
		return $result;
	}

	public static function create_image_imagemagick($filename) {
                $thumb_path = self::get_thumb_path($filename);
                $fullsize_path = self::get_fullsize_path($filename);
		if (self::create_imagemagick($filename,$thumb_path,self::thumb_width,self::thumb_height) && 
			self::create_imagemagick($filename,$fullsize_path,self::fullsize_width,self::fullsize_height)) {
			return array('RESULT'=>true,'FULL'=>basename($fullsize_path),'THUMB'=>basename($thumb_path));
		}
		return array('RESULT'=>false);
	}
	public static function create_imagemagick($input,$output,$width,$height) {
	
		if (file_exists($output)) {
			unlink($output);
		}
		$image = new Imagick($input);
		$image->scaleImage($width,$height,true);
		$image->setImageFormat('jpeg');
		$image->writeImage($output);
		$image->clear();
		$image->destroy();
		return true;
	}


	public static function create_image_powerpoint($filename) {
		$thumb_path = self::get_thumb_path($filename);
		$fullsize_path = self::get_fullsize_path($filename);
		$tmp_path = $fullsize_path . ".tmp";
		if (file_exists($fullsize_path)) {
			unlink($fullsize_path);
		}
                if (file_exists($thumb_path)) {
                        unlink($thumb_path);
                }

		if (settings::get_unoconv_exec()) {
			$exec = settings::get_unoconv_exec() . " -f jpg -o " . $tmp_path . " " . $filename;
			$exit_status = 1;
                        $output_array = array();
                        $output = exec($exec,$output_array,$exit_status);
			if (($exit_status == 0) && file_exists($tmp_path)) {
				self::create_imagemagick($tmp_path,$thumb_path,self::thumb_width,self::thumb_height);
				self::create_imagemagick($tmp_path,$fullsize_path,self::fullsize_wdith,self::fullsize_height);
				unlink($tmp_path);
				return array('RESULT'=>true,'FULL'=>basename($fullsize_path),'THUMB'=>basename($thumb_path));
			}	

		}
		return array('RESULT'=>false);		
	
	}

	public static function get_thumb_path($filename) {
                $basename = basename($filename);
		$basename = strtolower(reset(explode(".",$basename)));
                $thumb_filename = "thumb_" . $basename . ".jpg";
                $full_path = "/var/www/html/eclipse/posterprinter/" . settings::get_poster_dir() . "/" . $thumb_filename;
		return $full_path;

	}

        public static function get_fullsize_path($filename) {
                $basename = basename($filename);
                $basename = strtolower(reset(explode(".",$basename)));
                $thumb_filename = "fullsize_" . $basename . ".jpg";
                $full_path = "/var/www/html/eclipse/posterprinter/" . settings::get_poster_dir() . "/" . $thumb_filename;
                return $full_path;

        }

	public static function get_poster_size($filename) {


                if (!file_exists($filename)) {
                        return false;
                }

                $filetype = self::get_filetype($filename);


                switch ($filetype) {

                        case 'pdf':
                                $result = self::get_imagemagick_size($filename);
                                break;
                        case 'jpg':
                                $result = self::get_imagemagick_size($filename);
                                break;
                        case 'jpeg':
                                $result = self::get_imagemagick_size($filename);
                                break;
                        case 'tif':
                                $result = self::get_imagemagick_size($filename);
                                break;
                        case 'tiff':
                                $result = self::get_imagemagick_size($filename);
                                break;

                        case 'ppt':
                                $result = self::get_powerpoint_size($filename);
                                break;
                        case 'pptx':
                                $result = self::get_powerpoint_size($filename);
                                break;
                        case 'ai':
                                $result = self::get_powerpoint_size($filename);
                                break;

                        default:
                                $result = false;
                                break;

                }
                return $result;
        }




	public static function get_imagemagick_size($filename) {
		$image = new Imagick($filename);
		$dimensions = $image->getImageGeometry();
		$resolution = $image->getImageResolution();
		$inches = array();
                if (!empty($resolution['y'])) {
                        $inches['height'] = round($dimensions['height'] / $resolution['y'],2);

                }
		if (!empty($resolution['x'])) {
			$inches['width'] = round($dimensions['width'] / $resolution['x'],2);
		}
                return $inches;
	}

	public static function get_powerpoint_size($filename) {
		$inches = array('height'=>0,'width'=>0);
		return $inches;


	}


	public static function get_max_poster_width($db) {
		$sql = "SELECT MAX(paperTypes_width) as max_width FROM tbl_paperTypes ";
		$sql .= "WHERE paperTypes_available=1";
		$result = $db->query($sql);
		if (count($result)) {
			return $result[0]['max_width'];
		}
		return 0;


	}


}

?>
