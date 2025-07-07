<?php
// get_cars_for_battery_ajax.php - Returns a JSON array of cars compatible with a given battery name.

require_once 'functions.php';

header('Content-Type: application/json');

$batteryName = $_GET['battery_name'] ?? '';

$cars = [];
if (!empty($batteryName)) {
    $cars = getCarsForBattery($batteryName);
}

echo json_encode($cars);

$conn = getDbConnection();
$conn->close();
?>
