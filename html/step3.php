<?php

require_once 'includes/main.inc.php';

//paper type, finish option, cfop, and file submission, confirms order
if ((isset($_GET['session'])) && ($_GET['session'] == $session->get_session_id())) {


}
elseif (isset($_POST['cancel'])) {
        $session->destroy_session();
        header('Location: index.php');
}
else {
        $session->destroy_session();
        echo "Redirect back to index.php";
        //header('Location: index.php');

}

if (isset($_POST['step3'])) {
        foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }

        $width = $_POST['width'];
        $length = $_POST['length'];
        $paperTypesId = $_POST['paperTypesId'];
        $finishOptionsId = $_POST['finishOptionsId'];
        $cfop = $_POST['cfop'];
        $activityCode = $_POST['activityCode'];
        $posterFileName = $_FILES['posterFile']['name'];
        $email = $_POST['email'];
        $name = stripslashes($_POST['name']);
        $comments = stripslashes($_POST['comments']);
        if (isset($_POST['posterTube'])) {
                $posterTube = $_POST['posterTube'];
        }
        else {
                $posterTube = 0;
        }
        if (isset($_POST['rushOrder'])) {
                $rushOrder = $_POST['rushOrder'];
        }
        else {
                $rushOrder = 0;
        }



        //Gets Finish Options Information
        $finishoption = new finishoption($db,$finishOptionsId);

        //Gets Paper Type Information
        $papertype = new papertype($db,$paperTypesId);

        $widthSwitched = 0;
        if (poster::switch_dimensions($width,$length,$papertype->get_width())) {
                $tempWidth = $width;
                $width = $length;
                $length = $tempWidth;
                $widthSwitched = 1;
        }

        $posterTubeResult = poster_tube::getPosterTubeStuff($db,$posterTube);
        $posterTubeCost =  $posterTubeResult["cost"];
        $posterTubeName =  $posterTubeResult["name"];
        $posterTubeId = $posterTubeResult["id"];

        $rushOrderResult = rush_order::getRushOrderStuff($db,$rushOrder);
        $rushOrderCost = $rushOrderResult["cost"];
        $rushOrderName = $rushOrderResult["name"];
        $rushOrderId = $rushOrderResult["id"];

        //Calculates Total Cost
        $totalCost = ($length * $papertype->get_cost()) + $finishoption->get_cost() + ($posterTube * $posterTubeCost) + ($rushOrder * $rushOrderCost);

}

require_once 'includes/header.inc.php';
?>


<table class='table table-bordered table-sm'>
<tr><th colspan='2'>Review Your Order</th></tr>
<tr><td colspan='2'><em>Please review your order below, then click "Submit Order" to send your order</em></td></tr>

<?php
if ($widthSwitched == 1) {
	echo "<tr><td colspan='2' class='description'>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</td></tr>";
}

?>

<tr><td>Poster File</td><td><?php echo $posterFileName; ?></td></tr>
<tr><td>Width</td><td><?php echo $posterWidth; ?> inches</td></tr>
<tr><td>Length</td><td><?php echo $posterLength; ?> inches</td></tr>
<tr><td>Paper Type</td><td><?php echo $papertype->get_name(); ?></td></tr>
<tr><td>Finish Option</td><td><?php echo $finishoption->get_name(); ?></td></tr>
<tr><td>Poster Tube</td><td>><?php echo $posterTubeName; ?></td></tr>
<tr><td>Rush Order</td><td><?php echo $rushOrderName; ?></td></tr>
<tr><td>Total Cost</td><td>$<?php echo $totalCost; ?></td></tr>
<tr><td>CFOP</td><td><?php echo$cfop; ?></td></tr>
<tr><td>Activity Code</td><td><?php echo $activityCode; ?></td></tr>
<tr><td>Email</td><td><?php echo $email; ?></td></tr>
<tr><td>Full Name</td><td><?php echo stripslashes($name); ?></td></tr>
<tr><td>Comments</td><td><?php echo stripslashes($comments); ?></td></tr>

<?php
if (($posterThumbFileTmpName != "") && (file_exists(settings::get_poster_dir() . "/". $posterThumbFileTmpName))) {
	echo "<tr><td colspan='2'><img class='img-responsive img-thumbnail' src='" . settings::get_poster_dir() . "/" . $posterThumbFileTmpName . "'></td></tr>";
}
else {
	echo "<tr><td colspan='2'>No Preview</td></tr>";
}

$url = "step4.php?session=" . $session->get_session_id();

?>

</table>
<br><form method='post' action='<?php echo $url; ?>'>
<input type='hidden' name='posterWidth' value='<?php echo $width; ?>'>
<input type='hidden' name='posterLength' value='<?php echo $length; ?>'>
<input type='hidden' name='paperTypesId' value='<?php echo $paperTypesId; ?>'>
<input type='hidden' name='finishOptionsId' value='<?php echo $finishOptionsId; ?>'>
<input type='hidden' name='totalCost' value='<?php echo $totalCost; ?>'>
<input type='hidden' name='cfop' value='<?php echo $cfop; ?>'>
<input type='hidden' name='activityCode' value='<?php echo $activityCode; ?>'>
<input type='hidden' name='email' value='<?php echo $email; ?>'>
<input type='hidden' name='additional_emails' value='<?php echo $_POST['additional_emails']; ?>'>
<input type='hidden' name='name' value='<?php echo htmlspecialchars($name,ENT_QUOTES); ?>'>
<input type='hidden' name='comments' value='<?php echo htmlspecialchars($comments,ENT_QUOTES); ?>'>
<input type='hidden' name='posterTubeId' value='<?php echo $posterTubeId; ?>'>
<input type='hidden' name='rushOrderId' value='<?php echo $rushOrderId; ?>'>
<input type='hidden' name='posterFileName' value='<?php echo $posterFileName; ?>'>
<input type='hidden' name='posterFileTmpName' value='<?php echo $posterFileTmpName; ?>'>
<input type='hidden' name='widthSwitched' value='<?php echo $widthSwitched; ?>'>
<div class='row'><p class='text-center'>
<button class='btn btn-warning' type='button' onclick='window.location.href=window.location.href''>Cancel</button>
<button class='btn btn-primary' type='submit' name='step4'>Submit Order</button>
</p></div>
</form>


<?php if (isset($message)) { echo $message; } ?>

<?php require_once 'includes/footer.inc.php'; ?>

