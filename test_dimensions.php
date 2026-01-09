#!/usr/bin/env php
<?php
/**
 * Test script for dimension extraction
 *
 * This script tests the Imagick dimension extraction functionality
 * without requiring an actual file upload.
 */

// Simple test without database dependencies

echo "=== Testing Imagick Dimension Extraction ===\n\n";

// Check if Imagick is installed
if (!class_exists('Imagick')) {
    echo "ERROR: Imagick extension is not installed!\n";
    exit(1);
}

echo "✓ Imagick extension is installed\n";

// Get Imagick version
$imagick = new Imagick();
$version = $imagick->getVersion();
echo "✓ Imagick version: " . $version['versionString'] . "\n\n";

// Test creating a simple test PDF in memory
echo "Creating a test PDF to verify dimension extraction...\n";

try {
    // Create a simple test image (8x11 inches at 72 DPI = 576x792 pixels)
    $testImage = new Imagick();
    $testImage->newImage(576, 792, new ImagickPixel('white'));
    $testImage->setImageFormat('pdf');
    $testImage->setResolution(72, 72);

    // Save to temp file
    $tempFile = sys_get_temp_dir() . '/test_poster_' . time() . '.pdf';
    $testImage->writeImage($tempFile);
    $testImage->clear();
    $testImage->destroy();

    echo "✓ Test PDF created: $tempFile\n";

    // Now test dimension extraction
    $extractImage = new Imagick();
    $extractImage->setResolution(72, 72);
    $extractImage->pingImage($tempFile . '[0]');

    $width = $extractImage->getImageWidth();
    $height = $extractImage->getImageHeight();

    $widthInches = round($width / 72);
    $heightInches = round($height / 72);

    $extractImage->clear();
    $extractImage->destroy();

    echo "✓ Extracted dimensions: {$widthInches}\" x {$heightInches}\"\n";
    echo "  (Expected: 8\" x 11\")\n";

    // Clean up
    unlink($tempFile);

    if ($widthInches == 8 && $heightInches == 11) {
        echo "\n✓✓✓ SUCCESS! Dimension extraction is working correctly! ✓✓✓\n";
        exit(0);
    } else {
        echo "\n✗ WARNING: Dimensions don't match expected values\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
