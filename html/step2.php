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
	
	// Determine which paper type is selected
	$selectedPaperTypeId = null;
	foreach ($paperTypes as $paperType) {
		if ($paperType['paperTypes_default']) {
			$selectedPaperTypeId = $paperType['id'];
			break;
		}
	}
	
	// Check if selected paper type is Graphic Matte Canvas (17, 18) or Fine Art Watercolor (7)
	$restrictLamination = in_array($selectedPaperTypeId, [17, 18, 7]);
	
	//takes the result and formats it into html into the paperTypeHTML variable.
	$paperTypes_html = "";
	foreach ($paperTypes as $paperType) {
		$paperTypes_html .= "<tr>";
		$paperTypes_html .= "<td class='text-end'>$" . $paperType['cost'] . "</td>";
		$paperTypes_html .= "<td>" .  $paperType['name'] . "</td>";
		
		// Add data attribute to identify special paper types
		$dataAttr = in_array($paperType['id'], [17, 18, 7]) ? " data-restrict-lamination='true'" : "";
		
		if ($paperType['paperTypes_default']) {
			$paperTypes_html .= "<td class='left'><input type='radio' name='paperTypesId' checked='true' value='" . $paperType['id'] . "'" . $dataAttr . "></td></tr>\n";
		}
		else {
			$paperTypes_html .= "<td class='left'><input type='radio' name='paperTypesId' value='" . $paperType['id'] . "'" . $dataAttr . "></td></tr>\n";
		}
	}
	
	$finishOptions = functions::getValidFinishOptions($db,$_POST['width'],$_POST['length']);
	
	//takes the result and formats it into html into the finishOptionsHTML variable.
	$finishOptions_html = "";
	foreach ($finishOptions as $finishOption) {
		$finishOptions_html .= "<tr>";
		$finishOptions_html .= "<td class='text-end'>$" . $finishOption['cost'] . "</td>\n";
		$finishOptions_html .= "<td class='center'>" . $finishOption['name'] . "</td>\n";
		
		// If this is lamination (id=2) and a restricted paper type is selected
		if ($finishOption['id'] == 2 && $restrictLamination) {
			// Disable lamination option and don't check it
			$finishOptions_html .= "<td class='left'><input type='radio' name='finishOptionsId' value='" . $finishOption['id'] . "' disabled='disabled'></td></tr>\n";
		}
		// If this is "none" (id=1) and lamination should be restricted
		elseif ($finishOption['id'] == 1 && $restrictLamination) {
			// Force "none" to be selected
			$finishOptions_html .= "<td class='left'><input type='radio' name='finishOptionsId' checked='checked' value='" . $finishOption['id'] . "'></td></tr>\n";
		}
		// Normal handling for other options
		elseif ($finishOption['finishOptions_default'] && !$restrictLamination) {
			$finishOptions_html .= "<td class='left'><input type='radio' name='finishOptionsId' checked='checked' value='" . $finishOption['id'] . "'></td></tr>\n";
		}
		else {
			$finishOptions_html .= "<td class='left'><input type='radio' name='finishOptionsId' value='" . $finishOption['id'] . "'></td></tr>\n";
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
		<thead class='thead-dark'>
		<tr><th colspan='3'>Paper Types</th></tr>
		<tr><td colspan='3'><em>Please choose a paper type for your poster.  The cost is per an inch.</em></td></tr>
		</thead>
		<?php echo $paperTypes_html; ?>

	</table>
</div>
<div class='row'>	
	<table class='table table-bordered table-sm table-hover'>
		<thead class='thead-dark'>
		<tr><th colspan='3'>Finish Options</th></tr>
		<tr><td colspan='3'><em>Please choose a finish option for your poster.  The cost is a flat rate.</em></td></tr>
		</thead>
		<?php echo $finishOptions_html; ?>
	</table>
</div>	
<div class='row'>
	<table class='table table-bordered table-sm table-hover'>
		<thead class='thead-dark'>
		<tr><th colspan='3'>Other Options</th></tr>
		<tr><td colspan='3'><em>Please select any additional options.  Rush orders will be completed within <strong><?php echo settings::get_rush_order_timeframe(); ?> business hours</strong>.</em></td></tr>
	</thead>
	<?php echo $posterTube_html; ?>
	<?php echo $rushOrder_html; ?>
	</table>
</div>
<div class='row'>
	<table class='table table-bordered table-sm'>
		<thead class='thead-dark'>
		<tr><th colspan='3'>Required Information</th></tr>
		<tr><td colspan='3'><em>Please fill in the following information.</em></td></tr>
		</thead>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>Full Name</td>
			<td><input class='form-control' type='text' size='29' name='name' id='name'></td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>Email</td>
			<td><input class='form-control' type='text' size='29' name='email' id='email'></td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>Additional Emails</td>
			<td><input class='form-control' type='text' name='additional_emails' id='additional_emails'></td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>CFOP Number</td>
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
			<td class='text-end' style='vertical-align:middle;'>Activity Code (optional)</td>
			<td><div class='row'><div class='col-md-3'><input type='text' class='form-control' name='activityCode' id='activityCode' maxlength='6'></div></div></td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>File (Max <?php echo ini_get('post_max_size'); ?>)</td>
			<td><div class='custom-file'><input class='form-control' type='file' name='posterFile' id='posterFile' onChange='update_posterfile_name()'>
			<label class="form-label" id='posterfile-label' for="posterFile">Choose File...</label>
			</div>
			</td>
		</tr>
		<tr>
			<td class='text-end'>Comments</td>
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
		<button class='btn btn-warning' type='submit' name='cancel' id='cancel'>Cancel Order</button>&nbsp;
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
// Paper Type and Finish Option Dynamic Update
document.addEventListener('DOMContentLoaded', function() {
	console.log('[posterprinter] DOMContentLoaded fired, binding paper type handlers');
	// Get all paper type radio buttons
	const paperTypeRadios = document.querySelectorAll('input[name="paperTypesId"]');
	console.log('[posterprinter] Found ' + paperTypeRadios.length + ' paper type radios');

	// Function to update finish options based on selected paper type
	function updateFinishOptions() {
		// Get the currently selected paper type
		const selectedPaperType = document.querySelector('input[name="paperTypesId"]:checked');

		if (!selectedPaperType) return;

		// Read restriction from the data attribute set by PHP — no hardcoded IDs
		const restrictLamination = (selectedPaperType.getAttribute('data-restrict-lamination') === 'true');
		console.log('[posterprinter] Paper type ' + selectedPaperType.value + ', restrictLamination=' + restrictLamination);

		// Get all finish option radio buttons
		const finishOptionRadios = document.querySelectorAll('input[name="finishOptionsId"]');

		finishOptionRadios.forEach(function(radio) {
			const finishOptionId = parseInt(radio.value);
			const row = radio.closest('tr');

			if (restrictLamination && finishOptionId === 2) {
				// Disable lamination
				radio.disabled = true;
				radio.checked = false;
				if (row) {
					row.style.opacity = '0.5';
					row.style.cursor = 'not-allowed';
				}
			} else {
				radio.disabled = false;
				if (row) {
					row.style.opacity = '1';
					row.style.cursor = 'default';
				}
				// If restricting, auto-select "None" (id=1)
				if (restrictLamination && finishOptionId === 1) {
					radio.checked = true;
				}
			}
		});
	}

	// Add event listeners to all paper type radio buttons
	paperTypeRadios.forEach(function(radio) {
		radio.addEventListener('change', updateFinishOptions);
	});

	// Run once on page load to set initial state
	updateFinishOptions();
});

// Existing jQuery code for form submission
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