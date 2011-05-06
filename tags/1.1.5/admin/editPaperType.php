<?php
include_once 'includes/main.inc.php';
include_once 'paperTypes.inc.php';


if (isset($_POST['removePaperType'])) {
	$paperTypeId = $_POST['paperTypeId'];
	deletePaperType($db,$paperTypeId);
	header("Location: paperTypes.php");

}
elseif (isset($_POST['makeDefault'])) {
	$paperTypeId = $_POST['paperTypeId'];
	setDefaultPaperType($db,$paperTypeId);
	header("Location: paperTypes.php");
}
elseif (isset($_POST['editPaperType'])) {
	$paperTypeId = $_POST['paperTypeId'];
	$name = trim(rtrim($_POST['name']));
	$cost = trim(rtrim($_POST['cost']));
	$width = trim(rtrim($_POST['width']));
	$default = $_POST['default'];

	$result = updatePaperType($db,$paperTypeId,$name,$cost,$width,$default);
	if ($result['RESULT']) { header("Location: paperTypes.php"); }
	
	
}
elseif (isset($_GET['paperTypeId'])) {
	$paperTypeId = $_GET['paperTypeId'];
	$paperType = getPaperType($db,$paperTypeId);
	$name = $paperType[0]['paperTypes_name'];
	$cost = $paperType[0]['paperTypes_cost'];
	$width = $paperType[0]['paperTypes_width'];
	$available = $paperType[0]['paperTypes_available'];
	$default = $paperType[0]['paperTypes_default'];
	
}

include 'includes/header.inc.php';
?>

<script language="JavaScript">
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
<form method='post' action='editPaperType.php?paperTypeId=<?php echo $paperTypeId; ?>'>
<input type='hidden' name='paperTypeId' value='<?php echo $paperTypeId; ?>' />
<input type='hidden' name='default' value='<?php echo $default; ?>' />
<table>
	<tr><td colspan='2' class='header'>Edit Paper Type</td></tr>
	<tr>
		<td class='right'>Name:</td>
		<td class='left'><input type='text' name='name' value='<?php echo $name; ?>' maxlength='40'/> </td>
	</tr>
	<tr>
		<td class='right'>Cost per Inch:</td>
		<td class='left'><input type='text' name='cost' value='<?php echo $cost; ?>' / size='6'> </td>
	</tr>
	<tr>
		<td class='right'>Width:</td>
		<td class='left'><input type='text' name='width' value='<?php echo $width; ?>' / maxlength='2' size='3'>" </td>
	</tr>
	</table>
	<br /><input class='wide' type='submit' name='editPaperType' value='Update Paper Type' onClick='return confirmUpdate()'/>
	<?php 
	if ($default==0) { 
		echo "<br><br><input class='wide' type='submit' name='makeDefault' value='Make Default' onClick='return confirmDefault()'>";  
		echo "<br><br><input class='wide' type='submit' name='removePaperType' value='Remove Paper Type' onClick='return confirmDelete()'>"; 
	} 
	?>
</form>

<?php 

	if (isset($result['MESSAGE'])){echo $result['MESSAGE']; }
	include_once 'includes/footer.inc.php'; ?>
