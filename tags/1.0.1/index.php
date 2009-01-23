<?php
//////////////////////////////////////////////////////
//													//
//	Poster Printer Order Submittion					//
//	index.php										//
//													//
//	Page to allow the user to submit poster files 	//
//													//
//	David Slater									//
//	April 2007										//
//													//
//////////////////////////////////////////////////////

//Include files for the script to run
include 'includes/settings.inc.php';
include 'includes/mail.inc.php';

//poster width and length submission
if (isset($_POST['step1'])) {

	$posterWidth = $_POST['posterWidth'];
	$posterLength = $_POST['posterLength'];
	$posterWidth = trim(rtrim($posterWidth));
	$posterLength = trim(rtrim($posterLength));
	
	
	//Connects to database.  mysql settings are pulled from includes/settings.inc.php
	$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
	@mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");
	//sql statement to get the available paper types.
	$paperTypesSql = "SELECT * FROM tbl_paperTypes WHERE paperTypes_available=1 AND (paperTypes_width>=$posterWidth OR paperTypes_width>=$posterLength) ORDER BY paperTypes_name ASC";
	
	//runs the query.
	$paperTypesResult = mysql_query($paperTypesSql,$db);
	//takes the result and formats it into html into the paperTypeHTML variable.
	$paperTypesHTML;
	for ($i=0;$i < mysql_num_rows($paperTypesResult);$i++) {
	
		$paperTypesHTML .= "<tr><td class='td_2'>$" . mysql_result($paperTypesResult,$i,"paperTypes_cost") . "</td>" .
						"<td class='td_2'>" .  mysql_result($paperTypesResult,$i,"paperTypes_name") . "</td>";
		if (mysql_result($paperTypesResult,$i,"paperTypes_default") == 1) {
			$paperTypesHTML .= "<td class='form'><input type='radio' name='paperTypesId' checked='true' value='" . mysql_result($paperTypesResult,$i,"paperTypes_id") . "'></td></tr>";
		}
		else {
			$paperTypesHTML .= "<td class='form'><input type='radio' name='paperTypesId' value='" . mysql_result($paperTypesResult,$i,"paperTypes_id") . "'></td></tr>";
		}
						
	
	}
	
	//sql statment to get the available finishOptions.
	$finishOptionsSql = "SELECT * FROM tbl_finishOptions WHERE finishOptions_available=1 AND finishOptions_maxLength>=$posterLength AND" . 
						"(finishOptions_maxWidth>=$posterWidth OR finishOptions_maxWidth>=$posterLength) ORDER BY finishOptions_name ASC";
	$finishOptionsResult = mysql_query($finishOptionsSql,$db);
	//takes the result and formats it into html into the finishOptionsHTML variable.
	$finishOptionsHTML;
	for ($i=0; $i < mysql_num_rows($finishOptionsResult); $i++) {
		
		$finishOptionsHTML .= "<tr><td class='td_2'>$" . mysql_result($finishOptionsResult,$i,"finishOptions_cost") . "</td>" .
				"<td class='td_2'>" . mysql_result($finishOptionsResult,$i,"finishOptions_name") . "</td>";
		
		if (mysql_result($finishOptionsResult,$i,"finishOptions_default") == 1) {
			$finishOptionsHTML .= "<td class='form'> <input type='radio' name='finishOptionsId' checked='true' value='" . mysql_result($finishOptionsResult,$i,"finishOptions_id") . "'></td>" .
							"</tr>";
		}
		else {
			$finishOptionsHTML .= "<td class='form'> <input type='radio' name='finishOptionsId' value='" . mysql_result($finishOptionsResult,$i,"finishOptions_id") . "'></td>" .
							"</tr>";
		
		}
							
	
	}
	
	$posterTubeSql = "SELECT * FROM tbl_posterTube WHERE posterTube_available=1 AND posterTube_name='Yes'";
	$posterTubeResult = mysql_query($posterTubeSql,$db);
	$posterTubeHTML = "<tr><td class='td_2'>Poster Tube</td><td class='td_2'>$" . mysql_result($posterTubeResult,0,"posterTube_cost") ."</td>" .
					"<td class='form'><input type='checkbox' name='posterTube' value='1'></td></tr>";

	$rushOrderSql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_available=1 AND rushOrder_name='Yes'";
	$rushOrderResult = mysql_query($rushOrderSql,$db);
	$rushOrderHTML = "<tr><td class='td_2'>Rush Order</td><td class='td_2'>$" . mysql_result($rushOrderResult,0,"rushOrder_cost") ."</td>" .
					"<td class='form'><input type='checkbox' name='rushOrder' value='1'></td></tr>";
	
	$formHTML = "<br \>
			
	<br>
	<center>
	
	<form action='index.php' method='post' id='posterInfo' enctype='multipart/form-data' onsubmit='return validateStep2()'>
	 	<input type='hidden' name='MAX_FILE_SIZE' value='134217728'>
		<input type='hidden' name='posterWidth' value='$posterWidth'>
		<input type='hidden' name='posterLength' value='$posterLength'>
	<table class='table_1'>
		<tr><th colspan='3'>Paper Type</th></tr>
		<tr><td class='td_1' colspan='3'>Please choose a paper type for your poster.  The cost is per an inch</td></tr>
		<input type='hidden' name='paperTypesId' value='none'>" . $paperTypesHTML . "
	</table>
	
	<br>
	<table class='table_1'>
		<tr><th colspan='3'>Finish Options</th></tr>
		<tr><td class='td_1' colspan='3'>Please choose a finish option for your poster.  The cost is a flat rate</td></tr>
		<input type='hidden' name='finishOptionsId' value='none'>" . $finishOptionsHTML . "
	</table>
	
	<br>
	<table class='table_1'>
		<tr><th colspan='3'>Other Options</th></tr>
		<tr><td class='td_1' colspan='3'>Please select any additional options.  Rush order will be done within 24 hours during the business week only</td></tr>" . 
		$posterTubeHTML .
		$rushOrderHTML . "</table>
	
	<br>
	<table class='table_1'>
		<tr><th colspan='3'>Required Information</th></tr>
		<tr><td class='td_1' colspan='3'>Please fill in the following information</td></tr>
		<tr>
			<td class='td_2'>Full Name:</td>
			<td class='td_3'><input type='text' size='29' name='name' id='name'></td>
		</tr>
		<tr>
			<td class='td_2'>Email:</td>
			<td class='td_3'><input type='text' size='29' name='email' id='email'></td>
		</tr>
		<tr>
			<td class='td_2' width='150px'>CFOP Number:</td>
			<td class='td_3' width='300px'>
				<input type='text' name='cfop1' id='cfop1' maxlength='1' class='input_3' onKeyUp='cfopAdvance1()'> - <input type='text' name='cfop2' id='cfop2' maxlength='6' size='6' class='input_4' onKeyUp='cfopAdvance2()'> - 
				<input type='text' name='cfop3' id='cfop3' maxlength='6' class='input_4' onKeyUp='cfopAdvance3()'> - <input type='text' name='cfop4' id='cfop4' maxlength='6' class='input_4'>
			</td>
		</tr>
		<tr>
			<td class='td_2'>File:</td>
			<td class='td_3'><input type='file' size='25' name='posterFile' id='posterFile'></td>
		</tr>
		<tr>
			<td class='td_2' valign='top'>Comments:</td>
			<td class='td_3'><textarea name='comments' rows='3' cols='30'></textarea></td>
		</tr>
	</table>
	
	<br>
	
	<table>
		<tr>
			<td style='padding:5px 0px 10px 0px;'>
				<button onclick='window.location.href=window.location.href' class='button_1'>Cancel</button>
			</td>
			<td style='padding:5px 0px 10px 0px;'>
				<input type='submit' value='Next' name='step2' class='button_1'>
			</td>
		</tr>
	</table>
	</form>
	</center>
	<div id='paperTypesWarning' class='error'></div>
	<div id='finishOptionsWarning' class='error'></div>
	<div id='nameWarning' class='error'></div>
	<div id='emailWarning' class='error'></div>
	<div id='cfopWarning' class='error'></div>
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
	$posterFileName = $_FILES['posterFile']['name'];
	$email = $_POST['email'];
	$name = stripslashes($_POST['name']);
	$comments = stripslashes($_POST['comments']);
	$posterTube = $_POST['posterTube'];
	$rushOrder = $_POST['rushOrder'];
	
	$email = trim(rtrim($email));
	$name = trim(rtrim($name));
	$comments = trim(rtrim($comments));
	
	
	//gets the file type (ie .jpg, .bmp) of the uploaded poster file.
	$fileType = end(explode(".",$_FILES['posterFile']['name']));
	//creates a temp file name for the file
	$posterFileTmpName = "tmp_" . mt_rand(100000000,999999999) . "." . $fileType;
	//makes the path for the file
	$targetPath = $posterDirectory . "/" . $posterFileTmpName;
	//moves file to temporary location
	move_uploaded_file($_FILES['posterFile']['tmp_name'],$targetPath);
	//makes the complete CFOP number
	$cfop = $cfop1 . "-" . $cfop2 . "-" . $cfop3 . "-" . $cfop4;
	
	//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
	$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
	mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");

	//Gets Finish Options Information
	$finishOptionsSql = "SELECT * FROM tbl_finishOptions WHERE finishOptions_id=" . $finishOptionsId;
	$finishOptionsResult = mysql_query($finishOptionsSql,$db)
		or die("Problem with database. " . mysql_error());
	$finishOptionCost = mysql_result($finishOptionsResult,0,'finishOptions_cost');
	$finishOptionName = mysql_result($finishOptionsResult,0,'finishOptions_name');
	
	
	//Gets Paper Type Information
	$paperTypesSql = "SELECT * FROM tbl_paperTypes WHERE paperTypes_id=" . $paperTypesId;
	$paperTypesResult = mysql_query($paperTypesSql,$db)
		or die("Problem with database. " . mysql_error());
	$paperTypeCost = mysql_result($paperTypesResult,0,'paperTypes_cost');
	$paperTypeName = mysql_result($paperTypesResult,0,'paperTypes_name');
	$paperTypeWidth = mysql_result($paperTypesResult,0,'paperTypes_width');
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
	if ($posterTube ==1) {
		$posterTubeSql = "SELECT * FROM tbl_posterTube WHERE posterTube_available=1 AND posterTube_name='Yes'";
	}
	else {
		$posterTubeSql = "SELECT * FROM tbl_posterTube WHERE posterTube_available=1 AND posterTube_name='No'";
	}
	$posterTubeResult = mysql_query($posterTubeSql,$db)
		or die("Problem with poster tube. " . mysql_error());
	$posterTubeCost =  mysql_result($posterTubeResult,0,"posterTube_cost");
	$posterTubeName =  mysql_result($posterTubeResult,0,"posterTube_name");
	$posterTubeId = mysql_result($posterTubeResult,0,"posterTube_id");
	
	//Gets Rush Order Information
	if ($rushOrder == 1) {
		$rushOrderSql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_available=1 AND rushOrder_name='Yes'";
	}
	else {
		$rushOrderSql = "SELECT * FROM tbl_rushOrder WHERE rushOrder_available=1 AND rushOrder_name='No'";
	}
	$rushOrderResult = mysql_query($rushOrderSql,$db)
		or die("Problem with rush order. " . mysql_error());
	$rushOrderCost = mysql_result($rushOrderResult,0,"rushOrder_cost");
	$rushOrderName = mysql_result($rushOrderResult,0,"rushOrder_name");
	$rushOrderId = mysql_result($rushOrderResult,0,"rushOrder_id");

	if ($posterTubeName == "Yes")
		$posterTubeYesNo = 1;
	elseif ($posterTubeName == "No")
		$posterTubeYesNo = 0;
	
	if ($rushOrderName == "Yes")
		$rushOrderYesNo = 1;
	elseif ($rushOrderName == "No")
		$rushOrderYesNo = 0;
		
	//Calculates Total Cost
	$totalCost = ($posterLength * $paperTypeCost) +$finishOptionCost + ($posterTubeYesNo * $posterTubeCost) + ($rushOrderYesNo * $rushOrderCost);
		
	//outputs the order information to confirm the order.
	$formHTML = "<center>
				
				<table class='table_1'>
						<tr><th colspan='2'>Review</th></tr>
						<tr><td class='td_1' colspan='2'>Please review your order below, then click \"Submit Order\" to send your order</td></tr>";
	if ($widthSwitched == 1) {
		$formHTML .= "<tr><td class='td_1' colspan='2'>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</td></tr>";
	
	}
	$formHTML .=	"<tr><td class='td_2'>Poster File:</td><td>" . $posterFileName . "</td></tr>
					<tr><td class='td_2'>Length:</td><td>" . $posterLength . "\"</td></tr>
					<tr><td class='td_2'>Width:</td><td>" . $posterWidth . "\"</td></tr>
					<tr><td class='td_2'>Paper Type:</td><td>" . $paperTypeName . "</td></tr>
					<tr><td class='td_2'>Finish Option:</td><td>" . $finishOptionName . "</td></tr>
					<tr><td class='td_2'>Poster Tube:</td><td>" . $posterTubeName . "</td></tr>
					<tr><td class='td_2'>Rush Order:</td><td>" . $rushOrderName . "</td></tr>
					<tr><td class='td_2'>Total Cost:</td><td>$" . $totalCost . "</td></tr>
					<tr><td class='td_2'>CFOP:</td><td>" . $cfop . "</td></tr>
					<tr><td class='td_2'>Email:</td><td>" . $email . "</td></tr>
					<tr><td class='td_2'>Full Name:</td><td>" . stripslashes($name) . "</td></tr>
					<tr><td class='td_2' valign='top'>Comments:</td><td>" . stripslashes($comments) . "</td></tr>
				</table>
				
				<br>
				
				<table>
					<tr>
						<td style='padding:5px 0px 10px 0px;'>
							<button onclick='window.location.href=window.location.href' class='button_1'>Cancel</button>
						</td>
						<td style='padding:5px 0px 10px 0px;'>
							<form method='post' action='index.php'>
								<input type='hidden' name='posterWidth' value='$posterWidth'>
								<input type='hidden' name='posterLength' value='$posterLength'>
								<input type='hidden' name='paperTypesId' value='$paperTypesId'>
								<input type='hidden' name='paperTypeName' value='$paperTypeName'>
								<input type='hidden' name='finishOptionsId' value='$finishOptionsId'>
								<input type='hidden' name='finishOptionName' value='$finishOptionName'>
								<input type='hidden' name='totalCost' value='$totalCost'>
								<input type='hidden' name='cfop' value='$cfop'>
								<input type='hidden' name='email' value='$email'>
								<input type='hidden' name='name' value='" . htmlspecialchars($name,ENT_QUOTES) . "'>
								<input type='hidden' name='comments' value='" . htmlspecialchars($comments,ENT_QUOTES) . "'>
								<input type='hidden' name='posterTubeName' value='$posterTubeName'>
								<input type='hidden' name='posterTubeId' value='$posterTubeId'>
								<input type='hidden' name='rushOrderId' value='$rushOrderId'>
								<input type='hidden' name='rushOrderName' value='$rushOrderName'>
								<input type='hidden' name='posterFileName' value='$posterFileName'>
								<input type='hidden' name='posterFileTmpName' value='$posterFileTmpName'>
								<input type='hidden' name='widthSwitched' value='$widthSwitched'>
								<input type='submit' name='step3' value='Submit Order' class='button_1'>
								
							</form>
						</td>
					</tr>
				</table>
				</center>";
	
	
			
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
	$totalCost = $_POST['totalCost'];
	$posterFileName = $_POST['posterFileName'];
	$posterFileTmpName = $_POST['posterFileTmpName'];
	$email = $_POST['email'];
	$name = stripslashes($_POST['name']);
	$comments = stripslashes($_POST['comments']);
	$widthSwitched = $_POST['widthSwitched'];
	
	//connects to the database.  Pulls the mysql settings from the file includes/settings.inc.php.
	$db = mysql_connect($mysqlSettings['host'],$mysqlSettings['username'],$mysqlSettings['password']);
	mysql_select_db($mysqlSettings['database'],$db) or die("Unable to select database");

	//sql string to insert order into database.
	$ordersSql = "INSERT INTO tbl_orders(orders_email,orders_name,orders_fileName,orders_totalCost,orders_cfop,orders_width,orders_length,orders_statusId,orders_paperTypesId,orders_finishOptionsId,orders_comments,orders_posterTubeId,orders_rushOrderId,orders_widthSwitched) VALUES('" . $email . "','" .  mysql_real_escape_string($name) . "','" . $posterFileName . "'," . $totalCost . ",'" . $cfop . "'," . $posterWidth . "," . 
				$posterLength . ",'1'," . $paperTypesId . "," . $finishOptionsId . ",'" . mysql_real_escape_string($comments) . "'," . $posterTubeId . "," . $rushOrderId . "," . $widthSwitched . ")";
				
	//runs query and gets the order_id
	mysql_query($ordersSql,$db);
	$orderID = mysql_insert_id($db);
	//gets the file type (ie .jpg, .bmp) of the uploaded poster file.
	$fileType = end(explode(".",$posterFileName));
	//sets the path to where the file will be saved.
	$targetPath = $posterDirectory . "/" . $orderID . "." . $fileType;
	
	//renammes the temporary file to its permanment file name which is the orderID number plus the filetype extensions.
	rename($posterDirectory . "/" . $posterFileTmpName,$targetPath);

	//sets an array with order information.
	$orderInfo = array(
					'email' => $email,
					'name' => $name,
					'orderID' => $orderID,
					'fileName' => $posterFileName,
					'totalCost' => $totalCost,
					'posterLength' => $posterLength,
					'posterWidth' => $posterWidth,
					'cfop' => $cfop,
					'paperType' => $paperTypeName,
					'finishOption' => $finishOptionName,
					'posterTube' => $posterTubeName,
					'rushOrder' => $rushOrderName,
					'comments' => $comments,
					'adminEmail' => $adminEmail,
				);
				
	//mails admins that there is a new poster to be printed.
	mailAdminsNewOrder($orderInfo);
	//mails user with poster order information
	mailUserNewOrder($orderInfo);
	
	$formHTML = "<center>
				<table class='table_1'>
						<tr><th colspan='2'>Order Information</th></tr>
						<tr>
							<td class='td_1' colspan='2'>Thank you for your order.  Your order will be processed as soon as possible.  It could take up to three days.
				An email has been sent to you at " . $email . " with this information. We will email you when the poster is completed printing.</td>
						</tr>";
						
	if ($widthSwitched == 1) {
	$formHTML .= "<tr><td class='td_1' colspan='2'>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</td></tr>";
	}
	
	$formHTML .= "	<tr><td class='td_2'>Full Name:</td><td>" . $name . "</td></tr>
					<tr><td class='td_2'>Order Number:</td><td>" . $orderID . "</td></tr>
					<tr><td class='td_2'>File:</td><td>" . $posterFileName . "</td></tr>
					<tr><td class='td_2'>Length:</td><td>" . $posterLength . "\"</td></tr>
					<tr><td class='td_2'>Width:</td><td>" . $posterWidth . "\"</td></tr>
					<tr><td class='td_2'>Paper Type:</td><td>" . $paperTypeName . "</td></tr>
					<tr><td class='td_2'>Finish Option:</td><td>" . $finishOptionName . "</td></tr>
					<tr><td class='td_2'>Poster Tube:</td><td>" . $posterTubeName . "</td></tr>
					<tr><td class='td_2'>Rush Order:</td><td>" . $rushOrderName . "</td></tr>
					<tr><td class='td_2' valign='top'>Comments:</td><td>" . $comments . "</td>
					<tr><td class='td_2'>CFOP:</td><td>" . $cfop . "</td></tr>
					<tr><td class='td_2'>Total Cost:</td><td>$" . $totalCost . "</td></tr>
					<tr><td class='td_1' colspan='2'>If you have any questions please contact us at " . $adminEmail ."</td></tr>
			</table>
			</center>";
					
}

elseif ($enable == FALSE) {
	$formHTML = "<br>
			<center>The poster printer is currently broken, soon maintenance should arrive to take care of the problem.  In the mean time, we are not accepting any new poster orders.  Please accept our apologies.</center>";




}
else {

	
	$formHTML = "<br>
				
	<center>
	<form action='index.php' method='post' id='posterInfo' onsubmit='return validateStep1()' name='posterInfo'>
	<input type='hidden' name='maxPrinterWidth' value='$maxPrinterWidth'>
	<table class='table_1'>
		<tr><th colspan='2'>Paper Size</th></tr>
		<tr><td colspan='2' class='td_1' width='400'>Please choose a width and length for your poster.  The width maximum is $maxPrinterWidth inches</td></tr>
		<tr>
			<td class='td_2'>Width:</td>
			<td class='td_3'><input type='text' name='posterWidth' id='posterWidth' maxlength='6' class='input_1'>\"</td>
		</tr>
		<tr>
			<td class='td_2'>Length:</td>
			<td class='td_3'><input type='text' name='posterLength' id='posterLength' maxlength='6' class='input_1'>\"</td>
		</tr>
	</table>
	
	<br>
	
	<table>
	<tr>
			<td style='padding:5px 0px 10px 0px;'>
				<button class='button_1' onclick='window.location.href=window.location.href'>Cancel</button>
			</td>
			<td style='padding:5px 0px 10px 0px;'>
				<input class='button_1' type='submit'  value='Next' name='step1'>
			</td>
		</tr>
	</table>
	</form>
	</center>
	<div id='widthWarning' class='error'></div>
	<div id='lengthWarning' class='error'></div>";

}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css" media='screen'>

<script type="text/javascript" src="includes/poster.inc.js"></script>

<title>Poster Printer Submit Page</title>
</head>

<body OnLoad="document.posterInfo.posterWidth.focus();">
<div id="content_center">
<h2>Poster Printer Order Submit Form</h2>

<?php echo $formHTML; ?>

</div>
</body>

</html>
