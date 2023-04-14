<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';


if (isset($_POST['removePaperType'])) {
	$paperTypeId = $_POST['paperTypeId'];
	$papertype = new papertype($db,$paperTypeId);
	$papertype->delete();

	header("Location: paperTypes.php");

}
elseif (isset($_POST['makeDefault'])) {
	$paperTypeId = $_POST['paperTypeId'];
	$papertype = new papertype($db,$paperTypeId);
	$papertype->set_default();
	header("Location: paperTypes.php");
}
elseif (isset($_POST['editPaperType'])) {
	$paperTypeId = $_POST['paperTypeId'];
	$name = trim(rtrim($_POST['name']));
	$cost = trim(rtrim($_POST['cost']));
	$width = trim(rtrim($_POST['width']));
	$papertype = new papertype($db,$paperTypeId);
	$default = $papertype->get_default();
	$result = $papertype->update($name,$cost,$width);
	if ($result['RESULT']) { 
		header("Location: paperTypes.php"); 

	}
	
	
}
elseif (isset($_GET['paperTypeId'])) {
	$paperTypeId = $_GET['paperTypeId'];
	$papertype = new papertype($db,$paperTypeId);

	$name = $papertype->get_name();
	$cost = $papertype->get_cost();
	$width = $papertype->get_width();
	$available = $papertype->get_width();
	$default = $papertype->get_default();
	
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
<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>?paperTypeId=<?php echo $paperTypeId; ?>'>
<input type='hidden' name='paperTypeId' value='<?php echo $paperTypeId; ?>' />
<table class='table table-bordered table-sm'>
	<tr><th colspan='2'>Edit Paper Type</th></tr>
	<tr>
		<td class='text-right vcenter'>Name:</td>
		<td><div class='input-group col-md-6'><input class='form-control' type='text' name='name' value='<?php echo $name; ?>' maxlength='40'></div></td>
	</tr>
	<tr>
		<td class='text-right'>Cost per Inch:</td>
		<td><div class='input-group'><div class='input-group-prepend'><span class='input-group-text'>$</span></div><input class='form-control' type='text' name='cost' value='<?php echo $cost; ?>' / size='6'></div></td>
	</tr>
	<tr>
		<td class='text-right'>Width:</td>
		<td><div class='input-group col-md-3'><input class='form-control' type='text' name='width' value='<?php echo $width; ?>' / maxlength='2' size='3'><div class='input-group-append'><span class='input-group-text'>Inches</span><div></div> </td>
	</tr>
	</table>
	<br><input class='btn btn-primary' type='submit' name='editPaperType' value='Update Paper Type' onClick='return confirmUpdate()'>
	<?php 
	if (!$default) { 
		echo "<input class='btn btn-warning' type='submit' name='makeDefault' value='Make Default' onClick='return confirmDefault()'> ";  
		echo "<input class='btn btn-danger' type='submit' name='removePaperType' value='Remove Paper Type' onClick='return confirmDelete()'>"; 
	} 
	?>
</form>
<br>
<?php 

if (isset($result['MESSAGE'])){echo $result['MESSAGE']; }

require_once '../includes/footer.inc.php'; ?>
