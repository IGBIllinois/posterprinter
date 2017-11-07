<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'finishOptions.inc.php';

	
if (isset($_POST['removeFinishOption'])) {
	$finishOptionId = $_POST['finishOptionId'];
	deleteFinishOption($db,$finishOptionId);	
	header("Location: finishOptions.php");

}
elseif (isset($_POST['makeDefault'])) {
	$finishOptionId = $_POST['finishOptionId'];
	setDefaultFinishOption($db,$finishOptionId);
	header("Location: finishOptions.php");
}
elseif (isset($_POST['editFinishOption'])) {
	$finishOptionId = $_POST['finishOptionId'];
	$name = trim(rtrim($_POST['name']));
	$cost =trim(rtrim( $_POST['cost']));
	$maxWidth = trim(rtrim($_POST['maxWidth']));
	$maxLength = trim(rtrim($_POST['maxLength']));
	$default = $_POST['default'];
	
	$result = updateFinishOption($db,$finishOptionId,$name,$cost,$maxWidth,$maxLength,$default);	
	if ($result['RESULT']) { header("Location: finishOptions.php"); }
}

elseif (isset($_GET['finishOptionId'])) {
	$finishOptionId = $_GET['finishOptionId'];

	$finishOption = getFinishOption($db,$finishOptionId);
	$name = $finishOption[0]['finishOptions_name'];
	$cost = $finishOption[0]['finishOptions_cost'];
	$maxWidth = $finishOption[0]['finishOptions_maxWidth'];
	$maxLength = $finishOption[0]['finishOptions_maxLength']; 
	$available = $finishOption[0]['finishOptions_available'];
	$default = $finishOption[0]['finishOptions_default'];
	
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
<input type='hidden' name='default' value='<?php echo $default; ?>' />
<table class='table table-bordered table-condensed'>
	<tr><th colspan='2'>Edit Finish Option</th></tr>
	<tr>
		<td class='text-right'>Name:</td>
		<td><div class='input-group col-md-6'><input class='form-control' type='text' name='name' value='<?php echo $name; ?>' maxlength='40'/></div></td>
	</tr>
	<tr>
		<td class='text-right'>Cost:</td>
		<td><div class='input-group col-xs-3'><span class='input-group-addon'>$</span><input class='form-control' type='text' name='cost' value='<?php echo $cost; ?>' size='6'></div></td>
	</tr>
	<tr>
		<td class='text-right'>Max Width:</td>
		<td><div class='input-group col-xs-3'><input class='form-control' type='text' name='maxWidth' value='<?php echo $maxWidth; ?>' maxlength='2' size='3'><span class='input-group-addon'>Inches</span></div></td>
	</tr>
	<tr>
		<td class='text-right'>Max Length:</td>
		<td><div class='input-group col-xs-3'><input class='form-control' type='text' name='maxLength' value='<?php echo $maxLength; ?>' maxlength='3' size='3'><span class='input-group-addon'>Inches</span></div></td>
	</tr>
	</table>
	
	<br>
	<button class='btn btn-primary' type='submit' name='editFinishOption' onClick='return confirmUpdate()'>Update Finish Option</button> 
	<?php 
	if ($default==0) { 
		echo "<button class='btn btn-warning' type='submit' name='makeDefault' onClick='return confirmDefault()'>Make Default</button> ";
		echo "<button class='btn btn-danger' type='submit' name='removeFinishOption' onClick='return confirmDelete()'>Remove Finish Option</button>"; 
	} 
	?>
</form>

<?php 

	if (isset($result['MESSAGE'])){echo $result['MESSAGE']; }
	
 
?>
<?php require_once 'includes/footer.inc.php'; ?>
