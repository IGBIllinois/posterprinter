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


}



?>
