<?php
// get_car_makes_ajax.php - Returns a JSON array of all unique car makes.

require_once 'functions.php'; // Ensure functions.php is included to access getCarMakes()

header('Content-Type: application/json'); // Set header to indicate JSON response

// Get all car makes from the database
$carMakes = getCarMakes();

// Output the car makes as a JSON array
echo json_encode($carMakes);
?>
