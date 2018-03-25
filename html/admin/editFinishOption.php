<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';


if (isset($_POST['removeFinishOption'])) {
	$finishoption = new finishoption($db,$_POST['finishOptionId']);	
	$finishoption->delete();	
	header("Location: finishOptions.php");

}
elseif (isset($_POST['makeDefault'])) {
	$finishoption = new finishoption($db,$_POST['finishOptionId']);
	$finishoption->set_default();
	header("Location: finishOptions.php");
}
elseif (isset($_POST['editFinishOption'])) {
	
	$finishOptionId = $_POST['finishOptionId'];
	
	$name = trim(rtrim($_POST['name']));
	$cost =trim(rtrim( $_POST['cost']));
	$maxWidth = trim(rtrim($_POST['maxWidth']));
	$maxLength = trim(rtrim($_POST['maxLength']));
	$finishoption = new finishoption($db,$_POST['finishOptionId']);
	$default = $finishoption->get_default();
	$result = $finishoption->update($name,$cost,$maxWidth,$maxLength);
	if ($result['RESULT']) { header("Location: finishOptions.php"); }
}

elseif (isset($_GET['finishOptionId'])) {
	$finishOptionId = $_GET['finishOptionId'];
	$finishoption = new finishoption($db,$finishOptionId);
	if ($finishoption->get_available()) {
		$name = $finishoption->get_name();
		$cost = $finishoption->get_cost();
		$maxWidth = $finishoption->get_max_width();
		$maxLength = $finishoption->get_max_length();
		$available = $finishoption->get_available();
		$default = $finishoption->get_default();
	}
	else {
		$result['MESSAGE'] = functions::alert("Invalid Finish Option",0);
	}
	
}

include 'includes/header.inc.php';
?>

<script>
function confirmDelete()
{
var agree=confirm("Are you sure you wish to delete?");
if (agree)
	return true ;
else
	return false ;
}

function confirmDefault()
{
var agree=confirm("Are you sure you wish to make this default?");
if (agree)
	return true ;
else
	return false ;
}
function confirmUpdate()
{
var agree=confirm("Are you sure you wish to update?");
if (agree)
	return true ;
else
	return false ;
}
</script>
<form method='post' action='editFinishOption.php?finishOptioinId=<?php echo $finishOptionId; ?>'>
<input type='hidden' name='finishOptionId' value='<?php echo $finishOptionId; ?>' />
<table class='table table-bordered table-sm'>
	<tr><th colspan='2'>Edit Finish Option</th></tr>
	<tr>
		<td class='text-right'>Name:</td>
		<td><div class='input-group col-md-6'><input class='form-control' type='text' name='name' value='<?php echo $name; ?>' maxlength='40'/></div></td>
	</tr>
	<tr>
		<td class='text-right'>Cost:</td>
		<td><div class='input-group col-xs-3'><div class='input-group-prepend'><span class='input-group-text'>$</span></div><input class='form-control' type='text' name='cost' value='<?php echo $cost; ?>' size='6'></div></td>
	</tr>
	<tr>
		<td class='text-right'>Max Width:</td>
		<td><div class='input-group col-xs-3'><input class='form-control' type='text' name='maxWidth' value='<?php echo $maxWidth; ?>' maxlength='2' size='3'><div clas='input-group-append'><span class='input-group-text'>Inches</span><div></div></td>
	</tr>
	<tr>
		<td class='text-right'>Max Length:</td>
		<td><div class='input-group col-xs-3'><input class='form-control' type='text' name='maxLength' value='<?php echo $maxLength; ?>' maxlength='3' size='3'><div class='input-group-append'><span class='input-group-text'>Inches</span></div></div></td>
	</tr>
	</table>
	
	<br>
	<button class='btn btn-primary' type='submit' name='editFinishOption' onClick='return confirmUpdate()'>Update Finish Option</button> 
	<?php 
	if (!$default) { 
		echo "<button class='btn btn-warning' type='submit' name='makeDefault' onClick='return confirmDefault()'>Make Default</button> ";
		echo "<button class='btn btn-danger' type='submit' name='removeFinishOption' onClick='return confirmDelete()'>Remove Finish Option</button>"; 
	} 
	?>
</form>
<br>
<?php 

	if (isset($result['MESSAGE'])){echo $result['MESSAGE']; }
	
 
?>
<?php require_once 'includes/footer.inc.php'; ?>
