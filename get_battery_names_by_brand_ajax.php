<?php
// get_battery_names_by_brand_ajax.php - Returns a JSON array of battery names for a given brand.

require_once 'functions.php';

header('Content-Type: application/json');

$brand = $_GET['brand'] ?? '';

$names = [];
if (!empty($brand)) {
    $names = getBatteryNamesByBrand($brand);
}

echo json_encode($names);

$conn = getDbConnection();
$conn->close();
?>
