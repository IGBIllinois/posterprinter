<?php
//////////////////////////////////////////////////////
//
//  Poster Printer - api.php
//  Unified JSON endpoint. Replaces create.php + get_dimensions.php
//
//  Actions (POST 'action' field):
//    upload    - store poster file server-side, return detected dimensions
//    options   - return valid paper types / finish options for dimensions
//    rushcheck - server-side rush determination for a pickup date
//    submit    - validate everything, price the order, stage it in session
//
//////////////////////////////////////////////////////

require_once 'includes/main.inc.php';

// Same session the page flow uses, so staged data is shared.
$session = new \IGBIllinois\session(settings::get_session_name());

header('Content-Type: application/json; charset=UTF-8');

// Same guards as session.inc.php, but failing as JSON (a redirect would
// hand the AJAX client an HTML page and a confusing parse error).
$session_valid = (time() <= $session->get_var('timeout') + settings::get_session_timeout())
    && ($_SERVER['REMOTE_ADDR'] == $session->get_var('ipaddress'))
    && (($_POST['session'] ?? '') != '' && ($_POST['session'] ?? '') == $session->get_session_id());
if (!$session_valid) {
    $session->destroy_session();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => functions::alert('Your session has expired. Please <a href="index.php">start over</a>.', 0)]);
    exit;
}
$session->set_session(['timeout' => time()]); // reset timeout, matching session.inc.php

// ---------------------------------------------------------------
// Business rules - single source of truth.
// The lamination rule lives in libs/rules.class.inc.php (shared with the
// admin Edit Order page). Rush timing rules are below.
// ---------------------------------------------------------------
const RUSH_BUSINESS_HOURS           = 24;
const BUSINESS_START_HOUR           = 8;      // 8:00 am
const BUSINESS_END_HOUR             = 16;     // ...
const BUSINESS_END_MINUTE           = 30;     // 4:30 pm
const HOLIDAYS                      = [
    // 'YYYY-MM-DD'
];
const SAME_DAY_CONTACT              = 'help@igb.illinois.edu';
const DIMENSION_DETECTABLE_EXTS     = ['pdf', 'jpg', 'jpeg', 'png', 'tif', 'tiff', 'pptx'];

// ---------------------------------------------------------------
// Guarantee JSON output even on fatals/uncaught exceptions,
// so the client never chokes on an HTML error page.
// ---------------------------------------------------------------
set_exception_handler(function ($e) {
    error_log('poster api.php: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => functions::alert('Server error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES), 0),
    ]);
});

function respond(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function fail($messages, int $code = 200): void
{
    // $messages: string or array of strings. Wrapped in the app's alert style.
    $messages = (array) $messages;
    $html = implode('&nbsp;', array_map(fn($m) => functions::alert($m, 0), $messages));
    respond(['success' => false, 'message' => $html], $code);
}

function upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File exceeds the maximum upload size (' . ini_get('upload_max_filesize') . ').',
        UPLOAD_ERR_PARTIAL   => 'File was only partially uploaded. Please try again.',
        UPLOAD_ERR_NO_FILE   => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'Server storage error. Please contact the administrator.',
        default              => 'Unknown upload error (code ' . $code . ').',
    };
}

// ---------------------------------------------------------------
// Dimension extraction (fixes: PPCM units, zero/missing DPI)
// ---------------------------------------------------------------
function extract_pptx_dimensions(string $path): ?array
{
    // .pptx is a zip; slide size lives in ppt/presentation.xml as
    // <p:sldSz cx=".." cy=".."/> in EMUs (914400 EMU = 1 inch).
    // Only the slide canvas counts - off-slide content never prints.
    $zip = new \ZipArchive();
    if ($zip->open($path) !== true) {
        return null;
    }
    $xml = $zip->getFromName('ppt/presentation.xml');
    $zip->close();
    if (!$xml || !preg_match('/<p:sldSz\b[^>]*>/', $xml, $tag)) {
        return null;
    }
    if (!preg_match('/\bcx="(\d+)"/', $tag[0], $cx) || !preg_match('/\bcy="(\d+)"/', $tag[0], $cy)) {
        return null;
    }
    $w = (int) round((int) $cx[1] / 914400);
    $h = (int) round((int) $cy[1] / 914400);
    return ($w > 0 && $h > 0) ? ['width' => $w, 'height' => $h] : null;
}

function extract_dimensions(string $path, string $ext): ?array
{
    if ($ext === 'pptx') {
        return extract_pptx_dimensions($path);
    }
    try {
        $img = new \Imagick();
        if ($ext === 'pdf') {
            $img->setResolution(72, 72);
            $img->pingImage($path . '[0]'); // first page only
            $w = $img->getImageWidth() / 72;
            $h = $img->getImageHeight() / 72;
        } else {
            $img->pingImage($path);
            $res   = $img->getImageResolution();
            $units = $img->getImageUnits();
            $rx = (float) $res['x'];
            $ry = (float) $res['y'];
            if ($units === \Imagick::RESOLUTION_PIXELSPERCENTIMETER) {
                $rx *= 2.54;
                $ry *= 2.54;
            }
            if ($rx < 1 || $ry < 1) {
                // No embedded DPI (common for PNG / web-exported JPG).
                // Can't know real size - let the user enter it manually.
                $img->clear();
                return null;
            }
            $w = $img->getImageWidth() / $rx;
            $h = $img->getImageHeight() / $ry;
        }
        $img->clear();
        $w = (int) round($w);
        $h = (int) round($h);
        return ($w > 0 && $h > 0) ? ['width' => $w, 'height' => $h] : null;
    } catch (\Exception $e) {
        error_log('poster dimension extraction: ' . $e->getMessage());
        return null; // fall back to manual entry rather than blocking the order
    }
}

// ---------------------------------------------------------------
// Business-hours rush calculation (server-side source of truth)
// ---------------------------------------------------------------
function is_business_day(\DateTimeImmutable $d): bool
{
    return (int) $d->format('N') <= 5 && !in_array($d->format('Y-m-d'), HOLIDAYS, true);
}

function add_business_hours(\DateTimeImmutable $start, float $hours): \DateTimeImmutable
{
    $d = $start;
    $minutesNeeded = $hours * 60;

    for ($guard = 0; $guard < 60 && $minutesNeeded > 0; $guard++) {
        if (!is_business_day($d)) {
            $d = $d->modify('tomorrow')->setTime(BUSINESS_START_HOUR, 0);
            continue;
        }
        $open  = $d->setTime(BUSINESS_START_HOUR, 0);
        $close = $d->setTime(BUSINESS_END_HOUR, BUSINESS_END_MINUTE);
        if ($d < $open) {
            $d = $open;
        }
        if ($d >= $close) {
            $d = $d->modify('tomorrow')->setTime(BUSINESS_START_HOUR, 0);
            continue;
        }
        $minutesLeftToday = ($close->getTimestamp() - $d->getTimestamp()) / 60;
        if ($minutesNeeded <= $minutesLeftToday) {
            return $d->modify('+' . (int) round($minutesNeeded) . ' minutes');
        }
        $minutesNeeded -= $minutesLeftToday;
        $d = $d->modify('tomorrow')->setTime(BUSINESS_START_HOUR, 0);
    }
    return $d;
}

function pickup_is_rush(string $pickupDate): bool
{
    $deadline = add_business_hours(new \DateTimeImmutable('now'), RUSH_BUSINESS_HOURS);
    $pickup   = new \DateTimeImmutable($pickupDate . ' 00:00:00');
    return $pickup <= $deadline;
}

function pickup_date_error(string $pickupDate): ?string
{
    $d = \DateTimeImmutable::createFromFormat('Y-m-d', $pickupDate);
    if (!$d || $d->format('Y-m-d') !== $pickupDate) {
        return 'Please select a valid pickup date.';
    }
    $d     = $d->setTime(0, 0);
    $today = new \DateTimeImmutable('today');
    if ($d < $today) {
        return 'Pickup date cannot be in the past.';
    }
    if ($d == $today) {
        return 'Same-day pickup cannot be ordered online. Please choose the next business day or later - '
             . 'or email ' . SAME_DAY_CONTACT . ' and we will try to accommodate a same-day request.';
    }
    if (!is_business_day($d)) {
        return 'Please select a business day (Monday-Friday, excluding holidays).';
    }
    return null;
}

// ---------------------------------------------------------------
// Comments composition. orders_comments is the only free-text column,
// so PI, event and requested pickup date ride along in it as labeled
// lines until schema changes are approved. Requested pickup has no
// column at all otherwise, and staff need it.
// ---------------------------------------------------------------
const OPTIONAL_FIELD_MAX = 100;
const COMMENTS_MAX       = 300;

// Character count that works whether or not the mbstring extension is installed.
function text_length(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s);
}

function build_order_comments(string $pi, string $event, string $pickupDate, string $userComments): string
{
    $lines = [];
    if ($pi !== '')         { $lines[] = 'PI: ' . $pi; }
    if ($event !== '')      { $lines[] = 'Event: ' . $event; }
    if ($pickupDate !== '') { $lines[] = 'Requested pickup: ' . $pickupDate; }
    $userComments = trim($userComments);
    if ($userComments !== '') {
        $lines[] = '';
        $lines[] = $userComments;
    }
    return implode("\n", $lines);
}

// ---------------------------------------------------------------
// Router
// ---------------------------------------------------------------

// If the POST body exceeded post_max_size, PHP silently empties $_POST/$_FILES.
if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    fail('Upload exceeds the server limit of ' . ini_get('post_max_size') . '. Please reduce your file size.', 413);
}

$action = $_POST['action'] ?? '';

switch ($action) {

// ----------------------------------------------------------
case 'upload':
// ----------------------------------------------------------
    if (!isset($_FILES['posterFile'])) {
        fail('No file was uploaded.', 400);
    }
    if ($_FILES['posterFile']['error'] !== UPLOAD_ERR_OK) {
        fail('Error uploading file: ' . upload_error_message((int) $_FILES['posterFile']['error']), 400);
    }

    $origName = basename($_FILES['posterFile']['name']);
    if (!verify::verify_filetype($origName)) {
        fail('Please upload a valid filetype. Valid filetypes are .' . implode(', .', settings::get_valid_filetypes()) . '.');
    }

    // Replace any previous staged upload from this session.
    $prev = $session->get_var('poster_upload');
    if (!empty($prev['tmpName'])) {
        @unlink(poster::get_tmp_path() . '/' . basename($prev['tmpName']));
    }

    $tmpName = poster::move_tmp_file($origName, $_FILES['posterFile']['tmp_name']);
    if (!$tmpName) {
        fail('Error storing uploaded file on the server.', 500);
    }

    $ext  = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $dims = in_array($ext, DIMENSION_DETECTABLE_EXTS, true)
        ? extract_dimensions(poster::get_tmp_path() . '/' . $tmpName, $ext)
        : null;

    // Dimensions are never user-editable, so a file we cannot measure
    // cannot be ordered online.
    if ($dims === null) {
        @unlink(poster::get_tmp_path() . '/' . $tmpName);
        $session->set_session(['poster_upload' => null]);
        fail('We could not automatically detect the dimensions of this file. Please export your poster '
           . 'as a PDF and browse for it again. If you need assistance, email ' . SAME_DAY_CONTACT . '.');
    }

    $session->set_session(['poster_upload' => [
        'tmpName'  => $tmpName,
        'origName' => $origName,
        'size'     => (int) $_FILES['posterFile']['size'],
        // Detected dims stored server-side; submit uses these when present
        // and ignores POSTed values (fields are read-only client-side too).
        'width'    => $dims['width']  ?? null,
        'height'   => $dims['height'] ?? null,
    ]]);

    respond([
        'success'  => true,
        'detected' => $dims !== null,
        'width'    => $dims['width']  ?? 0,
        'height'   => $dims['height'] ?? 0,
        'filename' => $origName,
    ]);

// ----------------------------------------------------------
case 'options':
// ----------------------------------------------------------
    $w = (float) ($_POST['width'] ?? 0);
    $l = (float) ($_POST['length'] ?? 0);

    $check = poster::verify_dimensions($db, $w, $l);
    if (!$check['RESULT']) {
        respond(['success' => false, 'message' => $check['MESSAGE']]);
    }

    $paper = [];
    foreach (functions::getValidPaperTypes($db, $w, $l) as $p) {
        $paper[] = [
            'id'                 => (int) $p['id'],
            'name'               => $p['name'],
            'cost'               => $p['cost'],
            'maxWidth'           => $p['width'] ?? null,
            'isDefault'          => (bool) $p['paperTypes_default'],
            'restrictLamination' => rules::paper_rejects_laminate((int) $p['id']),
        ];
    }

    $finish = [];
    foreach (functions::getValidFinishOptions($db, $w, $l) as $f) {
        $finish[] = [
            'id'           => (int) $f['id'],
            'name'         => $f['name'],
            'cost'         => $f['cost'],
            'isDefault'    => (bool) $f['finishOptions_default'],
            'isLamination' => rules::finish_is_laminate((int) $f['id']),
            'isNone'       => rules::finish_is_none((int) $f['id']),
        ];
    }

    respond([
        'success'        => true,
        'paperTypes'     => $paper,
        'finishOptions'  => $finish,
        'posterTubeCost' => poster_tube::getPosterTubeCost($db),
        'rushOrderCost'  => rush_order::getRushOrderCost($db),
    ]);

// ----------------------------------------------------------
case 'rushcheck':
// ----------------------------------------------------------
    $pickup = $_POST['pickupDate'] ?? '';
    if (($pd_error = pickup_date_error($pickup)) !== null) {
        respond(['success' => false, 'message' => functions::alert($pd_error, 0)]);
    }
    respond([
        'success' => true,
        'rush'    => pickup_is_rush($pickup),
        'cost'    => rush_order::getRushOrderCost($db),
    ]);

// ----------------------------------------------------------
case 'submit':
// ----------------------------------------------------------
    $_POST = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);

    // Single-line fields: collapse runs of whitespace (double spaces, tabs,
    // pasted line breaks) to one space. Comments keep their line breaks.
    foreach (['name', 'email', 'additional_emails', 'pi', 'event', 'activityCode'] as $field) {
        if (isset($_POST[$field]) && is_string($_POST[$field])) {
            $_POST[$field] = preg_replace('/\s+/u', ' ', $_POST[$field]);
        }
    }

    // Comments: normalize whitespace. Spaces/tabs collapse to one space,
    // each line is trimmed, and any run of blank lines becomes a single
    // blank line. One blank line between paragraphs is kept on purpose.
    if (isset($_POST['comments']) && is_string($_POST['comments'])) {
        $c = str_replace(["\r\n", "\r"], "\n", $_POST['comments']);
        $c = preg_replace('/[ \t]+/u', ' ', $c);
        $c = implode("\n", array_map('trim', explode("\n", $c)));
        $c = preg_replace('/\n{3,}/', "\n\n", $c);
        $_POST['comments'] = trim($c);
    }

    // Length caps. Name/email/additional emails match the orders table
    // column sizes (orders_name VARCHAR(50), orders_email VARCHAR(30),
    // orders_cc_emails VARCHAR(255)); longer values would be cut off by the
    // database. Comments cap is a sanity limit (a few sentences).
    $field_limits = [
        'name'              => ['Full name', 50],
        'email'             => ['Email', 30],
        'additional_emails' => ['Additional emails', 255],
        'comments'          => ['Comments', COMMENTS_MAX],
    ];

    $up = $session->get_var('poster_upload');
    if (!$up || !file_exists(poster::get_tmp_path() . '/' . basename($up['tmpName']))) {
        fail('Your uploaded file has expired. Please upload it again.');
    }

    $errors = [];

    foreach ($field_limits as $field => [$label, $max]) {
        if (text_length($_POST[$field] ?? '') > $max) {
            $errors[] = $label . ' must be ' . $max . ' characters or fewer';
        }
    }

    if (!verify::verify_name($_POST['name'] ?? '')) {
        $errors[] = 'Please enter your first and last name';
    }
    if (!verify::verify_email($_POST['email'] ?? '')) {
        $errors[] = 'Please enter a valid email';
    }
    if (!verify::verify_cc_emails($_POST['additional_emails'] ?? '')) {
        $errors[] = 'Please enter valid additional email addresses';
    }

    $cfop = ($_POST['cfop1'] ?? '') . '-' . ($_POST['cfop2'] ?? '') . '-'
          . ($_POST['cfop3'] ?? '') . '-' . ($_POST['cfop4'] ?? '');
    if (!\IGBIllinois\cfop::verify_format($cfop, $_POST['activityCode'] ?? '')) {
        $errors[] = 'Please enter a valid CFOP';
    }

    // Dimensions come exclusively from server-side detection at upload time.
    // The client fields are read-only display; POSTed values are ignored.
    $w = (float) ($up['width']  ?? 0);
    $l = (float) ($up['height'] ?? 0);
    if ($w <= 0 || $l <= 0) {
        $errors[] = 'Could not determine poster dimensions. Please upload a valid file or enter dimensions manually.';
    } else {
        $dimCheck = poster::verify_dimensions($db, $w, $l);
        if (!$dimCheck['RESULT']) {
            respond(['success' => false, 'message' => $dimCheck['MESSAGE']]);
        }
    }

    if (($_POST['dimensionsConfirmed'] ?? '') !== '1') {
        $errors[] = 'Please confirm the poster dimensions are correct';
    }

    $pickup = $_POST['pickupDate'] ?? '';
    if (($pd_error = pickup_date_error($pickup)) !== null) {
        $errors[] = $pd_error;
    }

    // Optional record-keeping fields. Text only; nothing depends on them.
    // Stored as labeled lines at the top of orders_comments (no schema change).
    $pi    = trim($_POST['pi'] ?? '');
    $event = trim($_POST['event'] ?? '');
    if (text_length($pi) > OPTIONAL_FIELD_MAX)    { $errors[] = 'PI name must be ' . OPTIONAL_FIELD_MAX . ' characters or fewer'; }
    if (text_length($event) > OPTIONAL_FIELD_MAX) { $errors[] = 'Event name must be ' . OPTIONAL_FIELD_MAX . ' characters or fewer'; }

    // Validate selections against what's actually valid for these dimensions.
    $paperTypesId    = (int) ($_POST['paperTypesId'] ?? 0);
    $finishOptionsId = (int) ($_POST['finishOptionsId'] ?? 0);

    // id => name for every option valid at these dimensions.
    $validPapers   = array_column(functions::getValidPaperTypes($db, $w, $l), 'name', 'id');
    $validFinishes = array_column(functions::getValidFinishOptions($db, $w, $l), 'name', 'id');

    if (!isset($validPapers[$paperTypesId])) {
        $errors[] = 'Please select a valid paper type';
    }
    if (!isset($validFinishes[$finishOptionsId])) {
        $errors[] = 'Please select a valid finish option';
    }

    // Lamination restriction enforced SERVER-SIDE, so it holds even if the
    // page's JavaScript is stale or bypassed.
    if (isset($validPapers[$paperTypesId], $validFinishes[$finishOptionsId])
        && rules::paper_rejects_laminate($paperTypesId)
        && rules::finish_is_laminate($finishOptionsId)) {
        $errors[] = 'Lamination is not available for canvas or fine art media - the laminate does not bond to these materials';
    }

    if ($errors) {
        fail($errors);
    }

    // ---- Everything below is server-computed. Client input stops mattering here. ----

    $tube = (($_POST['posterTube'] ?? '0') === '1') ? 1 : 0;
    $rush = pickup_is_rush($pickup) ? 1 : 0;

    $papertype    = new papertype($db, $paperTypesId);
    $finishoption = new finishoption($db, $finishOptionsId);

    // Rotate to fit paper width if it saves paper (same rule as before).
    $rotated = 0;
    if (poster::rotate_dimensions($w, $l, $papertype->get_width())) {
        [$w, $l] = [$l, $w];
        $rotated = 1;
    }

    $tubeStuff = poster_tube::getPosterTubeStuff($db, $tube);
    $rushStuff = rush_order::getRushOrderStuff($db, $rush);

    // NOTE: uses post-rotation length. The old step3.php priced the
    // PRE-rotation length, which billed the wrong dimension on rotated orders.
    $totalCost = ($l * $papertype->get_cost())
               + $finishoption->get_cost()
               + ($tube * $tubeStuff['cost'])
               + ($rush * $rushStuff['cost']);

    // Thumbnail (once - the old code called create_image twice).
    $thumbName = '';
    $thumb = poster::create_image($up['tmpName']);
    if (is_array($thumb) && !empty($thumb['THUMB'])) {
        $thumbName = $thumb['THUMB'];
    } else {
        error_log('poster api.php: thumbnail generation failed for ' . $up['tmpName']);
        // Non-fatal: order proceeds without a preview.
    }

    // Stage the finalized order server-side. Keys mirror what
    // functions::create_order() received from the old step4 POST.
    $session->set_session([
        'poster_order' => [
        'width'                  => $w,
        'length'                 => $l,
        'rotated'                => $rotated,
        'paperTypesId'           => $paperTypesId,
        'finishOptionsId'        => $finishOptionsId,
        'cfop'                   => $cfop,
        'activityCode'           => $_POST['activityCode'] ?? '',
        'email'                  => $_POST['email'],
        'additional_emails'      => $_POST['additional_emails'] ?? '',
        'name'                   => $_POST['name'],
        // 'comments' is what create_order() writes to orders_comments.
        'comments'               => build_order_comments($pi, $event, $pickup, $_POST['comments'] ?? ''),
        // Kept separately for review.php and the Previous Page prefill:
        'userComments'           => $_POST['comments'] ?? '',
        'pi'                     => $pi,
        'event'                  => $event,
        'posterTube'             => $tube,
        'rushOrder'              => $rush,
        'pickupDate'             => $pickup,
        'posterFileName'         => $up['origName'],
        'posterFileTmpName'      => $up['tmpName'],
        'posterFileSize'         => $up['size'],
        'posterThumbFileTmpName' => $thumbName,
        'totalCost'              => $totalCost,
        'rushOrderId'            => $rushStuff['id'],
        'posterTubeId'           => $tubeStuff['id'],
        // Display-only extras for review.php:
        'paperTypeName'          => $papertype->get_name(),
        'finishOptionName'       => $finishoption->get_name(),
        'posterTubeName'         => $tubeStuff['name'],
        'rushOrderName'          => $rushStuff['name'],
        ],
        'poster_confirm_token' => bin2hex(random_bytes(16)),
    ]);

    respond([
        'success'  => true,
        'redirect' => 'review.php?session=' . urlencode($_POST['session'] ?? ''),
    ]);

// ----------------------------------------------------------
default:
// ----------------------------------------------------------
    fail('Unknown action.', 400);
}
