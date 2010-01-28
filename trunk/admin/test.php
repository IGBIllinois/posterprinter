<?php
include_once '../includes/settings.inc.php';
include 'includes/session.inc.php';
include 'includes/reports.inc.php';
include '../includes/db.class.inc.php';
include 'includes/functions.inc.php';

$year = 2009;
$month = 12;
$db = new db($mysqlSettings['host'],$mysqlSettings['database'],$mysqlSettings['username'],$mysqlSettings['password']);
$data = getReportData($db,$month,$year);
$filename = "PosterReport-" . $month . "-" . $year;
create_excel_2003_report($data,$filename);

?>




<?php include 'includes/footer.inc.php'; ?>
