<?php
require_once 'includes/main.inc.php';
// Every other admin page requires a login; this one did not.
require_once 'includes/session.inc.php';

$log_contents = $log->get_log();

require_once 'includes/header.inc.php';

?>

<h3>View Log</h3>
<hr>
<textarea class='form-control' rows='50' readonly><?php echo htmlspecialchars((string) $log_contents, ENT_QUOTES); ?></textarea>

<?php

require_once '../includes/footer.inc.php';
?>
