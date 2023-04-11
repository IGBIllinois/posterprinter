<?php


class poster {


	const THUMB_WIDTH = '600';
	const THUMB_HEIGHT = '600';
	const FULLSIZE_WIDTH = '1000';
	const FULLSIZE_HEIGHT = '1000';
	const TEMP_DIR = "tmp";

	public static function move_tmp_file($filename,$tmp_name) {
		functions::debug('move_tmp_file');
		//gets the file type (ie .jpg, .bmp) of the uploaded poster file.
		$filetype = self::get_filetype($filename);
		//creates a temp file name for the file
		$posterFileTmpName = "tmp_" . mt_rand(100000000,999999999) . "." . $filetype;
		//makes the path for the file
		if (!is_dir(self::get_tmp_path())) {
			mkdir (self::get_tmp_path(),0777);
		}
		$target_path = self::get_tmp_path() . "/" . $posterFileTmpName;
					//moves file to temporary location
		$result = 0;
		functions::debug("Tmp: " . $tmp_name . " Exists: " . file_exists($tmp_name));
		functions::debug("Uploaded File: " . is_uploaded_file($tmp_name));
		functions::debug("Target Path: " . $target_path);
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

	public static function rotate_dimensions($posterWidth,$posterLength,$paperTypeWidth) {


		//Switches around the poster width and length to make the length the shortest possible to save money.
		$rotated = 0;
		if (($posterWidth <= $paperTypeWidth) && ($posterLength <= $paperTypeWidth) && ($posterWidth < $posterLength)) {
			$rotated = 1;
		}
		elseif (($posterWidth > $paperTypeWidth) && ($posterLength <= $paperTypeWidth)) {
			$rotated = 1;
		}

		return $rotated;
	}

	public static function verify_dimensions($db,$width,$length) {
		$max_poster_width = self::get_max_poster_width($db);
		$message = "";
		$valid = true;
		if (($width == "") || ($width % 1 != 0) || ($width <= 0) || (!is_numeric($width))){
			$message = functions::alert("Please enter a valid poster width.",0);
			$valid = false;
					}
		elseif (($width > $max_poster_width) && ($length > $max_poster_width)) {
			$message = functions::alert("Width can't be greater than " . $max_poster_width . " inches",0);
			$valid = false;
		}

		if (($length == "") || ($length % 1 != 0) || ($length <=0) || (!is_numeric($length))){
			$message .= functions::alert("Please enter a valid poster length.",0);
			$valid = false;
		}
		elseif ($length > settings::get_max_length()) {
			$message .= functions::alert("Length can't be greater than " . settings::get_max_length() . " inches.",0);
			$valid = false;
		}

		return array('RESULT'=>$valid,'MESSAGE'=>$message);



	}
	public static function create_image($filename) {
		$full_path = self::get_tmp_path() . "/" . $filename;
		if (!file_exists($full_path)) {
			return array('RESULT'=>false);
		}

		$filetype = self::get_filetype($filename);

		switch ($filetype) {

			case 'pdf':
				$result = self::create_image_imagemagick($full_path);
				break;
			case 'jpg':
				$result = self::create_image_imagemagick($full_path);
				break;
			case 'jpeg':
				$result = self::create_image_imagemagick($full_path);
				break;
			case 'tif':
				$result = self::create_image_imagemagick($full_path);
				break;
			case 'tiff':
				$result = self::create_image_imagemagick($full_path);
				break;

			case 'ppt':
				$result = self::create_image_powerpoint($full_path);
				break;
			case 'pptx':
				$result = self::create_image_powerpoint($full_path);
				break;
			case 'ai':
				$result = self::create_image_imagemagick($full_path);
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
		if (self::create_imagemagick($filename,$thumb_path,self::THUMB_WIDTH,self::THUMB_HEIGHT) &&
			self::create_imagemagick($filename,$fullsize_path,self::FULLSIZE_WIDTH,self::FULLSIZE_HEIGHT)) {

			if (!file_exists($thumb_path)) {
				$thumb_path = "";
			}
			if (!file_exists($fullsize_path)) {
				$fullsize_path = "";
			}
			return array('RESULT'=>true,'FULL'=>basename($fullsize_path),'THUMB'=>basename($thumb_path));
		}
		return array('RESULT'=>false,'FULL'=>"",'THUMB'=>'');
	}
	public static function create_imagemagick($input,$output,$width,$height) {

		if (file_exists($output)) {
			unlink($output);
		}
		try {
			$image = new Imagick($input);
			$image->scaleImage($width,$height,true);
			$image->setImageFormat('jpeg');
			$image->writeImage($output);
			$image->clear();
			$image->destroy();
		} catch (Exception $e) {
			functions::debug("Error creating image: " . $e->getMessage(),1);
		}
		return true;
	}


	public static function create_image_powerpoint($filename) {
		$thumb_path = self::get_thumb_path($filename);
		$fullsize_path = self::get_fullsize_path($filename);
		$basename = basename($filename);
		$basename = strtolower(reset(explode(".",$basename)));
		$tmp_path = self::get_tmp_path() . "/" . $basename . ".jpg";
		if (file_exists($fullsize_path)) {
			unlink($fullsize_path);
		}
		if (file_exists($thumb_path)) {
			unlink($thumb_path);
		}
			try {
				$exec = "source /etc/profile && libreoffice --headless --convert-to jpg --outdir " . self::get_tmp_path() . " " . $filename;
				functions::debug($exec);
				$exit_status = 1;
				$output_array = array();
				$exec($exec,$output_array,$exit_status);
				if (($exit_status == 0) && file_exists($tmp_path)) {
					self::create_imagemagick($tmp_path,$thumb_path,self::THUMB_WIDTH,self::THUMB_HEIGHT);
					self::create_imagemagick($tmp_path,$fullsize_path,self::FULLSIZE_WIDTH,self::FULLSIZE_HEIGHT);
					unlink($tmp_path);
					if (!file_exists($thumb_path)) {
						$thumb_path = "";
					}
					if (!file_exists($fullsize_path)) {
						$fullsize_path = "";
					}
					return array('RESULT'=>true,'FULL'=>basename($fullsize_path),'THUMB'=>basename($thumb_path));
				}
			} catch (Exception $e) {
				functions::debug("Error converting powerpoint: " . $e->getMessage(),1);
			}
		
		return array('RESULT'=>false,'FULL'=>"",'THUMB'=>"");

	}

	public static function get_thumb_path($filename) {
		$basename = basename($filename);
		$basename = strtolower(reset(explode(".",$basename)));
		$thumb_filename = "thumb_" . $basename . ".jpg";
		$full_path = self::get_tmp_path() . "/" . $thumb_filename;
		return $full_path;

	}

	public static function get_fullsize_path($filename) {
		$basename = basename($filename);
		$basename = strtolower(reset(explode(".",$basename)));
		$thumb_filename = "fullsize_" . $basename . ".jpg";
		$full_path = self::get_tmp_path() . "/" . $thumb_filename;
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
		$sql = "SELECT MAX(paperTypes_width) as max_width FROM paperTypes ";
		$sql .= "WHERE paperTypes_available=1";
		$result = $db->query($sql);
		if (count($result)) {
			return $result[0]['max_width'];
		}
		return 0;


	}

	public static function get_tmp_path() {
		$tmp_path = self::get_root_path() . "/" . settings::get_poster_dir() . "/" . self::TEMP_DIR;
		return $tmp_path;
	}

	public static function move_poster($order_id,$posterFileName,$posterFileTmpName,$thumb_posterFileTmpName,$fullsize_posterFileTmpName) {
		$tmp_path = self::get_tmp_path();
		$final_poster_path = self::get_root_path() . "/" . settings::get_poster_dir() . "/" . $order_id;
	        $fileType = self::get_filetype($posterFileName);
		$filename = $order_id . "." . $fileType;
		$thumb_filename = "thumb_" . $order_id . ".jpg";
		$fullsize_filename = "fullsize_" . $order_id . ".jpg";

		mkdir($final_poster_path);

	        if (file_exists($tmp_path . "/" . $posterFileTmpName)) {
	                rename($tmp_path . "/" . $posterFileTmpName,$final_poster_path . "/" . $filename);
	        }
        	if (file_exists($tmp_path . "/" . $thumb_posterFileTmpName)) {
                	rename($tmp_path . "/" . $thumb_posterFileTmpName,$final_poster_path . "/" . $thumb_filename);
        	}
	        if (file_exists($tmp_path . "/" . $fullsize_posterFileTmpName)) {
        	        rename($tmp_path . "/" . $fullsize_posterFileTmpName,$final_poster_path . "/" . $fullsize_filename);
	        }			

	}
	private static function get_root_path() {
		$root_path = dirname(__DIR__);
		return $root_path;
	}
}

?>
