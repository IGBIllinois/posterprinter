<?php

require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

functions::debug("Referer: " . $_SERVER['REQUEST_URI']);

if (!(isset($_GET['session'])) || ($_GET['session'] != $session->get_session_id())) {
	$session->destroy_session();
	header('Location: index.php');

}
elseif (isset($_POST['cancel'])) {
        $session->destroy_session();
        header('Location: index.php');
}

elseif (isset($_POST['step3'])) {
        foreach ($_POST as $var) {
		
                $var = trim(rtrim($var));
        }
	functions::debug(implode(",",$_POST));
        $width = $_POST['width'];
        $length = $_POST['length'];
        $paperTypesId = $_POST['paperTypesId'];
        $finishOptionsId = $_POST['finishOptionsId'];
        $cfop = $_POST['cfop'];
        $activityCode = $_POST['activityCode'];
        $posterFileName = $_POST['posterFileName'];
        $email = $_POST['email'];
        $name = stripslashes($_POST['name']);
        $comments = stripslashes($_POST['comments']);

	$posterTube = 0;
        if (isset($_POST['posterTube'])) {
                $posterTube = 1;
        }
        $rushOrder = 0;
	if (isset($_POST['rushOrder'])) {
                $rushOrder = 1;
        }



        //Gets Finish Options Information
        $finishoption = new finishoption($db,$finishOptionsId);

        //Gets Paper Type Information
        $papertype = new papertype($db,$paperTypesId);

        $_POST['rotated'] = 0;
        if (poster::rotate_dimensions($_POST['width'],$_POST['length'],$papertype->get_width())) {
                $tempWidth = $_POST['width'];
                $_POST['width'] = $_POST['length'];
                $_POST['length'] = $tempWidth;
                $_POST['rotated'] = 1;
        }

        $posterTubeResult = poster_tube::getPosterTubeStuff($db,$posterTube);
        $posterTubeCost =  $posterTubeResult["cost"];
        $posterTubeName =  $posterTubeResult["name"];

        $rushOrderResult = rush_order::getRushOrderStuff($db,$rushOrder);
        $rushOrderCost = $rushOrderResult["cost"];
        $rushOrderName = $rushOrderResult["name"];

        //Calculates Total Cost
        $totalCost = ($length * $papertype->get_cost()) + $finishoption->get_cost() + ($posterTube * $posterTubeCost) + ($rushOrder * $rushOrderCost);

}
else {
        $session->destroy_session();
        header('Location: index.php');

}

require_once 'includes/header.inc.php';
?>

<div class='row'>
<table class='table table-bordered table-sm'>
<tr><th colspan='2'>Review Your Order</th></tr>
<tr><td colspan='2'><em>Please review your order below, then click "Submit Order" to send your order</em></td></tr>

<?php
if ($_POST['rotated']) {
	echo "<tr><td colspan='2' class='description'>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</td></tr>";
}

?>

<tr><td>Poster File</td><td><?php echo $_POST['posterFileName']; ?></td></tr>
<tr><td>Poster Filesize</td><td><?php echo functions::convert_bytes_to_megabytes($_POST['posterFileSize']); ?>MB</td></tr>
<tr><td>Width</td><td><?php echo $_POST['width']; ?> inches</td></tr>
<tr><td>Length</td><td><?php echo $_POST['length']; ?> inches</td></tr>
<tr><td>Paper Type</td><td><?php echo $papertype->get_name(); ?></td></tr>
<tr><td>Finish Option</td><td><?php echo $finishoption->get_name(); ?></td></tr>
<tr><td>Poster Tube</td><td><?php echo $posterTubeName; ?></td></tr>
<tr><td>Rush Order</td><td><?php echo $rushOrderName; ?></td></tr>
<tr><td>Total Cost</td><td>$<?php echo $totalCost; ?></td></tr>
<tr><td>CFOP</td><td><?php echo $_POST['cfop']; ?></td></tr>
<tr><td>Activity Code</td><td><?php echo $_POST['activityCode']; ?></td></tr>
<tr><td>Email</td><td><?php echo $_POST['email']; ?></td></tr>
<tr><td>Full Name</td><td><?php echo stripslashes($_POST['name']); ?></td></tr>
<tr><td>Comments</td><td><?php echo stripslashes($_POST['comments']); ?></td></tr>

<?php
if (($_POST['posterThumbFileTmpName'] != "") && (file_exists(poster::get_tmp_path() . "/". $_POST['posterThumbFileTmpName']))) {
	echo "<tr><td colspan='2'><img class='img-thumbnail mx-auto d-block' src='image.php?image=" . $_POST['posterThumbFileTmpName'] . "'></td></tr>";
}
else {
	echo "<tr><td colspan='2'>No Preview</td></tr>";
}

$url = "step4.php?session=" . $_GET['session'];

?>

</table>
</div>
<p></p>
<div class='row'>
	<div class='mx-auto btn-toolbar'>
        	<form method='post' action='<?php echo $url; ?>'>
	        <?php
        		$_POST['totalCost'] = $totalCost;
		        $_POST['rushOrderId'] = $rushOrderResult["id"];
		        $_POST['posterTubeId'] = $posterTubeResult["id"];
		        foreach ($_POST as $key=>$var) {
                		echo "<input type='hidden' name='" . $key . "' value='" . $var . "'>";
		        }
	        ?>

		<button class='btn btn-warning' type='submit' name='cancel'>Cancel</button>&nbsp;
		<button class='btn btn-primary' type='submit' name='step4'>Submit Order</button>
		</form>
	</div>
</div>
<?php require_once 'includes/footer.inc.php'; ?>

