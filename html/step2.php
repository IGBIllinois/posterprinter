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
	
	// Check if selected paper type is Graphic Matte Canvas (1) or Fine Art Watercolor (4)
	$restrictLamination = in_array($selectedPaperTypeId, [1, 4]);
	
	//takes the result and formats it into html into the paperTypeHTML variable.
	$paperTypes_html = "";
	foreach ($paperTypes as $paperType) {
		$paperTypes_html .= "<tr>";
		$paperTypes_html .= "<td class='text-end'>$" . $paperType['cost'] . "</td>";
		$paperTypes_html .= "<td>" .  $paperType['name'] . "</td>";
		
		// Add data attribute to identify special paper types
		$dataAttr = in_array($paperType['id'], [1, 4]) ? " data-restrict-lamination='true'" : "";
		
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
	
	// Modified rush order to be a pickup date selector
	$rushOrder_html = "<tr id='pickupDateRow'><td class='right'>Pick Up Date</td><td class='right'>";
	$rushOrder_html .= "<input type='date' class='form-control' id='pickupDate' name='pickupDate'>";
	$rushOrder_html .= "</td><td class='left'></td></tr>\n";
	$rushOrder_html .= "<tr id='rushOrderRow' style='display:none;'><td class='right'>Rush Order Fee</td>";
	$rushOrder_html .= "<td class='right' id='rushOrderCost'>$" . rush_order::getRushOrderCost($db) . "</td>\n";
	$rushOrder_html .= "<td class='left'><span id='rushOrderIndicator' style='color:red; font-weight:bold;'>APPLIED</span></td></tr>\n";
	$rushOrder_html .= "<input type='hidden' id='rushOrder' name='rushOrder' value='0'>\n";

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
		<tr><td colspan='3'><em>Please select your desired pick up date. Rush order fee applies if pickup is needed within 24 business hours (Mon 8am - Fri 4:30pm, excluding weekends and holidays).</em></td></tr>
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
		<tr id='dimensionsRow' style='display:none;'>
			<td class='text-end' style='vertical-align:middle;'>Detected Dimensions</td>
			<td>
				<div class='row'>
					<div class='col-md-3'>
						<label for='detectedWidth' class='form-label'>Width (inches)</label>
						<input type='text' class='form-control' id='detectedWidth' name='detectedWidth' readonly style='background-color:#f0f0f0;'>
					</div>
					<div class='col-md-3'>
						<label for='detectedHeight' class='form-label'>Height (inches)</label>
						<input type='text' class='form-control' id='detectedHeight' name='detectedHeight' readonly style='background-color:#f0f0f0;'>
					</div>
					<div class='col-md-6'>
						<span id='dimensionStatus' style='font-style:italic; color:#666;'></span>
					</div>
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
		<button class='btn btn-warning' type='submit' name='cancel' id='cancel' formnovalidate>Cancel Order</button>&nbsp;
		<button class='btn btn-primary' type='button' name='step2' id='step2'>Next</button>
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
	// Get all paper type radio buttons
	const paperTypeRadios = document.querySelectorAll('input[name="paperTypesId"]');
	
	// Function to update finish options based on selected paper type
	function updateFinishOptions() {
		// Get the currently selected paper type
		const selectedPaperType = document.querySelector('input[name="paperTypesId"]:checked');
		
		if (!selectedPaperType) return;
		
		const paperTypeId = parseInt(selectedPaperType.value);
		
		// Check if selected paper type is Graphic Matte Canvas (1) or Fine Art Watercolor (4)
		const restrictLamination = (paperTypeId === 1 || paperTypeId === 4);
		
		// Get all finish option radio buttons
		const finishOptionRadios = document.querySelectorAll('input[name="finishOptionsId"]');
		
		finishOptionRadios.forEach(function(radio) {
			const finishOptionId = parseInt(radio.value);
			
			if (restrictLamination) {
				// If lamination (id=2), disable it
				if (finishOptionId === 2) {
					radio.disabled = true;
					radio.checked = false;
					// Add visual styling to the row
					const row = radio.closest('tr');
					if (row) {
						row.style.opacity = '0.5';
						row.style.cursor = 'not-allowed';
					}
				}
				// If none (id=1), select it
				else if (finishOptionId === 1) {
					radio.disabled = false;
					radio.checked = true;
					const row = radio.closest('tr');
					if (row) {
						row.style.opacity = '1';
						row.style.cursor = 'default';
					}
				}
				// Other options remain enabled but unchecked
				else {
					radio.disabled = false;
					const row = radio.closest('tr');
					if (row) {
						row.style.opacity = '1';
						row.style.cursor = 'default';
					}
				}
			} else {
				// No restrictions - enable all options
				radio.disabled = false;
				const row = radio.closest('tr');
				if (row) {
					row.style.opacity = '1';
					row.style.cursor = 'default';
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
	
	// ============================================
	// Pickup Date and Rush Order Logic
	// ============================================
	
	const pickupDateInput = document.getElementById('pickupDate');
	const rushOrderRow = document.getElementById('rushOrderRow');
	const rushOrderHidden = document.getElementById('rushOrder');
	
	// List of holidays (you can modify this array as needed)
	// Format: 'YYYY-MM-DD'
	const holidays = [
		// Add your holidays here, for example:
		// '2025-01-01', // New Year's Day
		// '2025-07-04', // Independence Day
		// '2025-12-25', // Christmas
	];
	
	function isHoliday(date) {
		const dateStr = date.toISOString().split('T')[0];
		return holidays.includes(dateStr);
	}
	
	function isWeekend(date) {
		const day = date.getDay();
		return day === 0 || day === 6; // 0 = Sunday, 6 = Saturday
	}
	
	function addBusinessHours(startDate, hours) {
		let current = new Date(startDate);
		let hoursToAdd = hours;
		
		while (hoursToAdd > 0) {
			// Move to next hour
			current.setHours(current.getHours() + 1);
			
			// Skip weekends and holidays
			while (isWeekend(current) || isHoliday(current)) {
				current.setDate(current.getDate() + 1);
				current.setHours(8); // Start at 8am
			}
			
			// Only count hours within business hours (8am - 4:30pm)
			const hour = current.getHours();
			const minutes = current.getMinutes();
			const timeInMinutes = hour * 60 + minutes;
			
			// Business hours: 8:00am (480 min) to 4:30pm (16.5 * 60 = 990 min)
			if (timeInMinutes >= 480 && timeInMinutes < 990) {
				hoursToAdd--;
			} else if (timeInMinutes >= 990) {
				// If past 4:30pm, move to next business day at 8am
				current.setDate(current.getDate() + 1);
				current.setHours(8);
				current.setMinutes(0);
				
				// Skip weekends and holidays
				while (isWeekend(current) || isHoliday(current)) {
					current.setDate(current.getDate() + 1);
				}
			} else if (timeInMinutes < 480) {
				// If before 8am, set to 8am
				current.setHours(8);
				current.setMinutes(0);
			}
		}
		
		return current;
	}
	
	function checkRushOrder() {
		const selectedDate = pickupDateInput.value;
		
		if (!selectedDate) {
			rushOrderRow.style.display = 'none';
			rushOrderHidden.value = '0';
			return;
		}
		
		const pickupDateTime = new Date(selectedDate + 'T00:00:00');
		const now = new Date();
		
		// Calculate 24 business hours from now
		const rushDeadline = addBusinessHours(now, 24);
		
		// Check if pickup date is before the rush deadline
		if (pickupDateTime <= rushDeadline) {
			// Show rush order fee with red highlight
			rushOrderRow.style.display = 'table-row';
			rushOrderRow.style.backgroundColor = '#ffcccc';
			rushOrderHidden.value = '1';
		} else {
			// Hide rush order fee
			rushOrderRow.style.display = 'none';
			rushOrderRow.style.backgroundColor = '';
			rushOrderHidden.value = '0';
		}
	}
	
	// Set minimum date to next business day
	let minDate = new Date();
	while (isWeekend(minDate) || isHoliday(minDate)) {
		minDate.setDate(minDate.getDate() + 1);
	}
	pickupDateInput.setAttribute('min', minDate.toISOString().split('T')[0]);

	// Add event listener for pickup date changes - validate business days
	pickupDateInput.addEventListener('change', function() {
		const selectedDate = new Date(pickupDateInput.value + 'T00:00:00');
		
		// Validate it's a business day
		if (isWeekend(selectedDate) || isHoliday(selectedDate)) {
			alert('Please select a business day (Monday-Friday, excluding holidays).');
			pickupDateInput.value = '';
			rushOrderRow.style.display = 'none';
			rushOrderHidden.value = '0';
			return;
		}
		
		checkRushOrder();
	});

	// Check on page load if there's already a date selected
	checkRushOrder();

	// ============================================
	// Automatic Dimension Detection from File Upload
	// ============================================

	const posterFileInput = document.getElementById('posterFile');
	const dimensionsRow = document.getElementById('dimensionsRow');
	const detectedWidthInput = document.getElementById('detectedWidth');
	const detectedHeightInput = document.getElementById('detectedHeight');
	const dimensionStatus = document.getElementById('dimensionStatus');

	posterFileInput.addEventListener('change', function() {
		// Reset dimension fields
		detectedWidthInput.value = '';
		detectedHeightInput.value = '';
		dimensionStatus.textContent = '';
		dimensionsRow.style.display = 'none';

		// Check if a file was selected
		if (!posterFileInput.files || posterFileInput.files.length === 0) {
			return;
		}

		const file = posterFileInput.files[0];
		const fileName = file.name.toLowerCase();

		// Check if file type is supported for dimension extraction
		const supportedExtensions = ['pdf', 'jpg', 'jpeg', 'tif', 'tiff', 'png'];
		const fileExt = fileName.split('.').pop();

		if (!supportedExtensions.includes(fileExt)) {
			// Don't show dimensions for unsupported file types (like PPT/PPTX)
			return;
		}

		// Show loading status
		dimensionsRow.style.display = 'table-row';
		dimensionStatus.textContent = 'Extracting dimensions...';
		dimensionStatus.style.color = '#666';

		// Create FormData and send to get_dimensions.php
		const formData = new FormData();
		formData.append('file', file);

		fetch('get_dimensions.php', {
			method: 'POST',
			body: formData
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				detectedWidthInput.value = data.width;
				detectedHeightInput.value = data.height;
				dimensionStatus.textContent = 'Dimensions detected automatically';
				dimensionStatus.style.color = '#28a745'; // Green
			} else {
				dimensionStatus.textContent = 'Could not extract dimensions: ' + data.message;
				dimensionStatus.style.color = '#dc3545'; // Red
			}
		})
		.catch(error => {
			dimensionStatus.textContent = 'Error detecting dimensions';
			dimensionStatus.style.color = '#dc3545'; // Red
			console.error('Dimension extraction error:', error);
		});
	});
});

// Existing jQuery code for form submission
$( document ).ready(function() {
        $('#step2').on('click', function(event) {
                // Collect form values first
                var pickupDate = document.getElementById('pickupDate').value;
                var name = document.getElementById('name').value.trim();
                var email = document.getElementById('email').value.trim();
                var cfop1 = document.getElementById('cfop1').value.trim();
                var cfop2 = document.getElementById('cfop2').value.trim();
                var cfop3 = document.getElementById('cfop3').value.trim();
                var cfop4 = document.getElementById('cfop4').value.trim();
                var posterFile = document.getElementById('posterFile');
                
                // Validate required fields BEFORE disabling form
                var errors = [];
                
                if (!pickupDate) {
                        errors.push('Please select a pickup date.');
                }
                if (!name) {
                        errors.push('Please enter your full name.');
                }
                if (!email) {
                        errors.push('Please enter your email address.');
                }
                if (!cfop1 || !cfop2 || !cfop3 || !cfop4) {
                        errors.push('Please enter a complete CFOP number.');
                }
                if (!posterFile.files || posterFile.files.length === 0) {
                        errors.push('Please select a file to upload.');
                }
                
                // If there are errors, show them and don't proceed
                if (errors.length > 0) {
                        document.getElementById("message").innerHTML = '<div class="alert alert-danger">' + errors.join('<br>') + '</div>';
                        return false;
                }
                
                disableForm();
                
                // Use detected dimensions if available, otherwise fall back to step1 values
                var detectedWidth = document.getElementById('detectedWidth').value;
                var detectedHeight = document.getElementById('detectedHeight').value;
                var width = detectedWidth ? detectedWidth : document.getElementById('width').value;
                var length = detectedHeight ? detectedHeight : document.getElementById('length').value;
		var session = document.getElementById('session').value;
		var paperTypesId = document.querySelector('input[name="paperTypesId"]:checked').value;
		var finishOptionsId = document.querySelector('input[name="finishOptionsId"]:checked').value;
		var activityCode = document.getElementById('activityCode').value;
		var additional_emails = document.getElementById('additional_emails').value;
		var comments = document.getElementById('comments').value;
		var posterTube = document.getElementById('posterTube').checked;
		var rushOrder = document.getElementById('rushOrder').value;
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
		formData.append('pickupDate',pickupDate);
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