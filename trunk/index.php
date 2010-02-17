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
include_once 'includes/settings.inc.php';
set_include_path(get_include_path() . ':libs');
include_once 'db.class.inc.php';
include_once 'mail.inc.php';
include_once 'orders.inc.php';
include_once 'paperTypes.inc.php';
include_once 'finishOptions.inc.php';
include_once 'posterTube.inc.php';
include_once 'rushOrder.inc.php';


$db = new db(mysql_host,mysql_database,mysql_user,mysql_password);

//poster width and length submission
if (isset($_POST['step1'])) {

	$posterWidth = $_POST['posterWidth'];
	$posterLength = $_POST['posterLength'];
	$posterWidth = trim(rtrim($posterWidth));
	$posterLength = trim(rtrim($posterLength));
	

	$paperTypes = getValidPaperTypes($db,$posterWidth,$posterLength);
	//takes the result and formats it into html into the paperTypeHTML variable.
	$paperTypesHTML;
	for ($i=0;$i < count($paperTypes);$i++) {
		$paperType_cost = $paperTypes[$i]['paperTypes_cost'];
		$paperType_name = $paperTypes[$i]['paperTypes_name'];
		$paperType_id = $paperTypes[$i]['paperTypes_id'];
		$paperType_default = $paperTypes[$i]['paperTypes_default'];
		$paperTypesHTML .= "<tr>";
		$paperTypesHTML .= "<td class='td_2'>$" . $paperType_cost . "</td>";
		$paperTypesHTML .= "<td class='td_2'>" .  $paperType_name . "</td>";
		if ($paperType_default == 1) {
			$paperTypesHTML .= "<td class='td_4'><input type='radio' name='paperTypesId' checked='true' value='" . $paperType_id . "'></td></tr>";
		}
		else {
			$paperTypesHTML .= "<td class='td_4'><input type='radio' name='paperTypesId' value='" . $paperType_id . "'></td></tr>";
		}

	}
	

	$finishOptions = getValidFinishOptions($db,$posterWidth,$posterLength);
	//takes the result and formats it into html into the finishOptionsHTML variable.
	$finishOptionsHTML;
	for ($i=0; $i < count($finishOptions); $i++) {
		$finishOption_id = $finishOptions[$i]['finishOptions_id'];
		$finishOption_name = $finishOptions[$i]['finishOptions_name'];
		$finishOption_cost = $finishOptions[$i]['finishOptions_cost'];
		$finishOption_default = $finishOptions[$i]['finishOptions_default'];
		$finishOptionsHTML .= "<tr>";
		$finishOptionsHTML .= "<td class='td_2'>$" . $finishOption_cost . "</td>";
		$finishOptionsHTML .= "<td class='td_2'>" . $finishOption_name . "</td>";
		if ($finishOption_default == 1) {
			$finishOptionsHTML .= "<td class='td_4'> <input type='radio' name='finishOptionsId' checked='true' value='" . $finishOption_id . "'></td></tr>";
		}
		else {
			$finishOptionsHTML .= "<td class='td_4'> <input type='radio' name='finishOptionsId' value='" . $finishOption_id . "'></td></tr>";
		
		}

	}
	
	$posterTubeHTML = "<tr><td class='td_2'>Poster Tube</td><td class='td_2'>$" . getPosterTubeCost($db) ."</td>" .
					"<td class='td_4'><input type='checkbox' name='posterTube' value='1'></td></tr>";

	$rushOrderHTML = "<tr><td class='td_2'>Rush Order</td><td class='td_2'>$" . getRushOrderCost($db) ."</td>" .
					"<td class='td_4'><input type='checkbox' name='rushOrder' value='1'></td></tr>";
	
	$formHTML = "<br \>
			
	<br>
	<center>
	
	<form action='index.php' method='post' id='posterInfo' enctype='multipart/form-data' onsubmit='return validateStep2()'>
	 	<input type='hidden' name='MAX_FILE_SIZE' value='209715200'>
		<input type='hidden' name='posterWidth' value='$posterWidth'>
		<input type='hidden' name='posterLength' value='$posterLength'>
	<table class='table_1'>
		<tr><th colspan='3'>Paper Types</th></tr>
		<tr><td class='td_1' colspan='3'>Please choose a paper type for your poster.  The cost is per an inch.</td></tr>" . $paperTypesHTML . "
	</table>
	
	<br>
	<table class='table_1'>
		<tr><th colspan='3'>Finish Options</th></tr>
		<tr><td class='td_1' colspan='3'>Please choose a finish option for your poster.  The cost is a flat rate.</td></tr>" . $finishOptionsHTML . "
	</table>
	
	<br>
	<table class='table_1'>
		<tr><th colspan='3'>Other Options</th></tr>
		<tr><td class='td_1' colspan='3'>Please select any additional options.  Rush order will be done within 24 hours during the business week only.</td></tr>" . 
		$posterTubeHTML .
		$rushOrderHTML . "</table>
	
	<br>
	<table class='table_1'>
		<tr><th colspan='3'>Required Information</th></tr>
		<tr><td class='td_1' colspan='3'>Please fill in the following information.</td></tr>
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
			<td class='td_2'>Activity Code (optional):</td>
			<td class='td_3'><input type='text' size='6' name='activityCode' id='activityCode' maxlength='6'></td>
		</tr>
		<tr>
			<td class='td_2'>File (Max " . ini_get('post_max_size') . "):</td>
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
	$totalCost = ($posterLength * $paperTypeCost) +$finishOptionCost + ($posterTube * $posterTubeCost) + ($rushOrder * $rushOrderCost);
		
	//outputs the order information to confirm the order.
	$formHTML = "<center>
				
				<table class='table_1'>
						<tr><th colspan='2'>Review</th></tr>
						<tr><td class='td_1' colspan='2'>Please review your order below, then click \"Submit Order\" to send your order</td></tr>";
	if ($widthSwitched == 1) {
		$formHTML .= "<tr><td class='td_1' colspan='2'>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</td></tr>";
	
	}
	$formHTML .=	"<tr><td class='td_2'>Poster File:</td><td>" . $posterFileName . "</td></tr>
					<tr><td class='td_2'>Width:</td><td>" . $posterWidth . "\"</td></tr>
					<tr><td class='td_2'>Length:</td><td>" . $posterLength . "\"</td></tr>
					<tr><td class='td_2'>Paper Type:</td><td>" . $paperTypeName . "</td></tr>
					<tr><td class='td_2'>Finish Option:</td><td>" . $finishOptionName . "</td></tr>
					<tr><td class='td_2'>Poster Tube:</td><td>" . $posterTubeName . "</td></tr>
					<tr><td class='td_2'>Rush Order:</td><td>" . $rushOrderName . "</td></tr>
					<tr><td class='td_2'>Total Cost:</td><td>$" . $totalCost . "</td></tr>
					<tr><td class='td_2'>CFOP:</td><td>" . $cfop . "</td></tr>
					<tr><td class='td_2'>Activity Code:</td><td>" . $activityCode . "</td></tr>
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
								<input type='hidden' name='activityCode' value='$activityCode'>
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
        $sql .= "VALUES('" . $email . "','" . mysql_real_escape_string($name) . "',' ";
        $sql .= $posterFileName . "'," . $totalCost . ",'" . $cfop . "','";
        $sql .= $activityCode . "'," . $posterWidth . "," . $posterLength . ",'1',";
        $sql .= $paperTypesId . "," . $finishOptionsId . ",'" . mysql_real_escape_string($comments) . "',";
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
					<tr><td class='td_2'>Order Number:</td><td>" . $orderId . "</td></tr>
					<tr><td class='td_2'>File:</td><td>" . $posterFileName . "</td></tr>
					<tr><td class='td_2'>Length:</td><td>" . $posterLength . "\"</td></tr>
					<tr><td class='td_2'>Width:</td><td>" . $posterWidth . "\"</td></tr>
					<tr><td class='td_2'>Paper Type:</td><td>" . $paperTypeName . "</td></tr>
					<tr><td class='td_2'>Finish Option:</td><td>" . $finishOptionName . "</td></tr>
					<tr><td class='td_2'>Poster Tube:</td><td>" . $posterTubeName . "</td></tr>
					<tr><td class='td_2'>Rush Order:</td><td>" . $rushOrderName . "</td></tr>
					<tr><td class='td_2' valign='top'>Comments:</td><td>" . $comments . "</td>
					<tr><td class='td_2'>CFOP:</td><td>" . $cfop . "</td></tr>
					<tr><td class='td_2'>Activity Code:</td><td>" . $activityCode . "</td></tr>
					<tr><td class='td_2'>Total Cost:</td><td>$" . $totalCost . "</td></tr>
					<tr><td class='td_1' colspan='2'>If you have any questions please contact us at " . admin_email ."</td></tr>
			</table>
			</center>";
					
}

elseif (enable == FALSE) {
	$formHTML = "<br>
			<center>The poster printer is currently broken, soon maintenance should arrive to take care of the problem.  In the mean time, we are not accepting any new poster orders.  Please accept our apologies.</center>";

}
else {

	$paperTypes = getPaperTypes($db);

	$paperTypesHTML;
        for ($i=0;$i < count($paperTypes);$i++) {
        	$cost = $paperTypes[$i]['paperTypes_cost'];
		$name = $paperTypes[$i]['paperTypes_name'];
		$maxWidth = $paperTypes[$i]['paperTypes_width'];
                $paperTypesHtml .= "<tr>";
		$paperTypesHtml .= "<td class='td_2'>$" . $cost . "</td>";
		$paperTypesHtml .= "<td class='td_2'>" .  $name . "</td>";
		$paperTypesHtml .= "<td class='td_4'>" . $maxWidth . "''</td>";
                $paperTypesHtml .= "</tr>";
        }	
	$formHTML = "<br>
				
	<center>
	<form action='index.php' method='post' id='posterInfo' onsubmit='return validateStep1()' name='posterInfo'>
	<input type='hidden' name='maxPrinterWidth' value='" . max_printer_width . "'>
	<table class='table_1'>
		<tr><th colspan='2'>Paper Size</th></tr>
		<tr><td colspan='2' class='td_1' width='400'>Please choose a width and length for your poster.  The width maximum is " . max_printer_width . " inches.</td></tr>
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
	

	<table class='table_1'>
	<tr><th colspan='3'>Available Paper Types</th></tr>
	<tr><td class='td_1' colspan='3'>Below are the available paper types along with the maximum width for that type of paper. The cost is per an inch.</td></tr>
	$paperTypesHtml	
	</table>


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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css" media='screen'>

<script type="text/javascript" src="includes/poster.inc.js"></script>

<title>Poster Printer Submit Page</title>
</head>

<body OnLoad="document.posterInfo.posterWidth.focus();">
<div id='container'>
<div id="content_center">
<h2>Poster Printer Order Submit Form</h2>

<?php echo $formHTML; ?>

</div>
</div>
</body>

</html>
