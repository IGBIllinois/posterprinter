<?php

class html {


	public static function get_filetypes_table($columns = 1) {

		$filetypes = settings::get_valid_filetypes();

		$filetypes_html = "";
		foreach ($filetypes as $filetype) {
			$filetypes_html .= "<tr>";
			$filetypes_html .= "<td>" . $filetype . "</td>\n";
			$filetypes_html .= "</tr>";
		}

		return $filetypes_html;
	}

        public static function get_url_navigation_month($url,$year,$month,$get_array = array()) {
                $current_date = DateTime::createFromFormat('Y-m-d H:i:s',$year . "-" . $month . "-01 00:00:00");

                $next_date = clone $current_date;
                $next_date->modify('first day of next month');
                $next_get_array = array_merge(array('year'=>$next_date->format('Y'),'month'=>$next_date->format('m')),$get_array);
                $forward_url = $url . "?" . http_build_query($next_get_array);

                $previous_date = clone $current_date;
                $previous_date->modify('first day of previous month');
                $previous_get_array = array_merge(array('year'=>$previous_date->format('Y'),'month'=>$previous_date->format('m')),$get_array);
                $back_url = $url . "?" . http_build_query($previous_get_array);

                return array('back_url'=>$back_url,'forward_url'=>$forward_url);

        }
        public static function get_url_navigation_year($url,$year,$get_array = array()) {
                $current_date = DateTime::createFromFormat('Y-m-d H:i:s',$year . "-01-01 00:00:00");

                $next_date = clone $current_date;
                $next_date->modify('first day of next year');
                $next_get_array = array_merge(array('year'=>$next_date->format('Y')),$get_array);
                $forward_url = $url . "?" . http_build_query($next_get_array);

                $previous_date = clone $current_date;
                $previous_date->modify('first day of previous year');
                $previous_get_array = array_merge(array('year'=>$previous_date->format('Y')),$get_array);
                $back_url = $url . "?" . http_build_query($previous_get_array);

                return array('back_url'=>$back_url,'forward_url'=>$forward_url);

        }

}



?>
