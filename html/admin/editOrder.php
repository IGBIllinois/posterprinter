<?php
//////////////////////////////////////////////////////
//
//  admin/editOrder.php
//  Edit an order's CFOP, activity code, paper, finish, tube and rush.
//  Recalculates the total from current database prices.
//
//  Rewritten Oct 2026: the old version could not run (quote syntax errors,
//  calls to functions that do not exist, an undefined order id) and never
//  checked its input. All output is escaped; all input is validated.
//
//////////////////////////////////////////////////////
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
	header("Location: index.php");
	exit;
}
$order_id = (int) $_GET['order_id'];
$order    = new order($db, $order_id);

// Valid choices for this order's dimensions (same lists the order form uses).
$validPapers   = array_column(functions::getValidPaperTypes($db, $order->get_width(), $order->get_length()), 'name', 'id');
$validFinishes = array_column(functions::getValidFinishOptions($db, $order->get_width(), $order->get_length()), 'name', 'id');
$posterTubes   = poster_tube::getPosterTubes($db);
$rushOrders    = rush_order::getRushOrders($db);

$errors = [];

if (isset($_POST['editOrder'])) {
	$cfop = trim($_POST['cfop1'] ?? '') . '-' . trim($_POST['cfop2'] ?? '') . '-'
	      . trim($_POST['cfop3'] ?? '') . '-' . trim($_POST['cfop4'] ?? '');
	$activityCode    = strtoupper(trim($_POST['activityCode'] ?? ''));
	$paperTypeId     = (int) ($_POST['paperType'] ?? 0);
	$finishOptionId  = (int) ($_POST['finishOption'] ?? 0);
	$posterTubeId    = (int) ($_POST['posterTube'] ?? 0);
	$rushOrderId     = (int) ($_POST['rushOrder'] ?? 0);

	if (!\IGBIllinois\cfop::verify_format($cfop, $activityCode)) {
		$errors[] = 'Please enter a valid CFOP and activity code.';
	}
	elseif (settings::get_cfop_api_key()) {
		// Check against the university CFOP service only when an API key is
		// configured (production). Without a key this step is skipped.
		try {
			$cfop_obj = new \IGBIllinois\cfop(settings::get_cfop_api_key(), settings::get_debug());
			$cfop_obj->validate_cfop($cfop, $activityCode);
		} catch (\Exception $e) {
			$errors[] = 'CFOP check failed: ' . $e->getMessage();
		}
	}

	if (!isset($validPapers[$paperTypeId]))      { $errors[] = 'Please choose a valid paper type.'; }
	if (!isset($validFinishes[$finishOptionId])) { $errors[] = 'Please choose a valid finish option.'; }
	if (rules::paper_rejects_laminate($paperTypeId) && rules::finish_is_laminate($finishOptionId)) {
		$errors[] = 'Lamination is not available for canvas or fine art media - the laminate does not bond to these materials.';
	}

	$tube = poster_tube::getPosterTube($db, $posterTubeId);
	$rush = rush_order::getRushOrder($db, $rushOrderId);
	if (!$tube) { $errors[] = 'Please choose a valid poster tube option.'; }
	if (!$rush) { $errors[] = 'Please choose a valid rush option.'; }

	if (!$errors) {
		$papertype    = new papertype($db, $paperTypeId);
		$finishoption = new finishoption($db, $finishOptionId);
		$length       = rules::billed_length((float) $order->get_width(), (float) $order->get_length(), (float) $papertype->get_width());
		$totalCost    = ($length * $papertype->get_cost())
		              + $finishoption->get_cost()
		              + $tube[0]['posterTube_cost']
		              + $rush[0]['rushOrder_cost'];

		$order->edit($cfop, $activityCode, $finishOptionId, $paperTypeId, $posterTubeId, $rushOrderId, $totalCost);
		$log->send_log("Order " . $order_id . " edited by " . $session->get_var('username')
			. " (new total $" . number_format($totalCost, 2) . ")");
		header("Location: order.php?order_id=" . $order_id);
		exit;
	}
}

// Builds a <select>; $options is id => label.
function select_html(string $name, array $options, $selected): string {
	$html = "<select class='form-select form-select-sm' name='" . $name . "'>";
	foreach ($options as $id => $label) {
		$sel = ((string) $id === (string) $selected) ? " selected" : "";
		$html .= "<option value='" . (int) $id . "'" . $sel . ">" . htmlspecialchars((string) $label, ENT_QUOTES) . "</option>";
	}
	return $html . "</select>";
}

// On a failed save, keep what the admin chose; otherwise show the order's values.
$cur = [
	'paperType'    => $_POST['paperType']    ?? $order->get_paper_type_id(),
	'finishOption' => $_POST['finishOption'] ?? $order->get_finish_option_id(),
	'posterTube'   => $_POST['posterTube']   ?? $order->get_poster_tube_id(),
	'rushOrder'    => $_POST['rushOrder']    ?? $order->get_rush_order_id(),
	'cfop1'        => $_POST['cfop1']        ?? $order->get_cfop_college(),
	'cfop2'        => $_POST['cfop2']        ?? $order->get_cfop_fund(),
	'cfop3'        => $_POST['cfop3']        ?? $order->get_cfop_organization(),
	'cfop4'        => $_POST['cfop4']        ?? $order->get_cfop_program(),
	'activityCode' => $_POST['activityCode'] ?? $order->get_activity_code(),
];

require_once 'includes/header.inc.php';
?>

<?php if ($errors) { ?>
<div class='alert alert-danger'><?php echo implode('<br>', array_map($h, $errors)); ?></div>
<?php } ?>

<form method='post' action='editOrder.php?order_id=<?php echo $order_id; ?>' onsubmit="return confirm('Save these changes? The total will be recalculated from current prices.');">
<table class='table table-bordered table-sm edit-table' style='max-width: 900px;'>
<thead><tr><th colspan='2'>Edit Order #<?php echo $order_id; ?></th></tr></thead>
<tbody>
<tr><td class='text-end'>Full Name</td><td><?php echo $h($order->get_name()); ?></td></tr>
<tr><td class='text-end'>Email</td><td><?php echo $h($order->get_email()); ?></td></tr>
<tr><td class='text-end'>File</td><td><?php echo $h($order->get_filename()); ?></td></tr>
<tr><td class='text-end'>Size</td><td><?php echo $h($order->get_width()); ?>'' &times; <?php echo $h($order->get_length()); ?>''</td></tr>
<tr><td class='text-end'>Current Total</td><td>$<?php echo $h($order->get_total_cost()); ?></td></tr>
<tr><td class='text-end align-middle'>CFOP</td><td>
	<div class='d-flex align-items-center gap-1'>
	<input type='text' name='cfop1' maxlength='1' class='form-control form-control-sm cfop' style='width: 3rem;' value='<?php echo $h($cur['cfop1']); ?>'> -
	<input type='text' name='cfop2' maxlength='6' class='form-control form-control-sm cfop' style='width: 6rem;' value='<?php echo $h($cur['cfop2']); ?>'> -
	<input type='text' name='cfop3' maxlength='6' class='form-control form-control-sm cfop' style='width: 6rem;' value='<?php echo $h($cur['cfop3']); ?>'> -
	<input type='text' name='cfop4' maxlength='6' class='form-control form-control-sm cfop' style='width: 6rem;' value='<?php echo $h($cur['cfop4']); ?>'>
	</div>
</td></tr>
<tr><td class='text-end align-middle'>Activity Code</td><td><input class='form-control form-control-sm' style='width: 8rem;' type='text' name='activityCode' maxlength='6' value='<?php echo $h($cur['activityCode']); ?>'></td></tr>
<tr><td class='text-end align-middle'>Paper Type</td><td><?php echo select_html('paperType', $validPapers, $cur['paperType']); ?></td></tr>
<tr><td class='text-end align-middle'>Finish Option</td><td><?php echo select_html('finishOption', $validFinishes, $cur['finishOption']); ?></td></tr>
<tr><td class='text-end align-middle'>Poster Tube</td><td><?php echo select_html('posterTube', array_column($posterTubes, 'posterTube_name', 'posterTube_id'), $cur['posterTube']); ?></td></tr>
<tr><td class='text-end align-middle'>Rush Order</td><td><?php echo select_html('rushOrder', array_column($rushOrders, 'rushOrder_name', 'rushOrder_id'), $cur['rushOrder']); ?></td></tr>
</tbody>
</table>
<a class='btn btn-secondary' href='order.php?order_id=<?php echo $order_id; ?>'>Cancel</a>
<button class='btn btn-primary' type='submit' name='editOrder'>Save Changes</button>
</form>

<style>.edit-table td:first-child { width: 10rem; }</style>
<script>
// CFOP boxes: jump to the next box when one is full.
document.querySelectorAll('input.cfop').forEach(function (box, i, all) {
	box.addEventListener('input', function () {
		if (box.value.length >= box.maxLength && all[i + 1]) { all[i + 1].focus(); }
	});
});
</script>
<?php require_once '../includes/footer.inc.php'; ?>
