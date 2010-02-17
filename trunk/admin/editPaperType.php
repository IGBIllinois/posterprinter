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
	$name = $_POST['name'];
	$cost = $_POST['cost'];
	$width = $_POST['width'];
	$default = $_POST['default'];
	
	$name = trim(rtrim($name));
	$cost = trim(rtrim($cost));
	$width = trim(rtrim($width));
	$errors = 0;
	
	if ($name == "") {
		$nameMsg = "<br><b class='error'>Pleae enter paper type name</b>";
		$errors++;
	}
	if (($cost == "") || !eregi('^[0-9]{1}[0-9]*[.]{1}[0-9]{2}$',$cost)) {
		$costMsg = "<br><b class='error'>Please enter a valid cost</b>";
		$errors++;
	}
	
	if (($width == "") || ($width > max_printer_width) || !(eregi("^[0-9]{1,2}$", $width))) {
		$widthMsg = "<br><b class='error'>Please enter a valid Width.  Maximum is " . max_printer_width . " inches</b>";
		$errors++;
	}
	
	if ($errors == 0) {
	
		updatePaperType($db,$paperTypeId,$name,$cost,$width,$default);	
		header("Location: paperTypes.php");
	}
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
<table class='table_3'>
	<tr>
		<th colspan='2'>Paper Type</th>
	</tr>
	<tr>
		<td class='td_2'>Name:</td>
		<td class='td_3'><input type='text' name='name' value='<?php echo $name; ?>' maxlength='40'/> </td>
	</tr>
	<tr>
		<td class='td_2'>Cost per Inch:</td>
		<td class='td_3'><input type='text' name='cost' value='<?php echo $cost; ?>' / size='6'> </td>
	</tr>
	<tr>
		<td class='td_2'>Width:</td>
		<td class='td_3'><input type='text' name='width' value='<?php echo $width; ?>' / maxlength='2' size='3'>" </td>
	</tr>
	</table>
	<br /><input type='submit' name='editPaperType' value='Update Paper Type' onClick='return confirmUpdate()'/>
	<?php 
	if ($default==0) { 
		echo "<br><br><input type='submit' name='makeDefault' value='Make Default' onClick='return confirmDefault()'>";  
		echo "<br><br><input type='submit' name='removePaperType' value='Remove Paper Type' onClick='return confirmDelete()'>"; 
	} 
	?>
</form>

<?php 

	if (isset($nameMsg)){echo $nameMsg; }
	if (isset($costMsg)){echo $costMsg; }
	if (isset($widthMsg)){echo $widthMsg; }

?>

<?php include_once 'includes/footer.inc.php'; ?>
