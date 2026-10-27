<?php
//////////////////////////////////////////////////////
//
//  Poster Printer - order.php
//  Single order form. Replaces step1.php + step2.php.
//
//  Flow: upload file -> dimensions detected server-side
//  (PDF/JPG/PNG/TIFF via Imagick, PPTX via slide size) ->
//  user checks the confirm box -> paper/finish options load
//  -> submit -> review.php
//
//  Dimensions are NEVER user-editable. Files whose
//  dimensions cannot be read are rejected at upload.
//
//  If a staged order exists in the session (user clicked
//  "Previous Page" on review.php), the form is prefilled.
//
//////////////////////////////////////////////////////

require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

if (isset($_POST['cancel'])) {
    $up = $session->get_var('poster_upload');
    if (!empty($up['tmpName'])) {
        @unlink(poster::get_tmp_path() . '/' . basename($up['tmpName']));
    }
    $session->destroy_session();
    header('Location: index.php');
    exit;
}

$session_id = htmlspecialchars($_GET['session'] ?? '', ENT_QUOTES);

// Prefill support: returning from review.php with a staged order.
$staged  = $session->get_var('poster_order');
$upload  = $session->get_var('poster_upload');
$prefill = 'null';
if ($staged && $upload && !empty($upload['width'])) {
    $prefill = json_encode([
        'fileName'          => $upload['origName'],
        'width'             => $upload['width'],   // pre-rotation, as detected
        'length'            => $upload['height'],
        'paperTypesId'      => $staged['paperTypesId'],
        'finishOptionsId'   => $staged['finishOptionsId'],
        'posterTube'        => $staged['posterTube'],
        'pickupDate'        => $staged['pickupDate'],
        'pickupSlot'        => $staged['pickupSlot'] ?? '',
        'name'              => $staged['name'],
        'email'             => $staged['email'],
        'additional_emails' => $staged['additional_emails'],
        'activityCode'      => $staged['activityCode'],
        'comments'          => $staged['userComments'] ?? '',
        'pi'                => $staged['pi'] ?? '',
        'event'             => $staged['event'] ?? '',
        'cfop'              => explode('-', $staged['cfop']),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: 'null';
}

require_once 'includes/header.inc.php';
?>
<form action='' method='post' id='posterInfo' enctype='multipart/form-data'>
<fieldset id='poster_field'>
<div class='row'>
	<table class='table table-bordered table-sm'>
		<thead class='thead-dark'>
		<tr><th colspan='2'>Poster File</th></tr>
		<tr><td colspan='2'><em>Upload your poster file (Max <?php echo ini_get('post_max_size'); ?>).
		Dimensions are detected automatically from the file.</em></td></tr>
		</thead>
		<tr>
			<td class='text-end' style='vertical-align:middle; width:25%;'>File</td>
			<td>
				<!-- The native file input is hidden; the Browse button opens it. -->
				<input class='d-none' type='file' name='posterFile' id='posterFile'>
				<div class='d-flex align-items-center justify-content-between'>
					<span id='currentFile' class='text-muted'>No file selected</span>
					<button type='button' class='btn btn-outline-secondary btn-sm' id='browseFile'>Browse...</button>
				</div>
				<div class='progress mt-2' style='height: 24px; display:none;' id='uploadProgressWrap'>
					<div id='uploadProgress' class='progress-bar progress-bar-striped progress-bar-animated'
						role='progressbar' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'></div>
				</div>
			</td>
		</tr>
		<tr id='dimensionsRow' style='display:none;'>
			<td class='text-end' style='vertical-align:middle;'>Dimensions</td>
			<td>
				<div class='row align-items-end'>
					<div class='col-md-3'>
						<label for='width' class='form-label'>Width (in)</label>
						<input type='text' class='form-control' id='width' readonly style='background-color:#f0f0f0;'>
					</div>
					<div class='col-md-3'>
						<label for='length' class='form-label'>Length (in)</label>
						<input type='text' class='form-control' id='length' readonly style='background-color:#f0f0f0;'>
					</div>
					<div class='col-md-6'>
						<span id='dimensionStatus' class='fst-italic' style='color:#666;'></span>
					</div>
				</div>
				<div class='form-check mt-2'>
					<input class='form-check-input' type='checkbox' id='dimensionsConfirmed' value='1'>
					<label class='form-check-label' for='dimensionsConfirmed'>
						<strong>I confirm these dimensions are correct for my poster.</strong>
					</label>
				</div>
			</td>
		</tr>
	</table>
</div>

<div id='optionsSection' style='display:none;'>
<div class='row'>
	<table class='table table-bordered table-sm table-hover'>
		<thead class='thead-dark'>
		<tr><th colspan='4'>Paper Types</th></tr>
		<tr><td colspan='4'><em>Please choose a paper type for your poster.
		Papers narrower than your poster's smaller dimension are not shown.</em></td></tr>
		<tr><th class='text-end'>$ / inch</th><th>Paper Type</th><th>Max Width</th><th></th></tr>
		</thead>
		<tbody id='paperTypesBody'></tbody>
	</table>
</div>
<div class='row'>
	<table class='table table-bordered table-sm table-hover'>
		<thead class='thead-dark'>
		<tr><th colspan='3'>Finish Options</th></tr>
		<tr><td colspan='3'><em>Please choose a finish option for your poster.  The cost is a flat rate.</em></td></tr>
		</thead>
		<tbody id='finishOptionsBody'></tbody>
	</table>
</div>
<div class='row'>
	<table class='table table-bordered table-sm'>
		<thead class='thead-dark'>
		<tr><th colspan='3'>Other Options</th></tr>
		<tr><td colspan='3'><em>Please select your pick up date and a morning or afternoon pickup. Standard orders are ready in about 3 business days;
		<strong>Standard</strong> picks the earliest standard pickup. Earlier pickups (shown in red) are rush orders
		and a rush fee applies (rush = pickup within 24 business hours, Mon 8am - Fri 4:30pm, excluding weekends and holidays).
		Same-day pickup cannot be ordered online &mdash; for same-day requests email
		<a href='mailto:help@igb.illinois.edu'>help@igb.illinois.edu</a> and we will try to accommodate.</em></td></tr>
		</thead>
		<tbody>
		<tr>
			<td class='text-end align-middle' style='width: 9rem;'>Poster Tube</td>
			<td class='align-middle' id='posterTubeCost'></td>
			<td class='text-center align-middle' style='width: 8rem;'><input type='checkbox' id='posterTube' name='posterTube' value='1'
				style='margin: 0; vertical-align: middle; width: 1.1rem; height: 1.1rem; cursor: pointer;'></td>
		</tr>
		<tr>
			<td class='text-end align-middle'>Pick Up Date</td>
			<td class='align-middle'>
				<div class='d-flex align-items-center flex-wrap' style='gap: 0.5rem 1rem;'>
				<input type='text' class='form-control form-control-sm' id='pickupDate' name='pickupDate'
					readonly placeholder='Choose a date' style='width: 8.5rem; background-color: #fff; cursor: pointer;'>
				<div class='d-flex align-items-center' style='gap: 0.5rem;'>
				<span id='slotPrompt' class='small text-muted'>Choose time:</span>
				<div class='btn-group btn-group-sm' role='group' aria-label='Pickup time'>
					<button type='button' class='btn btn-outline-secondary slot-btn' data-slot='morning' disabled>Morning</button>
					<button type='button' class='btn btn-outline-secondary slot-btn' data-slot='afternoon' disabled>Afternoon</button>
				</div>
				</div>
				</div>
			</td>
			<td class='text-center align-middle'><button type='button' class='btn btn-outline-primary btn-sm' id='standardDate'
				title='Pick the earliest date that is not a rush order'>Standard</button></td>
		</tr>
		<tr id='rushOrderRow' style='display:none; background-color:#f8d7da;'>
			<td class='text-end'>Rush Order Fee</td>
			<td id='rushOrderCost'></td>
			<td class='text-center'><span style='color:#b02a37; font-weight:bold;'>APPLIED</span></td>
		</tr>
		</tbody>
	</table>
</div>
</div>

<div class='row'>
	<table class='table table-bordered table-sm'>
		<thead class='thead-dark'>
		<tr><th colspan='2'>Required Information</th></tr>
		<tr><td colspan='2'><em>Please fill in the following information.</em></td></tr>
		</thead>
		<tr>
			<td class='text-end' style='vertical-align:middle; width:25%;'>Full Name</td>
			<td><input class='form-control' type='text' name='name' id='name' maxlength='50'></td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>Email</td>
			<td><input class='form-control' type='text' name='email' id='email' maxlength='30'></td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>Additional Emails</td>
			<td><input class='form-control' type='text' name='additional_emails' id='additional_emails' maxlength='255'></td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>CFOP Number</td>
			<td>
				<div class='row'>
				<div class='col-md-2'><input type='text' name='cfop1' id='cfop1' maxlength='1' class='form-control cfop'></div> -
				<div class='col-md-3'><input type='text' name='cfop2' id='cfop2' maxlength='6' class='form-control cfop'></div> -
				<div class='col-md-3'><input type='text' name='cfop3' id='cfop3' maxlength='6' class='form-control cfop'></div> -
				<div class='col-md-3'><input type='text' name='cfop4' id='cfop4' maxlength='6' class='form-control cfop'></div>
				</div>
			</td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>Activity Code (optional)</td>
			<td><div class='row'><div class='col-md-3'><input type='text' class='form-control' name='activityCode' id='activityCode' maxlength='6'></div></div></td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>PI (optional)</td>
			<td><input class='form-control' type='text' name='pi' id='pi' maxlength='100'></td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>Event (optional)</td>
			<td><input class='form-control' type='text' name='event' id='event' maxlength='100' placeholder='Conference or event this poster is for'></td>
		</tr>
		<tr>
			<td class='text-end'>Comments</td>
			<td><textarea class='form-control' id='comments' name='comments' rows='3' maxlength='300'></textarea>
			<div class='form-text text-end'><span id='commentsCount'>0</span> / 300</div></td>
		</tr>
	</table>
</div>
<p></p>
<div class='row'>
	<div class='mx-auto btn-toolbar'>
		<button class='btn btn-warning' type='submit' name='cancel' id='cancel' formnovalidate>Cancel Order</button>&nbsp;
		<button class='btn btn-primary' type='button' id='submitOrder'>Next</button>
	</div>
</div>
</fieldset>
</form>
<p class='text-center text-muted mt-3'>Have questions? Reach out to <a href='mailto:help@igb.illinois.edu'>help@igb.illinois.edu</a></p>
<div id='message'></div>
<style>
	/* Pickup calendar: rush days shaded red, plus a note under the calendar. */
	.ui-datepicker td.rush-day a { background: #f8d7da !important; border-color: #f1aeb5 !important; color: #58151c !important; }
	.ui-datepicker td.rush-day a.ui-state-active { background: #dc3545 !important; color: #fff !important; }
	.ui-datepicker td.rush-am a { background: linear-gradient(to right, #f8d7da 50%, #f6f6f6 50%) !important; }
	.slot-btn.slot-rush { background: #f8d7da; border-color: #f1aeb5; color: #58151c; }
	.slot-btn.slot-rush.active { background: #dc3545; border-color: #dc3545; color: #fff; }
	.ui-datepicker .rush-note { font-size: 0.8rem; padding: 0.35rem 0.25rem 0.15rem; color: #495057; }
	.ui-datepicker .rush-swatch { display: inline-block; width: 0.8rem; height: 0.8rem; background: #f8d7da;
		border: 1px solid #f1aeb5; margin-right: 0.35rem; vertical-align: -1px; }
</style>
<?php require_once 'includes/footer.inc.php'; ?>

<script type="application/javascript">
(function () {
	'use strict';

	var SESSION_ID = '<?php echo $session_id; ?>';
	var DIMENSIONS_DETECTED_TEXT = 'Dimensions detected automatically. To change them, edit your file and browse for it again.';
	var PREFILL = <?php echo $prefill; ?>;

	// ---- tiny helpers -------------------------------------------------
	var $id = function (i) { return document.getElementById(i); };
	var showMessage = function (html) {
		$id('message').innerHTML = html;
		if (html) $id('message').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	};
	var alertBox = function (text, cls) {
		return "<div class='alert alert-" + (cls || 'danger') + "'>" + text + "</div>";
	};

	// One function talks to the server. Progress callback used for uploads.
	function api(action, formData, onProgress) {
		return new Promise(function (resolve, reject) {
			var xhr = new XMLHttpRequest();
			xhr.open('POST', 'api.php');
			xhr.responseType = 'json';
			if (onProgress) {
				xhr.upload.addEventListener('progress', function (e) {
					if (e.lengthComputable) onProgress(Math.round(e.loaded * 100 / e.total));
				});
			}
			xhr.onload = function () {
				if (xhr.response && typeof xhr.response === 'object') {
					resolve(xhr.response);
				} else {
					reject(new Error('Server returned an invalid response (HTTP ' + xhr.status + ').'));
				}
			};
			xhr.onerror = function () { reject(new Error('Network error - please try again.')); };
			formData.append('action', action);
			formData.append('session', SESSION_ID);
			xhr.send(formData);
		});
	}

	// Live character count under Comments.
	function updateCommentsCount() { $id('commentsCount').textContent = $id('comments').value.length; }
	$id('comments').addEventListener('input', updateCommentsCount);

	// ---- state --------------------------------------------------------
	var fileUploaded = false;
	var optionsLoadedFor = '';

	function setUploadedFileUI(fileName) {
		$id('currentFile').textContent = fileName;
		$id('currentFile').classList.remove('text-muted');
	}
	function clearUploadedFileUI() {
		$id('currentFile').textContent = 'No file selected';
		$id('currentFile').classList.add('text-muted');
	}

	// Browse opens the hidden file input; choosing again replaces the file.
	$id('browseFile').addEventListener('click', function () {
		$id('posterFile').click();
	});

	// ---- file upload + dimension detection -----------------------------
	$id('posterFile').addEventListener('change', async function () {
		fileUploaded = false;
		optionsLoadedFor = '';
		$id('dimensionsRow').style.display = 'none';
		$id('optionsSection').style.display = 'none';
		$id('dimensionsConfirmed').checked = false;
		showMessage('');

		var input = $id('posterFile');
		if (!input.files || input.files.length === 0) return;

		var wrap = $id('uploadProgressWrap');
		var bar = $id('uploadProgress');
		wrap.style.display = 'flex';
		bar.style.width = '0%';
		bar.textContent = 'Uploading: 0%';

		var fd = new FormData();
		fd.append('posterFile', input.files[0], input.files[0].name);

		try {
			var r = await api('upload', fd, function (pct) {
				bar.style.width = pct + '%';
				bar.setAttribute('aria-valuenow', pct);
				bar.textContent = 'Uploading: ' + pct + '%';
			});
			wrap.style.display = 'none';
			if (!r.success) {
				input.value = '';
				clearUploadedFileUI();
				showMessage(r.message);
				return;
			}
			fileUploaded = true;
			setUploadedFileUI(input.files[0].name);
			$id('dimensionsRow').style.display = 'table-row';
			$id('width').value = r.width;
			$id('length').value = r.height;
			$id('dimensionStatus').textContent = DIMENSIONS_DETECTED_TEXT;
			loadOptions();
		} catch (err) {
			wrap.style.display = 'none';
			input.value = '';
			clearUploadedFileUI();
			showMessage(alertBox(err.message));
		}
	});

	// ---- options load (dimensions come only from detection) -------------
	async function loadOptions() {
		var w = parseFloat($id('width').value);
		var l = parseFloat($id('length').value);
		if (!fileUploaded || !(w > 0) || !(l > 0)) return false;
		var key = w + 'x' + l;
		if (key === optionsLoadedFor) return true;
		try {
			var fd = new FormData();
			fd.append('width', w);
			fd.append('length', l);
			var r = await api('options', fd);
			if (!r.success) {
				$id('optionsSection').style.display = 'none';
				optionsLoadedFor = '';
				showMessage(r.message);
				return false;
			}
			showMessage('');
			renderPaperTypes(r.paperTypes);
			renderFinishOptions(r.finishOptions);
			$id('posterTubeCost').textContent = '$' + r.posterTubeCost;
			$id('rushOrderCost').textContent = '$' + r.rushOrderCost;
			$id('optionsSection').style.display = 'block';
			optionsLoadedFor = key;
			applyLaminationRule();
			return true;
		} catch (err) {
			showMessage(alertBox(err.message));
			return false;
		}
	}

	function renderPaperTypes(list) {
		var body = $id('paperTypesBody');
		body.innerHTML = '';
		list.forEach(function (p) {
			var tr = document.createElement('tr');
			tr.innerHTML =
				"<td class='text-end' style='width:15%'></td>" +
				"<td></td>" +
				"<td style='width:15%'></td>" +
				"<td style='width:10%'></td>";
			tr.children[0].textContent = '$' + p.cost;
			tr.children[1].textContent = p.name;
			tr.children[2].textContent = (p.maxWidth != null ? p.maxWidth + "''" : '');
			var radio = document.createElement('input');
			radio.type = 'radio';
			radio.name = 'paperTypesId';
			radio.value = p.id;
			radio.checked = p.isDefault;
			radio.dataset.restrictLamination = p.restrictLamination ? '1' : '0';
			radio.addEventListener('change', applyLaminationRule);
			tr.children[3].appendChild(radio);
			body.appendChild(tr);
		});
	}

	function renderFinishOptions(list) {
		var body = $id('finishOptionsBody');
		body.innerHTML = '';
		list.forEach(function (f) {
			var tr = document.createElement('tr');
			tr.innerHTML =
				"<td class='text-end' style='width:15%'></td>" +
				"<td></td>" +
				"<td style='width:10%'></td>";
			tr.children[0].textContent = '$' + f.cost;
			tr.children[1].textContent = f.name;
			var radio = document.createElement('input');
			radio.type = 'radio';
			radio.name = 'finishOptionsId';
			radio.value = f.id;
			radio.checked = f.isDefault;
			radio.dataset.isLamination = f.isLamination ? '1' : '0';
			radio.dataset.isNone = f.isNone ? '1' : '0';
			tr.children[2].appendChild(radio);
			body.appendChild(tr);
		});
	}

	function applyLaminationRule() {
		var paper = document.querySelector("input[name='paperTypesId']:checked");
		var restrict = paper && paper.dataset.restrictLamination === '1';
		document.querySelectorAll("input[name='finishOptionsId']").forEach(function (radio) {
			var row = radio.closest('tr');
			if (restrict && radio.dataset.isLamination === '1') {
				if (radio.checked) radio.checked = false;
				radio.disabled = true;
				row.style.opacity = '0.5';
				row.style.cursor = 'not-allowed';
			} else {
				radio.disabled = false;
				row.style.opacity = '1';
				row.style.cursor = 'default';
			}
		});
		if (restrict && !document.querySelector("input[name='finishOptionsId']:checked")) {
			var none = document.querySelector("input[name='finishOptionsId'][data-is-none='1']");
			if (none) none.checked = true;
		}
	}

	// ---- pickup date + slot / rush (server decides) ---------------------
	// The server sends every selectable date with a rush flag per slot
	// (api.php action=calendar). The calendar only allows those dates
	// (weekends, holidays, today and past days are not clickable): a day is
	// red when both slots are rush, half red when only the morning is.
	// After a date is picked, Morning/Afternoon become available, each
	// shaded red if it is rush. Nothing is pre-selected; Standard picks the
	// earliest non-rush slot. The server re-checks rush on every pick and
	// again at submit, so a page left open overnight cannot get a stale price.
	var calendarDays = {};          // 'YYYY-MM-DD' => {morning: rush?, afternoon: rush?}
	var earliestStandard = null;    // {date, slot}
	var pickupSlot = '';

	async function loadCalendar() {
		var r = await api('calendar', new FormData());
		if (!r.success) throw new Error('Could not load pickup dates.');
		calendarDays = r.days;
		earliestStandard = r.earliestStandard;
		$('#pickupDate').datepicker('option', 'maxDate', r.lastDate);
		return r;
	}

	function addRushNote() {
		var dp = $('#ui-datepicker-div');
		if (!dp.find('.rush-note').length) {
			dp.append("<div class='rush-note'><span class='rush-swatch'></span>Red = rush order (fee applies). Half red = morning is rush, afternoon is not.</div>");
		}
	}

	$('#pickupDate').datepicker({
		dateFormat: 'yy-mm-dd',
		minDate: 1,                         // tomorrow; same-day is not offered online
		beforeShowDay: function (date) {
			var iso = $.datepicker.formatDate('yy-mm-dd', date);
			var day = calendarDays[iso];
			if (!day) return [false, '', ''];
			if (day.morning && day.afternoon) return [true, 'rush-day', 'Rush order'];
			if (day.morning) return [true, 'rush-am', 'Morning is rush; afternoon is standard'];
			return [true, '', 'Standard'];
		},
		beforeShow: function () { setTimeout(addRushNote, 0); },
		onChangeMonthYear: function () { setTimeout(addRushNote, 0); },
		onSelect: function () { pickupSlot = ''; refreshPickup(); }
	});

	// Shows rush shading on the two slot buttons and the rush fee row.
	function paintSlots(slots) {
		document.querySelectorAll('.slot-btn').forEach(function (b) {
			var name = b.dataset.slot;
			b.disabled = !slots;
			b.classList.toggle('slot-rush', !!(slots && slots[name]));
			b.classList.toggle('active', name === pickupSlot);
		});
		// Draw attention to "Choose time:" only while it still needs an answer.
		var needsSlot = !!slots && !pickupSlot;
		$id('slotPrompt').classList.toggle('fw-bold', needsSlot);
		$id('slotPrompt').classList.toggle('text-muted', !needsSlot);
	}

	// Asks the server about the chosen date (and slot, if any).
	async function refreshPickup() {
		var date = $id('pickupDate').value;
		$id('rushOrderRow').style.display = 'none';
		if (!date) { paintSlots(null); return; }
		try {
			var fd = new FormData();
			fd.append('pickupDate', date);
			fd.append('pickupSlot', pickupSlot);
			var r = await api('rushcheck', fd);
			if (!r.success) {
				$id('pickupDate').value = '';
				pickupSlot = '';
				paintSlots(null);
				showMessage(r.message);
				return;
			}
			showMessage('');
			paintSlots(r.slots);
			$id('rushOrderRow').style.display = (r.rush === true) ? 'table-row' : 'none';
		} catch (err) { showMessage(alertBox(err.message)); }
	}

	document.querySelectorAll('.slot-btn').forEach(function (b) {
		b.addEventListener('click', function () { pickupSlot = b.dataset.slot; refreshPickup(); });
	});

	$id('standardDate').addEventListener('click', async function () {
		try {
			await loadCalendar();               // fresh: the earliest standard slot moves as time passes
			if (earliestStandard) {
				$id('pickupDate').value = earliestStandard.date;
				pickupSlot = earliestStandard.slot;
				refreshPickup();
			}
			else { showMessage(alertBox('No standard pickup is available online right now. Please email help@igb.illinois.edu.')); }
		} catch (err) { showMessage(alertBox(err.message)); }
	});

	// Load selectable dates. No date is pre-selected: the choice is deliberate.
	loadCalendar().catch(function (err) { showMessage(alertBox(err.message)); });

	// ---- CFOP auto-advance ----------------------------------------------
	[['cfop1', 'cfop2'], ['cfop2', 'cfop3'], ['cfop3', 'cfop4']].forEach(function (pair) {
		$id(pair[0]).addEventListener('input', function () {
			if (this.value.length >= this.maxLength) $id(pair[1]).focus();
		});
	});

	// ---- submit -----------------------------------------------------------
	var submitting = false;
	$id('submitOrder').addEventListener('click', async function () {
		if (submitting) return;

		var errors = [];
		if (!fileUploaded) errors.push('Please select a file to upload.');
		if (!$id('dimensionsConfirmed').checked) errors.push('Please confirm the poster dimensions are correct.');
		if (!$id('pickupDate').value) errors.push('Please select a pickup date.');
		else if (!pickupSlot) errors.push('Please choose a morning or afternoon pickup.');
		if (!$id('name').value.trim()) errors.push('Please enter your full name.');
		if (!$id('email').value.trim()) errors.push('Please enter your email address.');
		if (!$id('cfop1').value.trim() || !$id('cfop2').value.trim() || !$id('cfop3').value.trim() || !$id('cfop4').value.trim()) {
			errors.push('Please enter a complete CFOP number.');
		}
		var paper = document.querySelector("input[name='paperTypesId']:checked");
		var finish = document.querySelector("input[name='finishOptionsId']:checked");
		if (!paper) errors.push('Please select a paper type.');
		if (!finish) errors.push('Please select a finish option.');

		if (errors.length) {
			showMessage(alertBox(errors.join('<br>')));
			return;
		}

		submitting = true;
		$id('submitOrder').disabled = true;
		$id('submitOrder').textContent = 'Processing...';

		var fd = new FormData();
		fd.append('dimensionsConfirmed', '1');
		fd.append('paperTypesId', paper.value);
		fd.append('finishOptionsId', finish.value);
		fd.append('posterTube', $id('posterTube').checked ? '1' : '0');
		fd.append('pickupDate', $id('pickupDate').value);
		fd.append('pickupSlot', pickupSlot);
		fd.append('name', $id('name').value);
		fd.append('email', $id('email').value);
		fd.append('additional_emails', $id('additional_emails').value);
		fd.append('cfop1', $id('cfop1').value);
		fd.append('cfop2', $id('cfop2').value);
		fd.append('cfop3', $id('cfop3').value);
		fd.append('cfop4', $id('cfop4').value);
		fd.append('activityCode', $id('activityCode').value);
		fd.append('comments', $id('comments').value);
		fd.append('pi', $id('pi').value);
		fd.append('event', $id('event').value);

		try {
			var r = await api('submit', fd);
			if (r.success) {
				window.location.href = r.redirect;
				return;
			}
			showMessage(r.message);
		} catch (err) {
			showMessage(alertBox(err.message));
		}
		submitting = false;
		$id('submitOrder').disabled = false;
		$id('submitOrder').textContent = 'Next';
	});

	// ---- prefill: returning from review.php via "Previous Page" ----------
	(async function bootstrap() {
		if (!PREFILL) return;
		fileUploaded = true;
		setUploadedFileUI(PREFILL.fileName);
		$id('dimensionsRow').style.display = 'table-row';
		$id('width').value = PREFILL.width;
		$id('length').value = PREFILL.length;
		$id('dimensionsConfirmed').checked = true; // was confirmed before review
		$id('dimensionStatus').textContent = DIMENSIONS_DETECTED_TEXT;

		if (!(await loadOptions())) return;

		var paper = document.querySelector("input[name='paperTypesId'][value='" + PREFILL.paperTypesId + "']");
		if (paper) { paper.checked = true; }
		applyLaminationRule();
		var finish = document.querySelector("input[name='finishOptionsId'][value='" + PREFILL.finishOptionsId + "']");
		if (finish && !finish.disabled) { finish.checked = true; }

		$id('posterTube').checked = PREFILL.posterTube == 1;
		['name', 'email', 'additional_emails', 'activityCode', 'comments', 'pi', 'event'].forEach(function (k) {
			$id(k).value = PREFILL[k] != null ? PREFILL[k] : '';
		});
		['cfop1', 'cfop2', 'cfop3', 'cfop4'].forEach(function (id, i) {
			$id(id).value = (PREFILL.cfop && PREFILL.cfop[i] != null) ? PREFILL.cfop[i] : '';
		});
		updateCommentsCount();
		$id('pickupDate').value = PREFILL.pickupDate || '';
		pickupSlot = PREFILL.pickupSlot || '';
		if (PREFILL.pickupDate) {
			refreshPickup();
		}
	})();
})();
</script>
