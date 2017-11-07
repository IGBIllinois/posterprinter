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
require_once 'includes/main.inc.php';
require_once 'mail.inc.php';
require_once 'orders.inc.php';
require_once 'paperTypes.inc.php';
require_once 'finishOptions.inc.php';
require_once 'posterTube.inc.php';
require_once 'rushOrder.inc.php';


//poster width and length submission
if (isset($_POST['step1'])) {
	foreach ($_POST as $var) {
		$var = trim(rtrim($var));
	}
	$posterWidth = $_POST['posterWidth'];
	$posterLength = $_POST['posterLength'];

	$result = poster::verify_dimensions($db,$posterWidth,$posterLength);
	if (!$result['RESULT']) {
		unset($_POST);
		$message = functions::alert($result['MESSAGE'],$result['RESULT']);
	} 
	else {

	$paperTypes = getValidPaperTypes($db,$posterWidth,$posterLength);
	//takes the result and formats it into html into the paperTypeHTML variable.
	$paperTypes_html = "";
	foreach ($paperTypes as $paperType) {
		$paperTypes_html .= "<tr>";
		$paperTypes_html .= "<td class='text-right'>$" . $paperType['cost'] . "</td>";
		$paperTypes_html .= "<td>" .  $paperType['name'] . "</td>";
		if (($paperType['paperTypes_default']) || (count($paperTypes) == 1)) {
			$paperTypes_html .= "<td class='left'><input type='radio' name='paperTypesId' checked='true' value='" . $paperType['id'] . "'></td></tr>\n";
		}
		else {
			$paperTypes_html .= "<td class='left'><input type='radio' name='paperTypesId' value='" . $paperType['id'] . "'></td></tr>\n";
		}

	}


	$finishOptions = getValidFinishOptions($db,$posterWidth,$posterLength);
	//takes the result and formats it into html into the finishOptionsHTML variable.
	$finishOptions_html = "";
	foreach ($finishOptions as $finishOption) {
		$finishOptions_html .= "<tr>";
		$finishOptions_html .= "<td class='text-right'>$" . $finishOption['cost'] . "</td>\n";
		$finishOptions_html .= "<td class='center'>" . $finishOption['name'] . "</td>\n";
		if (($finishOption['finishOptions_default']) || (count($finishOptions))) {
			$finishOptions_html .= "<td class='left'> <input type='radio' name='finishOptionsId' checked='checked' value='" . $finishOption['id'] . "'></td></tr>\n";
		}
		else {
			$finishOptions_html .= "<td class='left'> <input type='radio' name='finishOptionsId' value='" . $finishOption['id'] . "'></td></tr>\n";
		}

	}
	$posterTube_html = "<tr><td class='right'>Poster Tube</td><td class='right'>$" . getPosterTubeCost($db) . "</td>\n";
	$posterTube_html .= "<td class='left'><input type='checkbox' id='posterTube' name='posterTube' value='1'></td></tr>\n";

	$rushOrder_html = "<tr><td class='right'>Rush Order</td><td class='right'>$" . getRushOrderCost($db) ."</td>\n";
	$rushOrder_html .= "<td class='left'><input type='checkbox' id='rushOrder' name='rushOrder' value='1'></td></tr>\n";

	$form_html = "<br \>
			
	<br>
	<form action='' method='post' id='posterInfo' enctype='multipart/form-data'>\n
	<fieldset id='poster_field'>
		<input type='hidden' id='posterWidth' name='posterWidth' value='" . $posterWidth . "'>
		<input type='hidden' id='posterLength' name='posterLength' value='" . $posterLength . "'>
	<table class='table table-bordered table-condensed table-hover'>\n
		<tr><th colspan='3'>Paper Types</th></tr>\n
		<tr><td colspan='3'><em>Please choose a paper type for your poster.  The cost is per an inch.</em></td></tr>" . $paperTypes_html . "
	</table>\n
	
	<br>\n
	<table class='table table-bordered table-condensed table-hover'>\n
		<tr><th colspan='3'>Finish Options</th></tr>\n
		<tr><td colspan='3'><em>Please choose a finish option for your poster.  The cost is a flat rate.</em></td></tr>" . $finishOptions_html . "
	</table>\n
	
	<br>\n
	<table class='table table-bordered table-condensed table-hover'>\n
		<tr><th colspan='3'>Other Options</th></tr>\n
		<tr><td colspan='3'><em>Please select any additional options.  Rush orders will be done within <strong>" . settings::get_rush_order_timeframe() . "  business hours</strong>.</em></td></tr>" . $posterTube_html .

	$rushOrder_html . "</table>
	
	<br>
	<table class='table table-bordered table-condensed'>\n
		<tr><th colspan='3'>Required Information</th></tr>\n
		<tr><td colspan='3'><em>Please fill in the following information.</em></td></tr>\n
		<tr>\n
			<td class='text-right' style='vertical-align:middle;'>Full Name</td>\n
			<td><input class='form-control' type='text' size='29' name='name' id='name'></td>\n
		</tr>\n
		<tr>\n
			<td class='text-right' style='vertical-align:middle;'>Email</td>\n
			<td><input class='form-control' type='text' size='29' name='email' id='email'></td>\n
		</tr>\n
		<tr>\n
			<td class='text-right' style='vertical-align:middle;'>Additional Emails</td>\n
			<td><input class='form-control' type='text' name='additional_emails' id='additional_emails'></td>\n
		</tr>\n
		<tr>\n
			<td class='text-right' style='vertical-align:middle;'>CFOP Number</td>\n
			<td>\n
				<div class='col-md-2'><input type='text' name='cfop1' id='cfop1' maxlength='1' class='form-control' onKeyUp='cfopAdvance1()'></div>\n
				<div class='col-md-3'><input type='text' name='cfop2' id='cfop2' maxlength='6' size='6' class='form-control' onKeyUp='cfopAdvance2()'></div>\n
				<div class='col-md-3'><input type='text' name='cfop3' id='cfop3' maxlength='6' size='6' class='form-control' onKeyUp='cfopAdvance3()'></div>\n
				<div class='col-md-3'><input type='text' name='cfop4' id='cfop4' maxlength='6' size='6' class='form-control'></div>\n
			</td>\n
		</tr>\n
		<tr>\n
			<td class='text-right' style='vertical-align:middle;'>Activity Code (optional)</td>\n
			<td><div class='col-md-3'><input type='text' class='form-control' name='activityCode' id='activityCode' maxlength='6'></div></td>\n
		</tr>\n
		<tr>\n
			<td class='text-right' style='vertical-align:middle;'>File (Max " . ini_get('post_max_size') . ")</td>\n
			<td><input class='file' type='file' name='posterFile' id='posterFile'></td>\n
		</tr>\n
		<tr>\n
			<td class='text-right'>Comments</td>\n
			<td><textarea class='form-control' id='comments' name='comments' rows='3' cols='33'></textarea></td>\n
		</tr>\n
	</table>\n
	<div class='row'>
	<div class='progress' style='height: 2em;'>
                <div id='progress_bar' class='progress-bar progress-bar-striped active' role='progressbar' 
		aria-valuenow='0' aria-valuemin='0' aria-valuemax='100' style='width: 0%;'>
		</div>
        </div>
	</div>
	<br>
	<div class='row'><p class='text-center'>
		<button class='btn btn-warning' type='button' onclick='window.location.href=window.location.href'>Cancel</button>
		<button class='btn btn-primary' type='submit' name='step2' onClick='uploadFile()'>Next</button>
	</p></div>
	</fieldset>
	</form>
	<div id='message'>&nbsp;</div>\n";
	}

}

//paper type, finish option, cfop, and file submission, confirms order
elseif (isset($_POST['step2'])) {
        foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }

	$posterWidth = $_POST['posterWidth'];
	$posterLength = $_POST['posterLength'];
	$paperTypesId = $_POST['paperTypesId'];
	$finishOptionsId = $_POST['finishOptionsId'];
	$cfop1 = $_POST['cfop'];
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

//	$posterFileTmpName = poster::move_tmp_file($_FILES['posterFile']['name'],$_FILES['posterFile']['tmp_name']);
//	$result = poster::create_image(settings::get_poster_dir() . "/" . $posterFileTmpName);
//	$posterThumbFileTmpName = "";
//	if ($result['RESULT']) {
//		$posterThumbFileTmpName = $result['THUMB'];
//	}
	


	//Gets Finish Options Information
	$finishOptionsResult = getFinishOption($db,$finishOptionsId);
	$finishOptionCost = $finishOptionsResult[0]['finishOptions_cost'];
	$finishOptionName = $finishOptionsResult[0]['finishOptions_name'];


	//Gets Paper Type Information
	$paperType = getPaperType($db,$paperTypesId);
	$paperTypeCost = $paperType[0]['paperTypes_cost'];
	$paperTypeName = $paperType[0]['paperTypes_name'];
	$paperTypeWidth = $paperType[0]['paperTypes_width'];

	$widthSwitched = 0;
	if (poster::switch_dimensions($posterWidth,$posterLength,$paperTypeWidth)) {
                $tempPosterWidth = $posterWidth;
                $posterWidth = $posterLength;
                $posterLength = $tempPosterWidth;
		$widthSwitched = 1;	
	}

	$posterTubeResult = getPosterTubeStuff($db,$posterTube);
	$posterTubeCost =  $posterTubeResult["cost"];
	$posterTubeName =  $posterTubeResult["name"];
	$posterTubeId = $posterTubeResult["id"];

	$rushOrderResult = getRushOrderStuff($db,$rushOrder);
	$rushOrderCost = $rushOrderResult["cost"];
	$rushOrderName = $rushOrderResult["name"];
	$rushOrderId = $rushOrderResult["id"];

	//Calculates Total Cost
	$totalCost = ($posterLength * $paperTypeCost) + $finishOptionCost + ($posterTube * $posterTubeCost) + ($rushOrder * $rushOrderCost);

	//outputs the order information to confirm the order.
	$form_html = "<table class='table table-bordered table-condensed'>";
	$form_html .= "<tr><th colspan='2'>Review Your Order</th></tr>";
	$form_html .= "<tr><td colspan='2'><em>Please review your order below, then click \"Submit Order\" to send your order</em></td></tr>";

	if ($widthSwitched == 1) {
		$form_html .= "<tr><td colspan='2' class='description'>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</td></tr>";

	}
	$form_html .= "<tr><td class='text-right'>Poster File</td><td>" . $posterFileName . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Width</td><td>" . $posterWidth . "\"</td></tr>";
	$form_html .= "<tr><td class='text-right'>Length</td><td>" . $posterLength . "\"</td></tr>";
	$form_html .= "<tr><td class='text-right'>Paper Type</td><td>" . $paperTypeName . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Finish Option</td><td>" . $finishOptionName . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Poster Tube</td><td>" . $posterTubeName . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Rush Order</td><td>" . $rushOrderName . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Total Cost</td><td>$" . $totalCost . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>CFOP</td><td>" . $cfop . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Activity Code</td><td>" . $activityCode . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Email</td><td>" . $email . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Full Name</td><td>" . stripslashes($name) . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Comments</td><td>" . stripslashes($comments) . "</td></tr>";
	if (($posterThumbFileTmpName != "") && (file_exists(settings::get_poster_dir() . "/". $posterThumbFileTmpName))) {
		$form_html .= "<tr><td colspan='2'><img class='img-responsive img-thumbnail' src='" . settings::get_poster_dir() . "/" . $posterThumbFileTmpName . "'></td></tr>";
	}
	else {
		$form_html .= "<tr><td colspan='2'>No Preview</td></tr>";
	}
	$form_html .= "</table>";
	$form_html .= "<br><form method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
	$form_html .= "<input type='hidden' name='posterWidth' value='" . $posterWidth . "'>";
	$form_html .= "<input type='hidden' name='posterLength' value='" . $posterLength . "'>";
	$form_html .= "<input type='hidden' name='paperTypesId' value='" . $paperTypesId . "'>";
	$form_html .= "<input type='hidden' name='finishOptionsId' value='" . $finishOptionsId . "'>";
	$form_html .= "<input type='hidden' name='totalCost' value='" . $totalCost . "'>";
	$form_html .= "<input type='hidden' name='cfop' value='" . $cfop . "'>";
	$form_html .= "<input type='hidden' name='activityCode' value='" . $activityCode . "'>";
	$form_html .= "<input type='hidden' name='email' value='" . $email . "'>";
	$form_html .= "<input type='hidden' name='additional_emails' value='" . $_POST['additional_emails'] . "'>";
	$form_html .= "<input type='hidden' name='name' value='" . htmlspecialchars($name,ENT_QUOTES) . "'>";
	$form_html .= "<input type='hidden' name='comments' value='" . htmlspecialchars($comments,ENT_QUOTES) . "'>";
	$form_html .= "<input type='hidden' name='posterTubeId' value='" . $posterTubeId . "'>";
	$form_html .= "<input type='hidden' name='rushOrderId' value='" . $rushOrderId . "'>";
	$form_html .= "<input type='hidden' name='posterFileName' value='" . $posterFileName . "'>";
	$form_html .= "<input type='hidden' name='posterFileTmpName' value='" . $posterFileTmpName . "'>";
	$form_html .= "<input type='hidden' name='widthSwitched' value='" . $widthSwitched . "'>";
	$form_html .= "<div class='row'><p class='text-center'>";
	$form_html .= "<button class='btn btn-warning' type='button' onclick='window.location.href=window.location.href''>Cancel</button> ";
	$form_html .= "<button class='btn btn-primary' type='submit' name='step3'>Submit Order</button>";
	$form_html .= "</p></div>";
	$form_html .= "</form>";

}

//sends the order
elseif (isset($_POST['step3'])) {
	$posterWidth = $_POST['posterWidth'];
	$posterLength = $_POST['posterLength'];
	$paperTypesId = $_POST['paperTypesId'];
	$finishOptionsId = $_POST['finishOptionsId'];
	$posterTubeId = $_POST['posterTubeId'];
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

	$thumb_posterFileTmpName = "thumb_" . pathinfo($_POST['posterFileTmpName'],PATHINFO_FILENAME) . ".jpg";
	$fullsize_posterFileTmpName = "fullsize_" . pathinfo($_POST['posterFileTmpName'],PATHINFO_FILENAME) . ".jpg";
	
	$orderId = functions::create_order($db,$_POST);


	//gets the file type (ie .jpg, .bmp) of the uploaded poster file.
	$fileType = poster::get_filetype($posterFileName);
	$filename = $orderId . "." . $fileType;
	$thumb_filename = "thumb_" . $orderId . ".jpg";
	$fullsize_filename = "fullsize_" . $orderId . ".jpg";

	//renames the temporary file to its permanent file name which is the orderId number plus the filetype extensions.
	if (file_exists(poster_dir . "/" . $posterFileTmpName)) {
		rename(poster_dir . "/" . $posterFileTmpName,poster_dir . "/" . $filename);
	}
	if (file_exists(poster_dir . "/" . $thumb_posterFileTmpName)) {
		rename(poster_dir . "/" . $thumb_posterFileTmpName,poster_dir . "/" . $thumb_filename);
	}
	if (file_exists(poster_dir . "/" . $fullsize_posterFileTmpName)) {
		rename(poster_dir . "/" . $fullsize_posterFileTmpName,poster_dir . "/" . $fullsize_filename);
	}

	//mail new order to users and admins
	mailNewOrder($db,$orderId,settings::get_admin_email());

	$order = new order($db,$orderId);
	$form_html = "<table class='table table-bordered table-condensed'>";
	$form_html .= "<tr><th colspan='2'>Order Information</td></tr>";
	$form_html .= "<tr><td colspan='2'><em>Thank you for your order.  Your order will be completed within <strong>" . settings::get_order_timeframe() . " business hours</strong>. ";
	$form_html .= "If it is a rush order, it will be completed within <strong>" . settings::get_rush_order_timeframe() . " business hours</strong>. ";
	$form_html .= "An email has been sent to you at " . $email . " with this information. We will email you when the poster is completed printing.</em></td></tr>";

	if ($widthSwitched == 1) {
		$form_html .= "<tr><td colspan='2'><em>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</em></td></tr>";
	}

	$form_html .= "	<tr><td class='text-right'>Full Name</td><td>" . $order->get_name() . "</td></tr>";
	$form_html .= " <tr><td class='text-right'>Email</td><td>" . $order->get_email() . "</td></tr>";
	$form_html .= " <tr><td class='text-right'>Additional Emails</td><td>" . $order->get_cc_emails() . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Order Number</td><td>" . $orderId . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>File</td><td>" . $order->get_filename() . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Length</td><td>" . $order->get_length() . "\"</td></tr>";
	$form_html .= "<tr><td class='text-right'>Width</td><td>" . $order->get_width() . "\"</td></tr>";
	$form_html .= "<tr><td class='text-right'>Paper Type</td><td>" .  $order->get_paper_type_name() . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Finish Option</td><td>" .  $order->get_finish_option_name() . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Poster Tube</td><td>" . $order->get_poster_tube_name() . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Rush Order</td><td>" .  $order->get_rush_order_name() . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Comments</td><td>" .  $order->get_comments() . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>CFOP</td><td>" .  $order->get_cfop() . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Activity Code</td><td>" . $order->get_activity_code() . "</td></tr>";
	$form_html .= "<tr><td class='text-right'>Total Cost</td><td>$" . $order->get_total_cost() . "</td></tr>";
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
	$form_html = "<br><form action='index.php' method='post' id='posterInfo' name='posterInfo'>\n";
	$form_html .= "<fieldset id='poster_field''>\n";
	$form_html .= "<input type='hidden' name='maxPrinterWidth' value='" . settings::get_max_width() . "'>\n";
	$form_html .= "<table class='table table-bordered table-condensed'>\n";
	$form_html .= "<tr><th colspan='2'>Paper Size</th></tr>\n";
	$form_html .= "<tr><td colspan='2'><em>Please choose a width and length for your poster.  The width maximum is " . settings::get_max_width() . " inches.</em></td></tr>\n";
	$form_html .= "<tr><td class='text-right' style='vertical-align:middle;'>Width:</td>\n";
	$form_html .= "<td class='left'>\n";
	$form_html .= "<div class='input-group col-md-3'><input class='form-control' text='text' name='posterWidth' id='posterWidth' maxlength='6' size='6'><span class='input-group-addon'>Inches</span></div></td></tr>\n";
	$form_html .= "<tr><td class='text-right' style='vertical-align:middle;'>Length:</td>\n";
	$form_html .= "<td class='left'>\n";
	$form_html .= "<div class='input-group col-md-3'><input class='form-control' type='text' name='posterLength' id='posterLength' maxlength='6' size='6'><span class='input-group-addon'>Inches</span></div></td></tr>\n";
	$form_html .= "</table>\n";
	$form_html .= "<br>\n";
	$form_html .= "<table class='table table-bordered table-condensed table-striped'>\n";
	$form_html .= "<tr><th colspan='3'>Available Paper Types</th></tr>\n";
	$form_html .= "<tr><td colspan='3'><em>Below are the available paper types along with the maximum width for that type of paper. The cost is per an inch.</em></td></tr>\n";
	$form_html .= $paperTypes_html;
	$form_html .= "</table>\n";
	$form_html .= "<div class='row'><p class='text-center'>\n";
	$form_html .= "<button class='btn btn-warning' type='button' onclick='window.location.href=window.location.href'>Cancel</button> ";
	$form_html .= "<button class='btn btn-primary' type='submit' name='step1'>Next</button>\n";
	$form_html .= "</p></div>\n";
	$form_html .= "</fieldset>\n";
	$form_html .= "</form>\n";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
<link rel="stylesheet" type="text/css" href="includes/bootstrap-3.3.5-dist/css/bootstrap.min.css">

<script type='text/javascript' src='includes/jquery-2.1.4.min.js'></script>
<script type="text/javascript" src="includes/poster.inc.js"></script>

<title><?php echo settings::get_title(); ?></title>

</head>
<body OnLoad="document.posterInfo.posterWidth.focus();" style='padding-top: 60px;'>
<nav class="navbar navbar-inverse navbar-fixed-top">
	 <div class='navbar-header'>
                        <a class='navbar-brand' href='#'><?php echo settings::get_title(); ?></a>
                </div>
	<p class='navbar-text pull-right'>Version <?php echo app_version; ?></p>
</nav>

<div class='container-fluid'>
	<div class='col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4'>

		<?php if (isset($form_html)) { echo $form_html; } ?>

		<div id='message'>
			<?php if (isset($message)) { echo $message; } ?>
		</div>
	</div>	
	<div class='navbar navbar-fixed-bottom' style='text-align: center'>
	<strong><em>If you have any questions, please contact <?php echo settings::get_admin_email(); ?>.</em></strong>

	</div>
</div>
</body>

</html>
