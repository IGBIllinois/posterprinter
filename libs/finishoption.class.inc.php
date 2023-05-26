<?php

class finishoption {


////////////////Private Variables//////////

        private $db; //mysql database object
        private $id;
        private $name;
        private $cost;
        private $max_width;
	private $max_length;
        private $available;
        private $default;
	private $time_creatd;

////////////////Public Functions///////////

        public function __construct($db,$id = 0) {
                $this->db = $db;
		if ($id != 0) {
	                $this->get_finishoption($id);
		}
        }
        public function __destruct() { }

	public function create($name,$cost,$max_width,$max_length,$default = 0) {

		$errors = 0;
                $message = "";
                if ($name == "") {
                        $message .= functions::alert("Pleae enter finish option name",0);
                        $errors++;
                }
                if (!verify::verify_cost($cost)) {
                        $message .= functions::alert("Please enter a valid cost",0);
                        $errors++;
                }

                if (($max_width == "") || ($max_width > settings::get_printer_max_width()) || !(preg_match("/^[0-9]{1,2}$/", $max_width))) {
                        $message .= functions::alert("Please enter a valid Max Width. Maximum is " . settings::get_printer_max_width() . " inches",0);
                        $errors++;
                }
                if (($max_length == "") || !(preg_match("/^[0-9]{1,3}$/", $max_length))) {
                        $message .= functions::alert("Please enter a valid Max Length",0);
                        $errors++;
                }

                if ($errors == 0) {
                        $available = 1;
                        if ($default) {
				$this->remove_default_finishoption();
                        }
			
			$insert_array['finishOptions_name'] = $name;
			$insert_array['finishOptions_cost'] = $cost;
			$insert_array['finishOptions_maxWidth'] = $max_width;
			$insert_array['finishOptions_maxLength'] = $max_length;
			$insert_array['finishOptions_available'] = $available;
			$insert_array['finishOptions_default'] = $default;

                        $id = $this->db->build_insert("finishOptions",$insert_array);
                        $message = "<br>Finish Option successfully added.";
                        return array ('RESULT'=>TRUE,
                                        'ID'=>$id,
                                        'MESSAGE'=>$message);

                }
                else {
                        return array('RESULT'=>FALSE,
                                        'MESSAGE'=>$message);

                }

	}
	public function get_id() {
		return $this->id;
	}

	public function get_name() {
                return $this->name;
        }
        public function get_cost() {
                return $this->cost;
        }
        public function get_max_width() {
                return $this->max_width;
        }
	public function get_max_length() {
		return $this->max_length;
	}
        public function get_available() {
                return $this->available;
        }
        public function get_default() {
                return $this->default;
        }
	public function get_time_created() {
		return $this->time_created;
	}

	//delete()
        //returns true on success of deletion of finish option
	public function delete() {
		$sql = "UPDATE finishOptions SET finishOptions_available=0 WHERE finishOptions_id=:finishoption_id LIMIT 1";
		$parameters = array(
			':finishoption_id'=>$this->get_id()
		);
                return $this->db->non_select_query($sql,$parameters);

	}

        //update()
        //$name - string - name of finish option
        //$cost - decimal - cost of the finish option
        //$maxWidth - integer - maximum width of the finish option in inches
        //$maxLength - integer - maximum length of the finish option in inches
        //returns id of the updated finish option.
        //this function actually deletes the finish option then creates a new one.  If we really just
        //updated the finish option then calculating the cost for previous orders will be inconsistant.
        public function update($name,$cost,$maxWidth,$maxLength) {
		$name = trim(rtrim($name));
		$cost = trim(rtrim($cost));
		$maxWidth = trim(rtrim($maxWidth));
		$maxLength = trim(rtrim($maxLength));
		if (($this->get_name() == $name) && ($this->get_cost() == $cost) 
				&& ($this->get_max_width() == $maxWidth) && ($this->get_max_length() == $maxLength)) {
			$message = functions::alert("No fields were updated");
			return array('RESULT'=>false,'MESSAGE'=>$message);

		}
		else {
	                $result = $this->create($name,$cost,$maxWidth,$maxLength,$this->get_default());
        	        $this->delete();
                	$message = "<br>Finish Option successfully updated.";
	                if ($result['RESULT']) {
				$this->delete();
                	        return array('RESULT'=>true,
                                        'MESSAGE'=>$message);
	                }
        	        else { return $result; }
		}
        }


	//set_default()
        //returns true on success of making the finish option the default
        public function set_default() {

                $this->remove_default_finishoption();
		$sql =  "UPDATE finishOptions SET finishOptions_default=1 WHERE finishOptions_id=:finishoptions_id LIMIT 1";
		$parameters = array(
                        ':finishoption_id'=>$this->get_id()
                );
                return $this->db->non_select_query($sql,$parameters);

        }


///////////////////Private Functions//////////////


	private function get_finishoption($id) {
		$sql = "SELECT * FROM finishOptions WHERE finishOptions_id=:finishoptions_id LIMIT 1";
		$parameters = array(	
			':finishoptions_id'=>$id
		);
		$result = $this->db->query($sql,$parameters);
		if (count($result)) {
			$this->id = $result[0]['finishOptions_id'];
			$this->name = $result[0]['finishOptions_name'];
			$this->cost = $result[0]['finishOptions_cost'];
			$this->max_width = $result[0]['finishOptions_maxWidth'];
			$this->max_length = $result[0]['finishOptions_maxLength'];
			$this->available = $result[0]['finishOptions_available'];
			$this->default = $result[0]['finishOptions_default'];
			$this->time_created = $result[0]['finishOptions_timeCreated'];

		}

	}


	//remove_default_finishoption()
        //removes the default finish option.  This is a helper function.
        private function remove_default_finishoption() {
		$sql = "UPDATE finishOptions SET finishOptions_default=0";
                return $this->db->non_select_query($sql);
        }

}
?>
