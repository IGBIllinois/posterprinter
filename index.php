<?php
//////////////////////////////////////////////////////
//
//	Poster Printer Order Submission
//	index.php
//
//	Page to allow the user to submit poster files
//
//	David Slater
//	April 2007
//
//////////////////////////////////////////////////////

//include files for the script to run
require_once 'includes/settings.inc.php';
set_include_path(get_include_path() . ':libs');
require_once 'db.class.inc.php';
require_once 'mail.inc.php';
require_once 'orders.inc.php';
require_once 'paperTypes.inc.php';
require_once 'finishOptions.inc.php';
require_once 'posterTube.inc.php';
require_once 'rushOrder.inc.php';

//connects to database
$db = new db(mysql_host,mysql_database,mysql_user,mysql_password);

//poster width and length submission
if (isset($_POST['step1'])) {

	$posterWidth = $_POST['posterWidth'];
	$posterLength = $_POST['posterLength'];
	$posterWidth = trim(rtrim($posterWidth));
	$posterLength = trim(rtrim($posterLength));


	$paperTypes = getValidPaperTypes($db,$posterWidth,$posterLength);
	//takes the result and formats it into html into the paperTypeHTML variable.
	$paperTypes_html = "";
	foreach ($paperTypes as $paperType) {
		$paperTypes_html .= "<tr>";
		$paperTypes_html .= "<td class='right'>$" . $paperType['cost'] . "</td>";
		$paperTypes_html .= "<td class='center'>" .  $paperType['name'] . "</td>";
		if (($paperType['paperTypes_default'] == 1) || (count($paperTypes) == 1)) {
			$paperTypes_html .= "<td class='left'><input type='radio' name='paperTypesId' checked='true' value='" . $paperType['id'] . "'></td></tr>";
		}
		else {
			$paperTypes_html .= "<td class='left'><input type='radio' name='paperTypesId' value='" . $paperType['id'] . "'></td></tr>";
		}

	}


	$finishOptions = getValidFinishOptions($db,$posterWidth,$posterLength);
	//takes the result and formats it into html into the finishOptionsHTML variable.
	$finishOptions_html = "";
	foreach ($finishOptions as $finishOption) {
		$finishOptions_html .= "<tr>";
		$finishOptions_html .= "<td class='right'>$" . $finishOption['cost'] . "</td>";
		$finishOptions_html .= "<td class='center'>" . $finishOption['name'] . "</td>";
		if (($finishOption['finishOptions_default'] == 1) || (count($finishOptions))) {
			$finishOptions_html .= "<td class='left'> <input type='radio' name='finishOptionsId' checked='true' value='" . $finishOption['id'] . "'></td></tr>";
		}
		else {
			$finishOptions_html .= "<td class='left'> <input type='radio' name='finishOptionsId' value='" . $finishOption['id'] . "'></td></tr>";
		}

	}
	$posterTube_html = "<tr><td class='right'>Poster Tube</td><td class='right'>$" . getPosterTubeCost($db) . "</td>";
	$posterTube_html .= "<td class='left'><input type='checkbox' name='posterTube' value='1'></td></tr>";

	$rushOrder_html = "<tr><td class='right'>Rush Order</td><td class='right'>$" . getRushOrderCost($db) ."</td>";
	$rushOrder_html .= "<td class='left'><input type='checkbox' name='rushOrder' value='1'></td></tr>";

	$form_html = "<br \>
			
	<br>
	<form action='index.php' method='post' id='posterInfo' enctype='multipart/form-data' onsubmit='return validateStep2()'>
	 	<input type='hidden' name='MAX_FILE_SIZE' value='209715200'>
		<input type='hidden' name='posterWidth' value='" . $posterWidth . "'>
		<input type='hidden' name='posterLength' value='" . $posterLength . "'>
	<table class='medium_center'>
		<tr><td colspan='3' class='header'>Paper Types</td></tr>
		<tr><td colspan='3' class='description'>Please choose a paper type for your poster.  The cost is per an inch.</td></tr>" . $paperTypes_html . "
	</table>
	
	<br>
	<table class='medium_center'>
		<tr><td colspan='3' class='header'>Finish Options</td></tr>
		<tr><td colspan='3' class='description'>Please choose a finish option for your poster.  The cost is a flat rate.</td></tr>" . $finishOptions_html . "
	</table>
	
	<br>
	<table class='medium_center'>
		<tr><td colspan='3' class='header'>Other Options</td></tr>
		<tr><td colspan='3' class='description'>Please select any additional options.  Rush order will be done within 24 hours during the business week only.</td></tr>" . 
	$posterTube_html .
	$rushOrder_html . "</table>
	
	<br>
	<table class='medium_center'>
		<tr><td colspan='3' class='header'>Required Information</td></tr>
		<tr><td colspan='3' class='description'>Please fill in the following information.</td></tr>
		<tr>
			<td class='right'>Full Name:</td>
			<td class='left'><input type='text' size='29' name='name' id='name'></td>
		</tr>
		<tr>
			<td class='right'>Email:</td>
			<td class='left'><input type='text' size='29' name='email' id='email'></td>
		</tr>
		<tr>
			<td class='right' width='150px'>CFOP Number:</td>
			<td class='left' width='300px'>
				<input type='text' name='cfop1' id='cfop1' maxlength='1' class='cfop_1' onKeyUp='cfopAdvance1()'> - <input type='text' name='cfop2' id='cfop2' maxlength='6' size='6' class='cfop_2' onKeyUp='cfopAdvance2()'> - 
				<input type='text' name='cfop3' id='cfop3' maxlength='6' class='cfop_2' onKeyUp='cfopAdvance3()'> - <input type='text' name='cfop4' id='cfop4' maxlength='6' class='cfop_2'>
			</td>
		</tr>
		<tr>
			<td class='right'>Activity Code (optional):</td>
			<td class='left'><input type='text' class='cfop_2' name='activityCode' id='activityCode' maxlength='6'></td>
		</tr>
		<tr>
			<td class='right'>File (Max " . ini_get('post_max_size') . "):</td>
			<td class='left'><input type='file' size='25' name='posterFile' id='posterFile'></td>
		</tr>
		<tr>
			<td class='right' valign='top'>Comments:</td>
			<td class='left'><textarea name='comments' rows='3' cols='33'></textarea></td>
		</tr>
	</table>
	
	<br>
	
	<table class='center'>
		<tr>
			<td style='padding:5px 0px 10px 0px;'>
				<button onclick='window.location.href=window.location.href'>Cancel</button>
			</td>
			<td style='padding:5px 0px 10px 0px;'>
				<input type='submit' value='Next' name='step2'>
			</td>
		</tr>
	</table>
	</form>
	<div id='paperTypesWarning' class='error'></div>
	<div id='finishOptionsWarning' class='error'></div>
	<div id='nameWarning' class='error'></div>
	<div id='emailWarning' class='error'></div>
	<div id='cfopWarning' class='error'></div>
	<div id='activityCodeWarning' class='error'></div>
	<div id='posterFileWarning' class='error'></div>";


}

//paper type, finish option, cfop, and file submission, confirms order
elseif (isset($_POST['step2'])) {
	$posterWidth = $_POST['posterWidth'];
	$posterLength = $_POST['posterLength'];
	$paperTypesId = $_POST['paperTypesId'];
	$finishOptionsId = $_POST['finishOptionsId'];
	$cfop1 = $_POST['cfop1'];
	$cfop2 = $_POST['cfop2'];
	$cfop3 = $_POST['cfop3'];
	$cfop4 = $_POST['cfop4'];
	$activityCode = $_POST['activityCode'];
	$posterFileName = $_FILES['posterFile']['name'];
	$email = $_POST['email'];
	$name = stripslashes($_POST['name']);
	$comments = stripslashes($_POST['comments']);
	if (isset($_POST['posterTube'])) {
		$posterTube = $_POST['posterTube'];
	}
	else {
		$posterTube = 0;
	}
	if (isset($_POST['rushOrder'])) {
		$rushOrder = $_POST['rushOrder'];
	}
	else {
		$rushOrder = 0;
	}
	$email = trim(rtrim($email));
	$name = trim(rtrim($name));
	$comments = trim(rtrim($comments));


	//gets the file type (ie .jpg, .bmp) of the uploaded poster file.
	$fileType = end(explode(".",$_FILES['posterFile']['name']));
	//creates a temp file name for the file
	$posterFileTmpName = "tmp_" . mt_rand(100000000,999999999) . "." . $fileType;
	//makes the path for the file
	$targetPath = poster_dir . "/" . $posterFileTmpName;
	//moves file to temporary location
	move_uploaded_file($_FILES['posterFile']['tmp_name'],$targetPath);
	//makes the complete CFOP number
	$cfop = $cfop1 . "-" . $cfop2 . "-" . $cfop3 . "-" . $cfop4;


	//Gets Finish Options Information
	$finishOptionsResult = getFinishOption($db,$finishOptionsId);
	$finishOptionCost = $finishOptionsResult[0]['finishOptions_cost'];
	$finishOptionName = $finishOptionsResult[0]['finishOptions_name'];


	//Gets Paper Type Information
	$paperType = getPaperType($db,$paperTypesId);
	$paperTypeCost = $paperType[0]['paperTypes_cost'];
	$paperTypeName = $paperType[0]['paperTypes_name'];
	$paperTypeWidth = $paperType[0]['paperTypes_width'];
	$widthSwitched;

	//Switches around the poster width and length to make the length the shortest possible to save money.
	if (($posterWidth <= $paperTypeWidth) && ($posterLength <= $paperTypeWidth) && ($posterWidth < $posterLength)) {
		$tempPosterWidth = $posterWidth;
		$posterWidth = $posterLength;
		$posterLength = $tempPosterWidth;
		$widthSwitched = 1;
	}
	elseif (($posterWidth > $paperTypeWidth) && ($posterLength <= $paperTypeWidth)) {
		$tempPosterWidth = $posterWidth;
		$posterWidth = $posterLength;
		$posterLength = $tempPosterWidth;
		$widthSwitched = 1;
	}
	else {
		$widthSwitched = 0;
	}

	//Gets Power Tube Information
	if ($posterTube == 1) {
		$posterTubeSql = "SELECT * FROM tbl_posterTube WHERE posterTube_available=1 AND posterTube_name='Yes' LIMIT 1";
	}
	else {
		$posterTube = 0;
		$posterTubeSql = "SELECT * FROM tbl_posterTube WHERE posterTube_available=1 AND posterTube_name='No' LIMIT 1";
	}
	$posterTubeResult = $db->query($posterTubeSql);
	$posterTubeCost =  $posterTubeResult[0]["posterTube_cost"];
	$posterTubeName =  $posterTubeResult[0]["posterTube_name"];
	$posterTubeId = $posterTubeResult[0]["posterTube_id"];

	//Gets Rush Order Information
	if ($rushOrder == 1) {
		$rushOrderSql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_available=1 AND rushOrder_name='Yes' LIMIT 1";
	}
	else {
		$rushOrder = 0;
		$rushOrderSql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_available=1 AND rushOrder_name='No' LIMIT 1";
	}
	$rushOrderResult = $db->query($rushOrderSql);
	$rushOrderCost = $rushOrderResult[0]["rushOrder_cost"];
	$rushOrderName = $rushOrderResult[0]["rushOrder_name"];
	$rushOrderId = $rushOrderResult[0]["rushOrder_id"];

	//Calculates Total Cost
	$totalCost = ($posterLength * $paperTypeCost) + $finishOptionCost + ($posterTube * $posterTubeCost) + ($rushOrder * $rushOrderCost);

	//outputs the order information to confirm the order.
	$form_html = "<table class='medium_center'>";
	$form_html .= "<tr><td colspan='2' class='header'>Review</td></tr>";
	$form_html .= "<tr><td colspan='2' class='description'>Please review your order below, then click \"Submit Order\" to send your order</td></tr>";

	if ($widthSwitched == 1) {
		$form_html .= "<tr><td colspan='2' class='description'>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</td></tr>";

	}
	$form_html .= "<tr><td class='right'>Poster File:</td><td>" . $posterFileName . "</td></tr>";
	$form_html .= "<tr><td class='right'>Width:</td><td>" . $posterWidth . "\"</td></tr>";
	$form_html .= "<tr><td class='right'>Length:</td><td>" . $posterLength . "\"</td></tr>";
	$form_html .= "<tr><td class='right'>Paper Type:</td><td>" . $paperTypeName . "</td></tr>";
	$form_html .= "<tr><td class='right'>Finish Option:</td><td>" . $finishOptionName . "</td></tr>";
	$form_html .= "<tr><td class='right'>Poster Tube:</td><td>" . $posterTubeName . "</td></tr>";
	$form_html .= "<tr><td class='right'>Rush Order:</td><td>" . $rushOrderName . "</td></tr>";
	$form_html .= "<tr><td class='right'>Total Cost:</td><td>$" . $totalCost . "</td></tr>";
	$form_html .= "<tr><td class='right'>CFOP:</td><td>" . $cfop . "</td></tr>";
	$form_html .= "<tr><td class='right'>Activity Code:</td><td>" . $activityCode . "</td></tr>";
	$form_html .= "<tr><td class='right'>Email:</td><td>" . $email . "</td></tr>";
	$form_html .= "<tr><td class='right'>Full Name:</td><td>" . stripslashes($name) . "</td></tr>";
	$form_html .= "<tr><td class='right' valign='top'>Comments:</td><td>" . stripslashes($comments) . "</td></tr>";
	$form_html .= "</table>";
	$form_html .= "<br><form method='post' action='index.php'>";
	$form_html .= "<table class='center'>";
	$form_html .= "<tr>";
	$form_html .= "<td style='padding:5px 0px 10px 0px;'>";
	$form_html .= "<button onclick='window.location.href=window.location.href' class='button_1'>Cancel</button>";
	$form_html .= "</td>";
	$form_html .= "<td style='padding:5px 0px 10px 0px;'>";
	$form_html .= "<input type='hidden' name='posterWidth' value='$posterWidth'>";
	$form_html .= "<input type='hidden' name='posterLength' value='$posterLength'>";
	$form_html .= "<input type='hidden' name='paperTypesId' value='$paperTypesId'>";
	$form_html .= "<input type='hidden' name='paperTypeName' value='$paperTypeName'>";
	$form_html .= "<input type='hidden' name='finishOptionsId' value='$finishOptionsId'>";
	$form_html .= "<input type='hidden' name='finishOptionName' value='$finishOptionName'>";
	$form_html .= "<input type='hidden' name='totalCost' value='$totalCost'>";
	$form_html .= "<input type='hidden' name='cfop' value='$cfop'>";
	$form_html .= "<input type='hidden' name='activityCode' value='$activityCode'>";
	$form_html .= "<input type='hidden' name='email' value='$email'>";
	$form_html .= "<input type='hidden' name='name' value='" . htmlspecialchars($name,ENT_QUOTES) . "'>";
	$form_html .= "<input type='hidden' name='comments' value='" . htmlspecialchars($comments,ENT_QUOTES) . "'>";
	$form_html .= "<input type='hidden' name='posterTubeName' value='$posterTubeName'>";
	$form_html .= "<input type='hidden' name='posterTubeId' value='$posterTubeId'>";
	$form_html .= "<input type='hidden' name='rushOrderId' value='$rushOrderId'>";
	$form_html .= "<input type='hidden' name='rushOrderName' value='$rushOrderName'>";
	$form_html .= "<input type='hidden' name='posterFileName' value='$posterFileName'>";
	$form_html .= "<input type='hidden' name='posterFileTmpName' value='$posterFileTmpName'>";
	$form_html .= "<input type='hidden' name='widthSwitched' value='$widthSwitched'>";
	$form_html .= "<input type='submit' name='step3' value='Submit Order' class='button_1'></td></tr>";
	$form_html .= "</table></form>";

}

//sends the order
elseif (isset($_POST['step3'])) {
	$posterWidth = $_POST['posterWidth'];
	$posterLength = $_POST['posterLength'];
	$paperTypesId = $_POST['paperTypesId'];
	$paperTypeName = $_POST['paperTypeName'];
	$finishOptionsId = $_POST['finishOptionsId'];
	$finishOptionName = $_POST['finishOptionName'];
	$posterTubeName = $_POST['posterTubeName'];
	$posterTubeId = $_POST['posterTubeId'];
	$rushOrderName = $_POST['rushOrderName'];
	$rushOrderId = $_POST['rushOrderId'];
	$cfop = $_POST['cfop'];
	$activityCode = strtoupper($_POST['activityCode']);
	$totalCost = $_POST['totalCost'];
	$email = $_POST['email'];
	$name = stripslashes($_POST['name']);
	$comments = stripslashes($_POST['comments']);
	$widthSwitched = $_POST['widthSwitched'];
	$posterFileName = $_POST['posterFileName'];
	$posterFileTmpName = $_POST['posterFileTmpName'];

	$sql = "INSERT INTO tbl_orders(orders_email, ";
	$sql .= "orders_name, orders_fileName, orders_totalCost, ";
	$sql .= "orders_cfop, orders_activityCode, orders_width, ";
	$sql .= "orders_length, orders_statusId, orders_paperTypesId, ";
	$sql .= "orders_finishOptionsId, orders_comments, orders_posterTubeId, ";
	$sql .= "orders_rushOrderId, orders_widthSwitched) ";
	$sql .= "VALUES('" . $email . "','" . $name . "',' ";
	$sql .= $posterFileName . "'," . $totalCost . ",'" . $cfop . "','";
	$sql .= $activityCode . "'," . $posterWidth . "," . $posterLength . ",'1',";
	$sql .= $paperTypesId . "," . $finishOptionsId . ",'" . $comments . "',";
	$sql .= $posterTubeId . "," .$rushOrderId . "," . $widthSwitched . ")";


	//runs query and gets the order_id
	$orderId = $db->insert_query($sql);
	//gets the file type (ie .jpg, .bmp) of the uploaded poster file.
	$fileType = end(explode(".",$posterFileName));
	//sets the path to where the file will be saved.
	$targetPath = poster_dir . "/" . $orderId . "." . $fileType;

	//renames the temporary file to its permanent file name which is the orderId number plus the filetype extensions.
	rename(poster_dir . "/" . $posterFileTmpName,$targetPath);

	//mail new order to users and admins
	mailNewOrder($db,$orderId,admin_email);

	$form_html = "<table class='medium_center'>";
	$form_html .= "<tr><td colspan='2' class='header'>Order Information</td></tr>";
	$form_html .= "<tr><td colspan='2' class='description'>Thank you for your order.  Your order will be processed as soon as possible.  It could take up to three days.";
	$form_html .= "An email has been sent to you at " . $email . " with this information. We will email you when the poster is completed printing.</td></tr>";

	if ($widthSwitched == 1) {
		$form_html .= "<tr><td colspan='2' class='description'>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</td></tr>";
	}

	$form_html .= "	<tr><td class='right'>Full Name:</td><td>" . $name . "</td></tr>";
	$form_html .= "<tr><td class='right'>Order Number:</td><td>" . $orderId . "</td></tr>";
	$form_html .= "<tr><td class='right'>File:</td><td>" . $posterFileName . "</td></tr>";
	$form_html .= "<tr><td class='right'>Length:</td><td>" . $posterLength . "\"</td></tr>";
	$form_html .= "<tr><td class='right'>Width:</td><td>" . $posterWidth . "\"</td></tr>";
	$form_html .= "<tr><td class='right'>Paper Type:</td><td>" . $paperTypeName . "</td></tr>";
	$form_html .= "<tr><td class='right'>Finish Option:</td><td>" . $finishOptionName . "</td></tr>";
	$form_html .= "<tr><td class='right'>Poster Tube:</td><td>" . $posterTubeName . "</td></tr>";
	$form_html .= "<tr><td class='right'>Rush Order:</td><td>" . $rushOrderName . "</td></tr>";
	$form_html .= "<tr><td class='right' valign='top'>Comments:</td><td>" . $comments . "</td></tr>";
	$form_html .= "<tr><td class='right'>CFOP:</td><td>" . $cfop . "</td></tr>";
	$form_html .= "<tr><td class='right'>Activity Code:</td><td>" . $activityCode . "</td></tr>";
	$form_html .= "<tr><td class='right'>Total Cost:</td><td>$" . $totalCost . "</td></tr>";
	$form_html .= "<tr><td class='description' colspan='2'>If you have any questions please contact us at " . admin_email ."</td></tr>";
	$form_html .= "</table>";
		
}

elseif (enable == FALSE) {
	$form_html = "<br>The poster printer is currently broken, soon maintenance should arrive to take care of the problem.";
	$form_html .= "<br>In the mean time, we are not accepting any new poster orders.  Please accept our apologies.";

}
else {

	$paperTypes = getPaperTypes($db);

	$paperTypes_html = "";
	foreach ($paperTypes as $paperType) {
		$paperTypes_html .= "<tr>";
		$paperTypes_html .= "<td class='right'>$" . $paperType['cost'] . "</td>";
		$paperTypes_html .= "<td class='right'>" .  $paperType['name'] . "</td>";
		$paperTypes_html .= "<td class='left'>" . $paperType['width'] . "''</td>";
		$paperTypes_html .= "</tr>";
	}
	$form_html = "<br><form action='index.php' method='post' id='posterInfo' onsubmit='return validateStep1()' name='posterInfo'>";
	$form_html .= "<input type='hidden' name='maxPrinterWidth' value='" . max_printer_width . "'>";
	$form_html .= "<table class='medium_center'>";
	$form_html .= "<tr><td colspan='2' class='header'>Paper Size</td></tr>";
	$form_html .= "<tr><td colspan='2' class='description' width='400'>Please choose a width and length for your poster.  The width maximum is " . max_printer_width . " inches.</td></tr>";
	$form_html .= "<tr><td class='right'>Width:</td>";
	$form_html .= "<td class='left'><input type='text' name='posterWidth' id='posterWidth' maxlength='6' size='6'>\"</td></tr>";
	$form_html .= "<tr><td class='right'>Length:</td>";
	$form_html .= "<td class='left'><input type='text' name='posterLength' id='posterLength' maxlength='6' size='6'>\"</td></tr>";
	$form_html .= "</table>";
	$form_html .= "<br>";
	$form_html .= "<table class='medium_center'>";
	$form_html .= "<tr><td colspan='3' class='header'>Available Paper Types</td></tr>";
	$form_html .= "<tr><td colspan='3' class='description'>Below are the available paper types along with the maximum width for that type of paper. The cost is per an inch.</td></tr>";
	$form_html .= $paperTypes_html;
	$form_html .= "</table>";
	$form_html .= "<table class='center'>";
	$form_html .= "<tr><td style='padding:5px 0px 10px 0px;'>";
	$form_html .= "<button onclick='window.location.href=window.location.href'>Cancel</button></td>";
	$form_html .= "<td style='padding:5px 0px 10px 0px;'><input class='button_1' type='submit'  value='Next' name='step1'></td>";
	$form_html .= "</tr></table></form>";
	$form_html .= "<div id='widthWarning' class='error'></div>";
	$form_html .= "<div id='lengthWarning' class='error'></div>";

}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" type="text/css" href="includes/stylesheet.css"
		media='screen'>

		<script type="text/javascript" src="includes/poster.inc.js"></script>

		<title>Poster Printer Submit Page</title>

</head>

<body OnLoad="document.posterInfo.posterWidth.focus();">
	<div id='container'>
		<div id="order">
			<h2>Poster Printer Order Submit Form</h2>

			<?php echo $form_html; ?>

		</div>
	</div>
</body>

</html>
