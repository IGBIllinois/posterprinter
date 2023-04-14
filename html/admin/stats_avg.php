<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';

require_once 'includes/header.inc.php';

?>
<h3>Monthly Averages</h3>
<hr>
<div class='row'>    
<img class='mx-auto' src='graph.php?graph_type=monthly_avg'/>
</div>

<?php require_once '../includes/footer.inc.php'; ?>
