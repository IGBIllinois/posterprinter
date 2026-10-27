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
		<tr><td colspan='3'><em>Please select your desired pick up date (next business day at the earliest).
		Rush order fee applies if pickup is needed within 24 business hours
		(Mon 8am - Fri 4:30pm, excluding weekends and holidays).
		Same-day pickup cannot be ordered online &mdash; for same-day requests email
		<a href='mailto:help@igb.illinois.edu'>help@igb.illinois.edu</a> and we will try to accommodate.</em></td></tr>
		</thead>
		<tbody>
		<tr>
			<td class='text-end'>Poster Tube</td>
			<td class='text-end' id='posterTubeCost'></td>
			<td><input type='checkbox' id='posterTube' name='posterTube' value='1'></td>
		</tr>
		<tr>
			<td class='text-end' style='vertical-align:middle;'>Pick Up Date</td>
			<td class='text-end'><input type='date' class='form-control' id='pickupDate' name='pickupDate'></td>
			<td></td>
		</tr>
		<tr id='rushOrderRow' style='display:none; background-color:#ffcccc;'>
			<td class='text-end'>Rush Order Fee</td>
			<td class='text-end' id='rushOrderCost'></td>
			<td><span style='color:red; font-weight:bold;'>APPLIED</span></td>
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
			<td><textarea class='form-control' id='comments' name='comments' rows='3' maxlength='500'></textarea>
			<div class='form-text text-end'><span id='commentsCount'>0</span> / 500</div></td>
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

	// ---- pickup date / rush (server decides) ----------------------------
	(function () {
		// Earliest self-service pickup: next business day. No same-day online.
		var d = new Date();
		d.setDate(d.getDate() + 1);
		while (d.getDay() === 0 || d.getDay() === 6) d.setDate(d.getDate() + 1);
		var iso = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
		$id('pickupDate').setAttribute('min', iso);
	})();

	$id('pickupDate').addEventListener('change', async function () {
		var val = $id('pickupDate').value;
		$id('rushOrderRow').style.display = 'none';
		if (!val) return;
		try {
			var fd = new FormData();
			fd.append('pickupDate', val);
			var r = await api('rushcheck', fd);
			if (!r.success) {
				$id('pickupDate').value = '';
				showMessage(r.message);
				return;
			}
			showMessage('');
			$id('rushOrderRow').style.display = r.rush ? 'table-row' : 'none';
		} catch (err) { showMessage(alertBox(err.message)); }
	});

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
		if (PREFILL.pickupDate) {
			$id('pickupDate').dispatchEvent(new Event('change'));
		}
	})();
})();
</script>
