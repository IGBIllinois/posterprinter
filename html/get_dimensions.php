<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'includes/main.inc.php';

$response = array(
    'success' => false,
    'width' => 0,
    'height' => 0,
    'message' => ''
);

$response_code = 200;

try {
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'No file uploaded or upload error occurred';
        echo json_encode($response);
        exit;
    }

    $tmpFile = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];

    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Only process supported file types
    $supportedTypes = ['pdf', 'jpg', 'jpeg', 'tif', 'tiff', 'png'];
    if (!in_array($fileExt, $supportedTypes)) {
        $response['message'] = 'Unsupported file type. Please upload PDF, JPG, JPEG, TIF, TIFF, or PNG files.';
        echo json_encode($response);
        exit;
    }

    // Extract dimensions using Imagick
    $imagick = new \Imagick();

    // For PDFs, we need to set resolution before reading
    if ($fileExt === 'pdf') {
        $imagick->setResolution(72, 72);
        // Use pingImage for faster reading (doesn't load full image data)
        $imagick->pingImage($tmpFile . '[0]'); // Read first page only
    } else {
        // For image files, just read normally
        $imagick->pingImage($tmpFile);
    }

    // Get dimensions in pixels
    $widthPixels = $imagick->getImageWidth();
    $heightPixels = $imagick->getImageHeight();

    // Get resolution
    $resolution = $imagick->getImageResolution();

    // Calculate dimensions in inches
    if ($fileExt === 'pdf') {
        // For PDFs at 72 DPI, pixels = points, so divide by 72 to get inches
        $widthInches = round($widthPixels / 72);
        $heightInches = round($heightPixels / 72);
    } else {
        // For other image formats, use the resolution from the file
        $widthInches = round($widthPixels / $resolution['x']);
        $heightInches = round($heightPixels / $resolution['y']);
    }

    // Clean up
    $imagick->clear();
    $imagick->destroy();

    // Return successful response
    $response['success'] = true;
    $response['width'] = (int)$widthInches;
    $response['height'] = (int)$heightInches;
    $response['message'] = 'Dimensions extracted successfully';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error extracting dimensions: ' . $e->getMessage();
    $response_code = 500;
}

ob_clean();
http_response_code((int)$response_code);
echo json_encode($response);
?>
