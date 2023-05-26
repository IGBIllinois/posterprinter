<?php

require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

$paperTypes_html = "";
$finishOptions_html = "";
$posterTube_html = "";
$rushOrder_html = "";

if (isset($_POST['cancel'])) {
        $session->destroy_session();
        header('Location: index.php');
}

if (isset($_POST['step1'])) {


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
	//header('Location: index.php');
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
				<div class='row'>
				<div class='col-md-2'><input type='text' name='cfop1' id='cfop1' maxlength='1' class='form-control' onKeyUp='cfopAdvance1()'></div> - 
				<div class='col-md-3'><input type='text' name='cfop2' id='cfop2' maxlength='6' class='form-control' onKeyUp='cfopAdvance2()'></div> - 
				<div class='col-md-3'><input type='text' name='cfop3' id='cfop3' maxlength='6' class='form-control' onKeyUp='cfopAdvance3()'></div> - 
				<div class='col-md-3'><input type='text' name='cfop4' id='cfop4' maxlength='6' class='form-control'></div>
				</div>
			</td>
		</tr>
		<tr>
			<td class='text-right' style='vertical-align:middle;'>Activity Code (optional)</td>
			<td><div class='row'><div class='col-md-3'><input type='text' class='form-control' name='activityCode' id='activityCode' maxlength='6'></div></div></td>
		</tr>
		<tr>
			<td class='text-right' style='vertical-align:middle;'>File (Max <?php echo ini_get('post_max_size'); ?>)</td>
			<td><div class='custom-file'><input class='custom-file-input' type='file' name='posterFile' id='posterFile' onChange='update_posterfile_name()'>
			<label class="custom-file-label" id='posterfile-label' for="posterFile">Choose File...</label>
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
	<div class='progress w-100' style="height: 30px;">
	<div id='progress_bar' class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' 
		aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'>
	</div>
	</div>
</div>
<p></p>
<div class='row'>
	<div class='mx-auto btn-toolbar'>
		<button class='btn btn-warning' type='submit' name='cancel' id='cancel'>Cancel</button>&nbsp;
		<button class='btn btn-primary' type='submit' name='step2' id='step2'>Next</button>
	</div>
</div>
</fieldset>
</form>
<p></p>
	<div id='message'>
		<?php if (isset($message)) { echo $message; } ?>
	</div>
<?php require_once 'includes/footer.inc.php'; ?>

<script type="application/javascript">
$( document ).ready(function() {
        $('#step2').on('click', function(event) {
                disableForm();
                var width = document.getElementById('width').value;
                var length = document.getElementById('length').value;
		var session = document.getElementById('session').value;
		var paperTypesId = document.querySelector('input[name="paperTypesId"]:checked').value;
		var finishOptionsId = document.querySelector('input[name="finishOptionsId"]:checked').value;
		var cfop1 = document.getElementById('cfop1').value;
		var cfop2 = document.getElementById('cfop2').value;
		var cfop3 = document.getElementById('cfop3').value;
		var cfop4 = document.getElementById('cfop4').value;
		var activityCode = document.getElementById('activityCode').value;
		var email = document.getElementById('email').value;
		var additional_emails = document.getElementById('additional_emails').value;
		var name = document.getElementById('name').value;
		var comments = document.getElementById('comments').value;
		var posterTube = document.getElementById('posterTube').checked;
		var rushOrder = document.getElementById('rushOrder').checked;
		var session = document.getElementById('session').value;
		var posterFile = document.getElementById('posterFile');
		var formData = new FormData();
		formData.append('step2','1');
		formData.append('width',width);
		formData.append('length',length);
		formData.append('paperTypesId',paperTypesId);
		formData.append('finishOptionsId',finishOptionsId);
		formData.append('cfop1',cfop1);
		formData.append('cfop2',cfop2);
		formData.append('cfop3',cfop3);
		formData.append('cfop4',cfop4);
		formData.append('activityCode',activityCode);
		formData.append('email',email);
		formData.append('additional_emails',additional_emails);
		formData.append('name',name);
		formData.append('comments',comments);
		formData.append('posterTube',posterTube);
		formData.append('rushOrder',rushOrder);
		formData.append('posterFile',posterFile.files[0],posterFile.files[0].name);

		$.ajax({
			xhr: function() {
				var xhr = new window.XMLHttpRequest();
			        // Upload progress
				xhr.upload.addEventListener("progress", function(evt){
					if (evt.lengthComputable) {
						var percentComplete = Math.round(evt.loaded * 100 / evt.total);
						document.getElementById('progress_bar').innerHTML = "Uploading and Processing File: " + percentComplete.toString() + "%";
						document.getElementById('progress_bar').style= "width: " + percentComplete.toString() + "%;";
						document.getElementById('progress_bar').getAttribute("aria-valuenow").value = percentComplete.toString();

					}
				}, false);


				return xhr;

			},
                        url: 'create.php',
                        type: 'POST',
                        data: formData,
			dataType: 'json',
			processData: false,
                        contentType: false,
                        enctype: 'multipart/form-data',
                        success: function(response) {
				if (response.valid) {
                                	var parameters = response.post;
					var form = $('<form></form>');
					form.attr('method','post');
					form.attr('action','step3.php?session=' + session);
					$.each(parameters,function(key,value) {
						var field = $('<input></input>');
						field.attr("type", "hidden");
						field.attr("name", key);
						field.attr("value", value);
						form.append(field);
					});
					$(document.body).append(form);
					form.submit();


				}
				else {
					document.getElementById("message").innerHTML =  response.message;
                                        enableForm();
				}

                        },
                        error: function(response) {
                                document.getElementById("message").innerHTML =  response.message;
                                enableForm();
                        }

                });

        return false;

        });
});
</script>


