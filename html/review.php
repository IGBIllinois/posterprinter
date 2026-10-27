<?php
//////////////////////////////////////////////////////
//
//  Poster Printer - review.php
//  Review + confirm. Replaces step3.php + step4.php.
//
//  Reads the staged order from $_SESSION (written by
//  api.php action=submit). Nothing here trusts POST
//  except the confirm/cancel intent + one-time token.
//  On confirm: create order -> move file -> email ->
//  redirect (PRG) to status.php, which doubles as the
//  confirmation page. Refresh can never double-submit.
//
//////////////////////////////////////////////////////

require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

$order_data = $session->get_var('poster_order');

// ---- cancel ----------------------------------------------------------
if (isset($_POST['cancel'])) {
    $up = $session->get_var('poster_upload');
    if (!empty($up['tmpName'])) {
        @unlink(poster::get_tmp_path() . '/' . basename($up['tmpName']));
    }
    $session->destroy_session();
    header('Location: index.php');
    exit;
}

// ---- nothing staged? back to start -----------------------------------
if (!$order_data) {
    header('Location: index.php');
    exit;
}

// ---- confirm ----------------------------------------------------------
if (isset($_POST['confirm'])) {
    // One-time token: blocks double-clicks, resubmits, and CSRF.
    $token = $_POST['token'] ?? '';
    if ($token === '' || !hash_equals((string) $session->get_var('poster_confirm_token'), $token)) {
        header('Location: index.php');
        exit;
    }
    $session->set_session(['poster_confirm_token' => '']); // burn it

    $tmpBase = basename($order_data['posterFileTmpName']);
    $tmpPath = poster::get_tmp_path() . '/' . $tmpBase;
    $error   = '';

    if (!file_exists($tmpPath)) {
        $error = 'Your uploaded file has expired. Please start your order again.';
    } else {
        $orderId = functions::create_order($db, $order_data);
        if (!$orderId) {
            $error = 'Failed to create your order. Please try again or contact ' . htmlspecialchars(settings::get_admin_email(), ENT_QUOTES) . '.';
        } else {
            $thumbName    = 'thumb_'    . pathinfo($tmpBase, PATHINFO_FILENAME) . '.jpg';
            $fullsizeName = 'fullsize_' . pathinfo($tmpBase, PATHINFO_FILENAME) . '.jpg';

            $moved = poster::move_poster($orderId, $order_data['posterFileName'], $tmpBase, $thumbName, $fullsizeName);
            if ($moved === false) {
                error_log('poster review.php: move_poster failed for order ' . $orderId);
                // Order exists in DB; staff will see it lacks a file. Log loudly, continue.
            }

            $order = new order($db, $orderId);
            $log->send_log("Order " . (int) $orderId . " submitted by " . $order->get_email());
            // Email failure must not break the order: the order is already
            // saved and its file moved. The mail functions throw on failure,
            // so each send is caught and logged separately. Admin and user
            // emails are sent independently so one failing doesn't skip the other.
            try {
                $order->mailAdminsNewOrder();
            } catch (\Throwable $mailError) {
                error_log('poster review.php: ADMIN email failed for order ' . $orderId . ': ' . $mailError->getMessage());
                $log->send_log("Order " . (int) $orderId . ": admin notification email failed: " . $mailError->getMessage(), \IGBIllinois\log::ERROR);
            }
            $confirmationSent = true;
            try {
                $order->mailUserNewOrder();
            } catch (\Throwable $mailError) {
                $confirmationSent = false;
                error_log('poster review.php: CONFIRMATION email failed for order ' . $orderId . ': ' . $mailError->getMessage());
                $log->send_log("Order " . (int) $orderId . ": confirmation email to customer failed: " . $mailError->getMessage(), \IGBIllinois\log::ERROR);
            }

            $session->destroy_session();

            // PRG: status.php is now the confirmation page. Refresh-safe.
            header('Location: status.php?order_id=' . (int) $orderId . '&key=' . urlencode($order->get_key()) . '&new=1'
                . ($confirmationSent ? '' : '&mail=failed'));
            exit;
        }
    }

    // Fall through to render the error on the review page.
    $session->set_session(['poster_confirm_token' => bin2hex(random_bytes(16))]); // fresh token for retry
}

$token = (string) $session->get_var('poster_confirm_token');
$o     = $order_data;
$e     = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES); // escape-once helper

require_once 'includes/header.inc.php';
?>
<?php if (!empty($error)) { echo "<div class='alert alert-danger'>" . $e($error) . "</div>"; } ?>
<div class='row'>
<div class='col-lg-7'>
<table class='table table-bordered table-sm detail-table'>
<thead class='thead-dark'>
<tr><th colspan='2'>Review Your Order</th></tr>
<tr><td colspan='2'><em>Please review your order below, then click "Submit Order" to send your order.</em></td></tr>
</thead>
<?php if ($o['rotated']) { ?>
<tr><td colspan='2'><em>Your width and length have been flipped to save paper and money.  This won't affect the size of your poster.</em></td></tr>
<?php } ?>
<tr><td>Poster File</td><td><?php echo $e($o['posterFileName']); ?></td></tr>
<tr><td>Poster Filesize</td><td><?php echo $e(functions::convert_bytes_to_megabytes($o['posterFileSize'])); ?>MB</td></tr>
<tr><td>Width</td><td><?php echo $e($o['width']); ?> inches</td></tr>
<tr><td>Length</td><td><?php echo $e($o['length']); ?> inches</td></tr>
<tr><td>Paper Type</td><td><?php echo $e($o['paperTypeName']); ?></td></tr>
<tr><td>Finish Option</td><td><?php echo $e($o['finishOptionName']); ?></td></tr>
<tr><td>Poster Tube</td><td><?php echo $e($o['posterTubeName']); ?></td></tr>
<tr><td>Rush Order</td><td><?php echo $e($o['rushOrderName']); ?></td></tr>
<tr><td>Pick Up Date</td><td><?php echo $e($o['pickupDate']); ?></td></tr>
<tr><td>Total Cost</td><td>$<?php echo $e($o['totalCost']); ?></td></tr>
<tr><td>CFOP</td><td><?php echo $e($o['cfop']); ?></td></tr>
<tr><td>Activity Code</td><td><?php echo $e($o['activityCode']); ?></td></tr>
<tr><td>Email</td><td><?php echo $e($o['email']); ?></td></tr>
<tr><td>Additional Emails</td><td><?php echo $e($o['additional_emails']); ?></td></tr>
<tr><td>Full Name</td><td><?php echo $e($o['name']); ?></td></tr>
<tr><td>PI</td><td><?php echo $e($o['pi'] ?? ''); ?></td></tr>
<tr><td>Event</td><td><?php echo $e($o['event'] ?? ''); ?></td></tr>
<tr><td>Comments</td><td><?php echo nl2br($e($o['userComments'] ?? '')); ?></td></tr>
</table>
</div>
<div class='col-lg-5'>
<?php
// Same layout as the admin order page: thumbnail beside the details, click
// for the full-size image in an in-page viewer (click outside / Esc closes).
$thumbPath    = poster::get_tmp_path() . '/' . basename($o['posterThumbFileTmpName'] ?? '');
$fullsizePath = poster::get_tmp_path() . '/fullsize_' . pathinfo(basename($o['posterFileTmpName']), PATHINFO_FILENAME) . '.jpg';
if (!empty($o['posterThumbFileTmpName']) && file_exists($thumbPath)) {
    $fullUrl = file_exists($fullsizePath)
        ? 'image.php?image_path=' . urlencode($fullsizePath)
        : 'image.php?image_path=' . urlencode($thumbPath);
    echo "<div class='position-sticky' style='top: 80px;'>";
    echo "<a href='" . $fullUrl . "' onclick=\"event.preventDefault(); document.getElementById('imageDialog').showModal();\">";
    echo "<img class='img-thumbnail d-block w-100' src='image.php?image_path=" . urlencode($thumbPath) . "' alt='Poster preview'></a>";
    echo "<div class='form-text text-center'>Click to view full size</div>";
    echo "</div>";
    echo "<dialog id='imageDialog' class='p-0 border-0 bg-transparent' style='max-width: 95vw; max-height: 95vh;'>";
    echo "<img src='" . $fullUrl . "' alt='Full size poster' style='display:block; max-width: 95vw; max-height: 95vh; object-fit: contain;'>";
    echo "</dialog>";
} else {
    echo "<div class='text-muted'>No preview available</div>";
}
?>
</div>
</div>
<style>
	/* Details tables: fixed layout so long unbroken text (e.g. a pasted URL
	   or "qqqq...") wraps inside its cell instead of widening the table
	   underneath the preview column. */
	.detail-table { table-layout: fixed; width: 100%; }
	.detail-table td { overflow-wrap: anywhere; word-break: break-word; }
	.detail-table td:first-child { width: 9.5rem; }
	dialog::backdrop { background: rgba(0, 0, 0, 0.6); }
</style>
<script>
document.querySelectorAll('dialog').forEach(function (d) {
	d.addEventListener('click', function (e) { if (e.target === d) { d.close(); } });
});
</script>
<p></p>
<div class='row'>
	<div class='mx-auto btn-toolbar'>
		<form method='post' action='review.php?session=<?php echo urlencode($_GET['session'] ?? ''); ?>'>
			<input type='hidden' name='token' value='<?php echo $e($token); ?>'>
			<a class='btn btn-secondary' role='button' href='order.php?session=<?php echo urlencode($_GET['session'] ?? ''); ?>'>&laquo; Previous Page</a>&nbsp;
			<button class='btn btn-warning' type='submit' name='cancel'>Cancel Order</button>&nbsp;
			<button class='btn btn-primary' type='submit' name='confirm'>Submit Order</button>
		</form>
	</div>
</div>
<?php require_once 'includes/footer.inc.php'; ?>
