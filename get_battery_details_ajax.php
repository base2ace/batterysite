<?php
// get_battery_details_ajax.php - Returns a JSON object with details for a single battery.

require_once 'functions.php';

header('Content-Type: application/json');

$batteryName = $_GET['battery_name'] ?? '';

$batteryDetails = [];
if (!empty($batteryName)) {
    try {
        // IMPORTANT: Pass the retailer ID to get the correct pricing for the battery.
        // This assumes a default retailer ID of 1 for now.
        // In a real app, this would come from the user's session/context.
        $defaultRetailerId = 1;
        $batteryDetails = getBatteryDetailsByName($batteryName, $defaultRetailerId);

        if ($batteryDetails === null) {
            // If getBatteryDetailsByName returns null, it means no battery was found or an internal error occurred.
            // We should return an empty array or a specific error message.
            $batteryDetails = ['error' => 'Battery not found or database error.'];
            http_response_code(404); // Not Found if battery isn't in DB
        }

    } catch (Exception $e) {
        error_log("Error in get_battery_details_ajax.php: " . $e->getMessage());
        $batteryDetails = ['error' => 'Failed to fetch battery details due to server error.'];
        http_response_code(500);
    }
} else {
    $batteryDetails = ['error' => 'Battery name is required.'];
    http_response_code(400);
}

echo json_encode($batteryDetails);

// Ensure the database connection is closed
// This part is crucial for good practice, but be mindful if config.php handles global connection closing
$conn = getDbConnection(); // Get the global connection object
if ($conn && $conn->ping()) { // Check if connection is still active before closing
    $conn->close();
}
?>
