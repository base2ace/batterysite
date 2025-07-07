<?php
// get_battery_brands_ajax.php - Returns a JSON array of all unique battery brands.

require_once 'functions.php';

header('Content-Type: application/json');

try {
    $brands = getBatteryBrands();
    echo json_encode($brands);
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error in get_battery_brands_ajax.php: " . $e->getMessage());
    // Return an empty array or an error message to the client
    echo json_encode(['error' => 'Failed to load battery brands. ' . $e->getMessage()]);
    // Optionally, send a 500 status code
    http_response_code(500);
} finally {
    // Ensure the database connection is closed
    $conn = getDbConnection();
    if ($conn) {
        $conn->close();
    }
}
?>
