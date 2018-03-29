<?php

require_once 'includes/main.inc.php';

$paperTypes_html = "";
$finishOptions_html = "";
$posterTube_html = "";
$rushOrder_html = "";

if (isset($_POST['cancel'])) {
        $session->destroy_session();
        header('Location: index.php');
}

elseif ((isset($_GET['session'])) && ($_GET['session'] == $session->get_session_id())) {

	$session_vars = $session->get_all_vars();

	$paperTypes = functions::getValidPaperTypes($db,$_POST['width'],$_POST['length']);
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
	$finishOptions = functions::getValidFinishOptions($db,$_POST['width'],$_POST['length']);
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
	$posterTube_html = "<tr><td class='right'>Poster Tube</td><td class='right'>$" . poster_tube::getPosterTubeCost($db) . "</td>\n";
	$posterTube_html .= "<td class='left'><input type='checkbox' id='posterTube' name='posterTube' value='1'></td></tr>\n";
	$rushOrder_html = "<tr><td class='right'>Rush Order</td><td class='right'>$" .rush_order:: getRushOrderCost($db) ."</td>\n";
	$rushOrder_html .= "<td class='left'><input type='checkbox' id='rushOrder' name='rushOrder' value='1'></td></tr>\n";

}
else {
	$session->destroy_session();
	header('Location: index.php');
}

require_once 'includes/header.inc.php';

?>
	<form action='' method='post' id='posterInfo' enctype='multipart/form-data'>
	<fieldset id='poster_field'>
		<input type='hidden' id='width' name='width' value='<?php echo $_POST['width']; ?>'>
		<input type='hidden' id='length' name='length' value='<?php echo  $_POST['length']; ?>'>
		<input type='hidden' id='session' name='session' value='<?php echo $_GET['session']; ?>'>
<div class='row'>
	<table class='table table-bordered table-sm table-hover'>
		<thead>
		<tr><th colspan='3'>Paper Types</th></tr>
		<tr><td colspan='3'><em>Please choose a paper type for your poster.  The cost is per an inch.</em></td></tr>
		</thead>
		<?php echo $paperTypes_html; ?>

	</table>
</div>
<div class='row'>	
	<table class='table table-bordered table-sm table-hover'>
		<thead>
		<tr><th colspan='3'>Finish Options</th></tr>
		<tr><td colspan='3'><em>Please choose a finish option for your poster.  The cost is a flat rate.</em></td></tr>
		</thead>
		<?php echo $finishOptions_html; ?>
	</table>
</div>	
<div class='row'>
	<table class='table table-bordered table-sm table-hover'>
		<thead>
		<tr><th colspan='3'>Other Options</th></tr>
		<tr><td colspan='3'><em>Please select any additional options.  Rush orders will be completed within <strong><?php echo settings::get_rush_order_timeframe(); ?> business hours</strong>.</em></td></tr>
	</thead>
	<?php echo $posterTube_html; ?>
	<?php echo $rushOrder_html; ?>
	</table>
</div>
<div class='row'>
	<table class='table table-bordered table-sm'>
		<thead>
		<tr><th colspan='3'>Required Information</th></tr>
		<tr><td colspan='3'><em>Please fill in the following information.</em></td></tr>
		</thead>
		<tr>
			<td class='text-right' style='vertical-align:middle;'>Full Name</td>
			<td><input class='form-control' type='text' size='29' name='name' id='name'></td>
		</tr>
		<tr>
			<td class='text-right' style='vertical-align:middle;'>Email</td>
			<td><input class='form-control' type='text' size='29' name='email' id='email'></td>
		</tr>
		<tr>
			<td class='text-right' style='vertical-align:middle;'>Additional Emails</td>
			<td><input class='form-control' type='text' name='additional_emails' id='additional_emails'></td>
		</tr>
		<tr>
			<td class='text-right' style='vertical-align:middle;'>CFOP Number</td>
			<td>
				<div class='form-group row'>
				<div class='col-md-2'><input type='text' name='cfop1' id='cfop1' maxlength='1' class='form-control' onKeyUp='cfopAdvance1()'></div> - 
				<div class='col-md-3'><input type='text' name='cfop2' id='cfop2' maxlength='6' size='6' class='form-control' onKeyUp='cfopAdvance2()'></div> - 
				<div class='col-md-3'><input type='text' name='cfop3' id='cfop3' maxlength='6' size='6' class='form-control' onKeyUp='cfopAdvance3()'></div> - 
				<div class='col-md-3'><input type='text' name='cfop4' id='cfop4' maxlength='6' size='6' class='form-control'></div>
				</div>
			</td>
		</tr>
		<tr>
			<td class='text-right' style='vertical-align:middle;'>Activity Code (optional)</td>
			<td><div class='col-md-3'><input type='text' class='form-control' name='activityCode' id='activityCode' maxlength='6'></div></td>
		</tr>
		<tr>
			<td class='text-right' style='vertical-align:middle;'>File (Max <?php echo ini_get('post_max_size'); ?>)</td>
			<td><div class='custom-file'><input class='form-control-file' type='file' name='posterFile' id='posterFile' onChange='update_posterfile_name()'>
			<label class="custom-file-label" id='posterfile-label' for="posterFile">Choose file</label>
			</div>
			</td>
		</tr>
		<tr>
			<td class='text-right'>Comments</td>
			<td><textarea class='form-control' id='comments' name='comments' rows='3' cols='33'></textarea></td>
		</tr>
	</table>
</div>
<div class='row'>
	<div class='progress col-md-12 col-lg-12 col-xl-12' style="height: 30px;">
	<div id='progress_bar' class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' 
		aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'>
	</div>
	</div>
</div>
<p></p>
<div class='row'>
	<div class='mx-auto btn-toolbar'>
		<button class='btn btn-warning' type='submit' name='cancel'>Cancel</button>&nbsp;
		<button class='btn btn-primary' type='submit' name='step2' onClick='second_step()'>Next</button>
	</div>
</div>
</fieldset>
</form>
<p></p>
	<div id='message'>
		<?php if (isset($message)) { echo $message; } ?>
	</div>
<?php require_once 'includes/footer.inc.php'; ?>


