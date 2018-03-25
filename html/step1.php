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


if (isset($_POST['step1'])) {
	$result = poster::verify_dimensions($db,$_POST['width'],$_POST['length']);

	if (!$result['RESULT']) {
		$message = functions::alert($result['MESSAGE'],$result['RESULT']);

	}
	else {
		$session_vars = array('width'=>$_POST['width'],'length'=>$_POST['length']);
		$session->set_session($session_vars);
		$url = "step2.php?session=" . $session->get_session_id();
		header('Location: ' . $url);

	}

}
elseif (isset($_POST['cancel'])) {
	$session->destroy_session();
	header('Location: index.php');

}



require_once 'includes/header.inc.php';

?>
<br><form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post' id='posterInfo' name='posterInfo'>
<fieldset id='poster_field'>
<table class='table table-bordered table-sm'>
<thead>
<tr><th colspan='2'>Paper Size</th></tr>
<tr><td colspan='2'><em>Please choose a width and length for your poster.  The maximum width is <?php echo poster::get_max_poster_width($db); ?> inches.</em></td></tr>
</thead>
<tr><td class='text-right' style='vertical-align:middle;'>Width:</td>
<td class='left'>
<div class='input-group col-md-5'><input class='form-control' text='text' name='width' id='width' maxlength='6' size='6'><div class='input-group-append'><span class='input-group-text'>&nbsp; Inches</span></div></div></td></tr>
<tr><td class='text-right' style='vertical-align:middle;'>Length:</td>
<td class='left'>
<div class='input-group col-md-5'><input class='form-control' type='text' name='length' id='length' maxlength='6' size='6'><div class='input-group-append'><span class='input-group-text'>&nbsp; Inches</span></div></div></td></tr>

</table>
<br>

<div class='row mx-auto' style='width: 200px'>
<div class='btn-toolbar'><p>
<button class='btn btn-warning' type='submit' name='cancel'>Cancel</button>
<button class='btn btn-primary' type='submit' name='step1'>Next</button>
</p>
</div>
</div>
</fieldset>
</form>

<?php if (isset($message)) { echo $message; } ?>

<?php require_once 'includes/footer.inc.php'; ?>
