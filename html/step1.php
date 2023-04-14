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


require_once 'includes/header.inc.php';

?>

<form action='' method='post' id='posterInfo' name='posterInfo' enctype='multipart/form-data'>
<fieldset id='poster_field'>
<input type='hidden' name='session' id='session' value='<?php echo $session->get_session_id(); ?>'>
<div class='row'>
<table class='table table-bordered table-sm'>
<thead>
<tr><th colspan='2'>Paper Size</th></tr>
<tr><td colspan='2'><em>Please choose a width and length for your poster.  The maximum width is <?php echo poster::get_max_poster_width($db); ?> inches.</em></td></tr>
</thead>
<tr><td class='text-right' style='vertical-align:middle;'>Width:</td>
<td class='left'>
<div class='input-group col-md-5'>
	<input class='form-control' text='text' name='width' id='width' maxlength='6' size='6'><div class='input-group-append' tabindex='1'>
	<span class='input-group-text'>&nbsp; Inches</span></div></div>
</td></tr>
<tr><td class='text-right' style='vertical-align:middle;'>Length:</td>
<td class='left'>
	<div class='input-group col-md-5'>
		<input class='form-control' type='text' name='length' id='length' maxlength='6' size='6'><div class='input-group-append' tabindex='2'>
		<span class='input-group-text'>&nbsp; Inches</span></div></div>
</td></tr>

</table>
</div>

<div class='row'>
	<div class='mx-auto btn-toolbar'>
		<p><button class='btn btn-warning' type='submit' name='cancel'>Cancel</button>
		<button class='btn btn-primary' type='submit' name='step1' onClick='first_step()'>Next</button>
		</p>
	</div>
</div>
</fieldset>
</form>
<div id='message'>
<?php if (isset($message)) { echo $message; } ?>
</div>

<?php require_once 'includes/footer.inc.php'; ?>

