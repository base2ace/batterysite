<?php
// get_batteries_ajax.php - AJAX endpoint to fetch compatible batteries for a given make and model

require_once 'functions.php';

header('Content-Type: application/json'); // Set header for JSON response

$make = $_GET['make'] ?? '';
$modelVariant = $_GET['model_variant'] ?? '';

// IMPORTANT: For this initial version, we'll hardcode a retailer ID.
// In a real application, this would come from user session after login.
$defaultRetailerId = 1; // Adjust this based on the actual ID in your 'retailers' table

$batteries = [];
if (!empty($make) && !empty($modelVariant)) {
    // No filters applied in this AJAX call yet, as per current request.
    // Filters will be handled when we implement the left pane filters.
    $batteries = getCompatibleBatteries($defaultRetailerId, $make, $modelVariant);
}

echo json_encode($batteries); // Output the batteries as a JSON array

// Close the database connection after use
$conn = getDbConnection();
$conn->close();
?>
