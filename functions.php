<?php
// functions.php - Reusable database interaction functions

// Include the database configuration
require_once 'config.php';

/**
 * Gets the database connection object.
 * @return mysqli The database connection object.
 */
function getDbConnection() {
    global $conn; // Use the global connection variable from config.php
    // It's good practice to ensure the connection is valid before returning
    if (!$conn || $conn->connect_error) {
        // Log the error if connection is not established or has failed
        error_log("Attempted to get DB connection, but it's not valid: " . ($conn ? $conn->connect_error : 'Connection object is null'));
        // You might want to throw an exception here or return null
        return null;
    }
    return $conn;
}

/**
 * Fetches all distinct car makes from the 'cars' table.
 * @return array An array of car makes.
 */
function getCarMakes() {
    $conn = getDbConnection();
    if (!$conn) return []; // Return empty if no valid connection

    $makes = [];
    $sql = "SELECT DISTINCT make FROM cars ORDER BY make ASC";
    $result = $conn->query($sql);

    if ($result === false) {
        error_log("SQL Error in getCarMakes: " . $conn->error);
        return [];
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $makes[] = $row['make'];
        }
    }
    return $makes;
}

/**
 * Fetches car models/variants for a given car make.
 * @param string $make The car make.
 * @return array An array of car model variants.
 */
function getCarModelsByMake($make) {
    $conn = getDbConnection();
    if (!$conn) return [];

    $models = [];
    $sql = "SELECT model_variant FROM cars WHERE make = ? ORDER BY model_variant ASC";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Failed to prepare statement in getCarModelsByMake: " . $conn->error);
        return [];
    }

    $stmt->bind_param("s", $make);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        error_log("Failed to get result in getCarModelsByMake: " . $stmt->error);
        $stmt->close();
        return [];
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $models[] = $row['model_variant'];
        }
    }
    $stmt->close();
    return $models;
}

/**
 * Fetches compatible batteries for a given car make and model variant,
 * with optional filters and specific retailer pricing.
 *
 * @param int $retailerId The ID of the retailer whose prices to fetch.
 * @param string $make The car make.
 * @param string $modelVariant The car model variant.
 * @param array $filters Optional array of filters (e.g., ['capacity_ah_min' => 40, 'capacity_ah_max' => 60, 'price_min' => 1000, 'price_max' => 5000, 'warranty_min' => 36]).
 * @return array An array of compatible battery details.
 */
function getCompatibleBatteries($retailerId, $make, $modelVariant, $filters = []) {
    $conn = getDbConnection();
    if (!$conn) return [];

    $batteries = [];

    // Base SQL query to join cars, compatibility, batteries, and retailer pricing
    $sql = "
        SELECT
            b.name,
            b.brand,
            b.capacity_ah,
            b.total_warranty_months,
            b.full_replacement_warranty_months,
            b.pro_rata_warranty_months,
            b.image_url,
            b.local_image_path,
            rbp.price_inr,
            rbp.special_price_inr,
            rbp.price_with_exchange_inr,
            rbp.price_without_exchange_inr
        FROM
            cars c
        JOIN
            car_battery_compatibility cbc ON c.car_id = cbc.car_id
        JOIN
            batteries b ON cbc.battery_id = b.battery_id
        JOIN
            retailer_battery_pricing rbp ON b.battery_id = rbp.battery_id
        WHERE
            c.make = ? AND c.model_variant = ? AND rbp.retailer_id = ?
    ";

    $params = [$make, $modelVariant, $retailerId];
    $types = "ssi"; // String, String, Integer for make, model_variant, retailer_id
    $whereClauses = [];

    // Add filters (not yet active in JS, but ready in PHP)
    // Capacity AH Filter
    if (isset($filters['capacity_ah_min']) && is_numeric($filters['capacity_ah_min'])) {
        $whereClauses[] = "b.capacity_ah >= ?";
        $params[] = $filters['capacity_ah_min'];
        $types .= "i";
    }
    if (isset($filters['capacity_ah_max']) && is_numeric($filters['capacity_ah_max'])) {
        $whereClauses[] = "b.capacity_ah <= ?";
        $params[] = $filters['capacity_ah_max'];
        $types .= "i";
    }

    // Price Range Filter (using special_price_inr as the primary filter price)
    if (isset($filters['price_min']) && is_numeric($filters['price_min'])) {
        $whereClauses[] = "rbp.special_price_inr >= ?";
        $params[] = $filters['price_min'];
        $types .= "d"; // Double for decimal
    }
    if (isset($filters['price_max']) && is_numeric($filters['price_max'])) {
        $whereClauses[] = "rbp.special_price_inr <= ?";
        $params[] = $filters['price_max'];
        $types .= "d";
    }

    // Total Warranty Months Filter
    if (isset($filters['warranty_min']) && is_numeric($filters['warranty_min'])) {
        $whereClauses[] = "b.total_warranty_months >= ?";
        $params[] = $filters['warranty_min'];
        $types .= "i";
    }
    if (isset($filters['warranty_max']) && is_numeric($filters['warranty_max'])) {
        $whereClauses[] = "b.total_warranty_months <= ?";
        $params[] = $filters['warranty_max'];
        $types .= "i";
    }

    if (!empty($whereClauses)) {
        $sql .= " AND " . implode(" AND ", $whereClauses);
    }

    // Order results (optional)
    $sql .= " ORDER BY b.brand, b.name ASC";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Failed to prepare statement in getCompatibleBatteries: " . $conn->error);
        return [];
    }

    // Dynamically bind parameters
    // The first argument to bind_param is the types string, followed by parameters
    if (!empty($params)) {
        $bind_params = array();
        $bind_params[] = $types;
        foreach ($params as $key => $value) {
            $bind_params[] = &$params[$key]; // Pass by reference
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_params);
    }


    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        error_log("Failed to get result in getCompatibleBatteries: " . $stmt->error);
        $stmt->close();
        return [];
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $batteries[] = $row;
        }
    }
    $stmt->close();
    return $batteries;
}

/**
 * Fetches all distinct battery brands from the 'batteries' table.
 * @return array An array of battery brands.
 */
function getBatteryBrands() {
    $conn = getDbConnection();
    if (!$conn) {
        error_log("No valid DB connection in getBatteryBrands.");
        throw new Exception("Database connection not available.");
    }

    $brands = [];
    $sql = "SELECT DISTINCT brand FROM batteries ORDER BY brand ASC";
    $result = $conn->query($sql);

    if ($result === false) {
        error_log("SQL Error in getBatteryBrands: " . $conn->error);
        throw new Exception("Database query failed for brands: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row['brand'];
        }
    }
    return $brands;
}

/**
 * Fetches battery names for a given battery brand.
 * @param string $brand The battery brand.
 * @return array An array of battery names.
 */
function getBatteryNamesByBrand($brand) {
    $conn = getDbConnection();
    if (!$conn) return [];

    $names = [];
    $sql = "SELECT name FROM batteries WHERE brand = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Failed to prepare statement in getBatteryNamesByBrand: " . $conn->error);
        return [];
    }

    $stmt->bind_param("s", $brand);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        error_log("Failed to get result in getBatteryNamesByBrand: " . $stmt->error);
        $stmt->close();
        return [];
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $names[] = $row['name'];
        }
    }
    $stmt->close();
    return $names;
}

/**
 * Fetches all details for a single battery by its name.
 * Includes pricing for a specific retailer.
 *
 * @param string $batteryName The name of the battery.
 * @param int $retailerId The ID of the retailer whose prices to fetch.
 * @return array|null An associative array of battery details, or null if not found.
 */
function getBatteryDetailsByName($batteryName, $retailerId) {
    $conn = getDbConnection();
    if (!$conn) return null;

    $sql = "
        SELECT
            b.name,
            b.brand,
            b.capacity_ah,
            b.total_warranty_months,
            b.full_replacement_warranty_months,
            b.pro_rata_warranty_months,
            b.image_url,
            b.local_image_path,
            rbp.price_inr,
            rbp.special_price_inr,
            rbp.price_with_exchange_inr,
            rbp.price_without_exchange_inr
        FROM
            batteries b
        LEFT JOIN
            retailer_battery_pricing rbp ON b.battery_id = rbp.battery_id AND rbp.retailer_id = ?
        WHERE
            b.name = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Failed to prepare statement in getBatteryDetailsByName: " . $conn->error);
        return null;
    }

    $stmt->bind_param("is", $retailerId, $batteryName); // Integer for retailerId, String for batteryName
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        error_log("Failed to get result in getBatteryDetailsByName: " . $stmt->error);
        $stmt->close();
        return null;
    }

    $battery = $result->fetch_assoc();
    $stmt->close();
    return $battery;
}


/**
 * Finds all cars compatible with a given battery name.
 * @param string $batteryName The name of the battery.
 * @return array An array of compatible car details (make, model_variant).
 */
function getCarsForBattery($batteryName) {
    $conn = getDbConnection();
    if (!$conn) return [];

    $cars = [];
    $sql = "
        SELECT
            c.make,
            c.model_variant
        FROM
            cars c
        JOIN
            car_battery_compatibility cbc ON c.car_id = cbc.car_id
        JOIN
            batteries b ON cbc.battery_id = b.battery_id
        WHERE
            b.name = ?
        ORDER BY c.make, c.model_variant ASC
    ";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Failed to prepare statement in getCarsForBattery: " . $conn->error);
        return [];
    }

    $stmt->bind_param("s", $batteryName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        error_log("Failed to get result in getCarsForBattery: " . $stmt->error);
        $stmt->close();
        return [];
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cars[] = $row;
        }
    }
    $stmt->close();
    return $cars;
}

?>
