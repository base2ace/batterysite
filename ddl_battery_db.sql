-- Database: `battery_db` (You can create this database first if you haven't already)
-- CREATE DATABASE IF NOT EXISTS battery_db;
-- USE battery_db;

-- 1. `retailers` table (NEW TABLE)
-- Stores information about each individual retailer/dealer
CREATE TABLE IF NOT EXISTS `retailers` (
    `retailer_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE, -- Retailer's business name
    `contact_person` VARCHAR(255) NULL,
    `phone` VARCHAR(50) NULL,
    `email` VARCHAR(255) NULL UNIQUE,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `zip_code` VARCHAR(20) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2. `cars` table (NO CHANGE)
-- Stores unique car make and model variants
CREATE TABLE IF NOT EXISTS `cars` (
    `car_id` INT AUTO_INCREMENT PRIMARY KEY,
    `make` VARCHAR(100) NOT NULL,
    `model_variant` VARCHAR(255) NOT NULL,
    UNIQUE KEY `idx_unique_car_variant` (`make`, `model_variant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 3. `batteries` table (MODIFIED: Pricing columns removed)
-- Stores universal details about each unique battery product
CREATE TABLE IF NOT EXISTS `batteries` (
    `battery_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `brand` VARCHAR(100) NOT NULL,
    `capacity_ah` INT,
    `total_warranty_months` INT,
    `full_replacement_warranty_months` INT,
    `pro_rata_warranty_months` INT,
    `image_url` VARCHAR(512),
    `local_image_path` VARCHAR(512) NULL, -- Relative path for your web app
    `original_url` VARCHAR(512) NOT NULL UNIQUE, -- Original scraped URL

    UNIQUE KEY `idx_unique_battery_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 4. `retailer_battery_pricing` table (NEW TABLE)
-- Stores specific pricing for a battery offered by a particular retailer
CREATE TABLE IF NOT EXISTS `retailer_battery_pricing` (
    `retailer_id` INT NOT NULL,
    `battery_id` INT NOT NULL,
    `price_inr` DECIMAL(10, 2) NULL, -- Standard price (MRP) for this retailer
    `special_price_inr` DECIMAL(10, 2) NULL,
    `price_with_exchange_inr` DECIMAL(10, 2) NULL,
    `price_without_exchange_inr` DECIMAL(10, 2) NULL,
    `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`retailer_id`, `battery_id`), -- Composite primary key
    FOREIGN KEY (`retailer_id`) REFERENCES `retailers`(`retailer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`battery_id`) REFERENCES `batteries`(`battery_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 5. `car_battery_compatibility` table (NO CHANGE)
-- Links cars to batteries (Many-to-Many relationship)
CREATE TABLE IF NOT EXISTS `car_battery_compatibility` (
    `compatibility_id` INT AUTO_INCREMENT PRIMARY KEY,
    `car_id` INT NOT NULL,
    `battery_id` INT NOT NULL,

    FOREIGN KEY (`car_id`) REFERENCES `cars`(`car_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`battery_id`) REFERENCES `batteries`(`battery_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `idx_unique_compatibility` (`car_id`, `battery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Indexes for efficient querying (beyond primary/unique keys)

-- Indexes on `cars` table
CREATE INDEX `idx_cars_make_model_variant` ON `cars` (`make`, `model_variant`);

-- Indexes on `batteries` table
CREATE INDEX `idx_batteries_capacity_ah` ON `batteries` (`capacity_ah`);
CREATE INDEX `idx_batteries_warranty` ON `batteries` (`total_warranty_months`);
CREATE INDEX `idx_batteries_name` ON `batteries` (`name`);

-- Indexes on `retailer_battery_pricing` table for efficient lookups
CREATE INDEX `idx_rbp_retailer_id` ON `retailer_battery_pricing` (`retailer_id`);
CREATE INDEX `idx_rbp_battery_id` ON `retailer_battery_pricing` (`battery_id`);
CREATE INDEX `idx_rbp_price_inr` ON `retailer_battery_pricing` (`price_inr`);
CREATE INDEX `idx_rbp_special_price_inr` ON `retailer_battery_pricing` (`special_price_inr`);

-- Indexes on `car_battery_compatibility` table
CREATE INDEX `idx_compatibility_car_id` ON `car_battery_compatibility` (`car_id`);
CREATE INDEX `idx_compatibility_battery_id` ON `car_battery_compatibility` (`battery_id`);