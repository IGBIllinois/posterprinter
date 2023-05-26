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
require_once 'includes/session.inc.php';

if (isset($_POST['cancel'])) {
	$session->destroy_session();
	header('Location: index.php');
}

$session_id = $_GET['session'];
require_once 'includes/header.inc.php';


?>

<form action='' method='post' id='posterInfo' name='posterInfo' enctype='multipart/form-data'>
<fieldset id='poster_field'>
<input type='hidden' name='session' id='session' value='<?php echo $session_id; ?>'>
<div class='row'>
<table class='table table-bordered table-sm'>
<thead class='thead-dark'>
<tr><th colspan='2'>Paper Size</th></tr>
<tr><td colspan='2'><em>Please choose a width and length for your poster.  The maximum width is <?php echo poster::get_max_poster_width($db); ?> inches.</em></td></tr>
</thead>
<tr><td class='text-right' style='vertical-align:middle;'>Width:</td>
<td class='left'>
<div class='input-group col-md-5'>
<input class='form-control' text='text' name='width' id='width' maxlength='6' size='6' value='<?php if (isset($_POST['width'])) { echo $_POST['width']; } ?>'><div class='input-group-append' tabindex='1'>
	<span class='input-group-text'>&nbsp; Inches</span></div></div>
</td></tr>
<tr><td class='text-right' style='vertical-align:middle;'>Length:</td>
<td class='left'>
	<div class='input-group col-md-5'>
		<input class='form-control' type='text' name='length' id='length' maxlength='6' size='6' value='<?php if (isset($_POST['length'])) { echo $_POST['length']; } ?>'><div class='input-group-append' tabindex='2'>
		<span class='input-group-text'>&nbsp; Inches</span></div></div>
</td></tr>

</table>
</div>

<div class='row'>
	<div class='mx-auto btn-toolbar'>
		<p><button class='btn btn-warning' type='submit' name='cancel' id='cancel'>Cancel</button>
		<button class='btn btn-primary' type='submit' name='step1' id='step1'>Next</button>
		</p>
	</div>
</div>
</fieldset>
</form>
<div id='message'>
<?php if (isset($message)) { echo $message; } ?>
</div>

<?php require_once 'includes/footer.inc.php'; ?>

<script type="application/javascript">
$( document ).ready(function() {
	$('#step1').on('click', function(event) {
		disableForm();
		var width = document.getElementById('width').value;
		var length = document.getElementById('length').value;
		var session = document.getElementById('session').value;
		var formData = new FormData();
		formData.append('step1','1');
		formData.append('width',width);
		formData.append('length',length);
	        $.ajax({
        	        url: 'create.php',
                	type: 'POST',
			data: formData,
			dataType: 'json',
			processData: false,
			contentType: false,
			enctype: 'multipart/form-data',
			success: function(response) {
				if (!response.valid) {
					document.getElementById("message").innerHTML =  response.message;
					enableForm();
					
				}
				else {
					var url = 'step2.php?session=' + session;
					var form = $('<form action="' + url + '" method="post">' +
						'<input type="text" name="step1" value="1">' +
						'<input type="text" name="width" value="' + width + '">' +
						'<input type="text" name="length" value="' + length + '"></form>'
						);
					$('body').append(form);
					form.submit();
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
