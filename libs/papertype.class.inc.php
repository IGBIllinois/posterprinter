<?php

class papertype {

	////////////////Private Variables//////////

        private $db; //mysql database object
        private $id;
	private $name;
	private $cost;
	private $width;
	private $available;
	private $default;
	private $time_created;

	////////////////Public Functions///////////

        public function __construct($db,$id = 0) {
                $this->db = $db;
                
		if ($id != 0) {
			$this->get_papertype($id);
		}
        }
        public function __destruct() { }

	public function create($name,$cost,$width,$default = 0) {
                $error = false;
                $message = "";
                if ($name == "") {
                        $message .= functions::alert("Please enter finish option name</b>",0);
                        $error = true;
                }
                if (!verify::verify_cost($cost)) {
                        $message .= functions::alert("Please enter a valid cost</b>",0);
                        $error = true;
                }

                if (($width == "") || ($width > settings::get_printer_max_width()) || !(preg_match("/^[0-9]{1,2}$/", $width))) {
                        $message .= functions::alert("Please enter a valid width.  The maximum is " . settings::get_printer_max_width(),0);
                        $error = true;
                }

                if ($error == 0) {
                        $available = 1;

	                if ($default) { 
				$this->remove_default_papertype();
                	}

			$insert_array['paperTypes_name'] = $name;
			$insert_array['paperTypes_cost'] = $cost;
			$insert_array['paperTypes_width'] = $width;
			$insert_array['paperTypes_available'] = $available;
			$insert_array['paperTypes_default'] = $default; 

	                $id = $this->db->build_insert("paperTypes",$insert_array);
        	        $message = "Paper Type successfully added";
                	return array('RESULT'=>TRUE,
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
	public function get_width() {
		return $this->width;
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
        //delet()
        //deletes paper type
	public function delete() {
		$sql = "UPDATE paperTypes SET paperTypes_available=0 WHERE paperTypes_id=:papertype_id LIMIT 1";
		$parameters = array(
			':papertype_id'=>$this->get_id()
		);
                return $this->db->non_select_query($sql,$parameters);


	}

	//update()
        //$name - string - name of paper type
        //$cost - decimal - cost of paper type
        //$width - integer - width of paper type
        //returns paper type id of new paper type.
        //this functions marks the current paper type as inactive then creates a new one
        //this is done to keep consistancy in the database for the previous orders. 
	public function update($name,$cost,$width) {
		$name = trim(rtrim($name));
		$cost = trim(rtrim($cost));
		$width = trim(rtrim($width));
		if (($this->get_name() == $name) && ($this->get_cost() == $cost) && ($this->get_width() == $width)) {
			$message = functions::alert("No fields were updated");
			return array('RESULT'=>false,'MESSAGE'=>$message);
		}
		else {
			$result = $this->create($name,$cost,$width,$this->get_default());
        	        $this->delete();
                	$message = "<br>Paper Type successfully updated.";
	                if ($result['RESULT']) {
        	                return array('RESULT'=>true,
                                        'MESSAGE'=>$message);
                	}
                	else { 
				return $result; 
			}
		}


	}

	public function set_default() {
		$this->remove_default_papertype();
		$sql = "UPDATE paperTypes SET paperTypes_default=1 WHERE paperTypes_id=:papertype_id";
		$parameters = array(
                        ':papertype_id'=>$this->get_id()
                );

                return $this->db->non_select_query($sql,$parameters);



	}
/////////////////Private Functions//////////////



	private function get_papertype($id) {

		$sql = "SELECT * FROM paperTypes WHERE paperTypes_id=:papertype_id LIMIT 1";
		$parameters = array(
                        ':papertype_id'=>$id
                );
		$result = $this->db->query($sql,$parameters);
		if (count($result)) {
			$this->id = $result[0]['paperTypes_id'];
			$this->name = $result[0]['paperTypes_name'];
			$this->cost = $result[0]['paperTypes_cost'];
			$this->width = $result[0]['paperTypes_width'];
			$this->available = $result[0]['paperTypes_available'];
			$this->default = $result[0]['paperTypes_default'];
			$this->time_created = $result[0]['paperTypes_timeCreated'];



		}

	}


	//remove_default_papertype()
        //removes the default flag for the paper type
        private  function remove_default_papertype() {
                $sql = "UPDATE paperTypes SET paperTypes_default=0";
                return $this->db->non_select_query($sql);

        }



}





?>
