<?php
// get_car_models_ajax.php - AJAX endpoint to fetch car models for a given make

require_once 'functions.php';

header('Content-Type: application/json'); // Set header for JSON response

$make = $_GET['make'] ?? ''; // Get the 'make' parameter from the GET request

$models = [];
if (!empty($make)) {
    $models = getCarModelsByMake($make);
}

echo json_encode($models); // Output the models as a JSON array

// Close the database connection after use
$conn = getDbConnection();
$conn->close();
?>
