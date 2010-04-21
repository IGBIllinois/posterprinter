<?php
include_once 'db.class.inc.php';

//getPaperTypes()
//$db - database object
//returns array of all enabled paper types
function getPaperTypes($db) {
	$sql = "SELECT * FROM tbl_paperTypes ";
	$sql .= "WHERE paperTypes_available=1 ";
	$sql .= "ORDER BY paperTypes_name ASC";
	return $db->query($sql);

}

//getValidPaperTypes()
//$db - database object
//$width - integer - width in inches
//$length - integer - length in inches
//returns array of paper types that fit the given dimensions
function getValidPaperTypes($db,$width,$length) {
	
	$sql = "SELECT * FROM tbl_paperTypes ";
	$sql .= "WHERE paperTypes_available=1 ";
	$sql .= "AND (paperTypes_width>='" . $width  . "' OR paperTypes_width>='" . $length . "') ";
	$sql .= "ORDER BY paperTypes_name ASC";;
	return $db->query($sql);

}

//addPaperType()
//$db - database object
//$name - string - name of paper type
//$cost - decimal - cost of paper type
//$width - integer - width of paper type in inches
//$default - boolean - specifies if it will be the default selected paper type
function addPaperType($db,$name,$cost,$width,$default = 0) {

	$available = 1;	
        if ($default == 1) {
        	removeDefaultPaperType($db);
	}
	else { $default = 0; }
	$sql = "INSERT INTO tbl_paperTypes(paperTypes_name,paperTypes_cost,paperTypes_width,paperTypes_available,paperTypes_default) ";
	$sql .= "VALUES('" . $name . "','" . $cost . "','" . $width . "','" . $available . "','" . $default . "')";
        return $db->insert_query($sql);
}

//setDefaultPaperType()
//$db - database object
//$paperTypeId - integer - paper type id
//sets paper type to be default selected paper type
function setDefaultPaperType($db,$paperTypeId) {
	removeDefaultPaperType($db);
	$sql = "UPDATE tbl_paperTypes SET paperTypes_default=1 WHERE paperTypes_id='" . $paperTypeId . "'";
	$db->non_select_query($sql);

}

//updatePaperType()
//$db - database object
//$paperTypeId - integer - paper type id
//$name - string - name of paper type
//$cost - decimal - cost of paper type
//$width - integer - width of paper type
//$default - boolean - specifies if paper type will be default
//returns paper type id of new paper type.
//this functions marks the current paper type as inactive then creates a new one
//this is done to keep consistancy in the database for the previous orders. 
function updatePaperType($db,$paperTypeId,$name,$cost,$width,$default) {
	deletePaperType($db,$paperTypeId);
	return addPaperType($db,$name,$cost,$width,$default);
}

//deletePaperType()
//$db - database object
//$paperTypeId - integer - paper type id
//deletes paper type
function deletePaperType($db,$paperTypeId) {
	$sql = "UPDATE tbl_paperTypes SET paperTypes_available=0 WHERE paperTypes_id=$paperTypeId";
	$db->non_select_query($sql);

}

//getPaperType()
//$db - databae object
//$paperTypeId - integer - paper type id
//returns array of selected paper type information
function getPaperType($db,$paperTypeId) {
	$sql = "SELECT * FROM tbl_paperTypes WHERE paperTypes_id='" . $paperTypeId . "' LIMIT 1";
	return $db->query($sql);
	
}

//removeDefaultPaperType()
//$db - database object
//removes the default flag for the paper type
function removeDefaultPaperType($db) {
	$sql = "UPDATE tbl_paperTypes SET paperTypes_default=0";
	$db->non_select_query($sql);

}

?>