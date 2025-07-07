<?php
// index.php - Main application logic and UI for dynamic content with filters and battery display

require_once 'functions.php';

// IMPORTANT: For this initial version, we'll hardcode a retailer ID.
// In a real application, this would come from user login or selection.
// Assuming 'BatteryBoss Online' has retailer_id = 1 after your population script runs.
// You might need to adjust this based on the actual ID in your 'retailers' table.
$defaultRetailerId = 1;

// --- Car Logo Mapping ---
// This array maps cleaned-up car make names (as stored in your DB and returned by getCarMakes())
// to their corresponding image filenames in the 'assets/imgs/carsmake/' directory.
$carLogoMap = [
    'Force Motors' => '1034_force-logo.png',
    'Hyundai' => '10_10_10_Hyundai_2.PNG',
    'Mahindra' => '12_12_12_mahindra-logo1.png',
    'Maruti Suzuki' => '13_13_13_maruti-suzuki.png',
    'Mercedes-Benz' => '14_14_14_benz.png', // Assuming 'Mercedes-Benz' maps to 'benz.png'
    'Nissan' => '15_15_15_nissian.png',
    'Renault' => '16_16_16_renault_logo.png',
    'KIA' => '1752_KIA.png',
    'MG' => '1775_MG.png',
    'Skoda' => '17_17_17_skoda.png',
    'Tata Motors' => 'tatathumb.png',
    'Audi' => '1_1_1_audi_logo.png',
    'Volvo' => '20_20_20_Volvo_Cars_logo1.png',
    'Volkswagen' => '21_Volkswagen.png',
    'BMW' => '2_2_bmw.png',
    'Jaguar' => '373_373_Jaguar-logo.png',
    'Porsche' => '374_374_Porsche-logo.png',
    'Chevrolet' => '3_3_Chevrolet_Logo.png',
    'MINI' => '658_658_MINI-logo.png',
    'JEEP' => 'Jeep.png',
    'Aston Martin' => '777_777_Aston-Martin-logo.png',
    'Bentley' => '782_782_Bentley.png',
    'Daewoo' => '788_788_Daewoo-logo.png',
    'Mitsubishi' => 'Mitsubishi.png',
    'ICML' => '795_795_ICML-logo.png',
    'Isuzu' => '801_801_Isuzu-logo.png', // Or '804_804_Isuzu-logo.png' if preferred
    'Lamborghini' => '808_808_Lamborghini-logo.png',
    'Lexus' => '812_812_Lexus-logo.png',
    'Maserati' => '820_820_Maserati-logo.png',
    'Land Rover' => '844_844_Land-Rover-logo.png',
    'Ford' => '8_8_ford_logo1.png',
    'Premier' => '927_927_premier-car-logo.png',
    'Honda' => '9_9_9_honda-logo-transparent-background-8.png',
    'Fiat' => '7_7_fait.png',
	'Toyota' => '19_19_19_car_logo_PNG1665.png',
	'Hindustan Motors' => 'Hindustan Motors.png'
    // Add more mappings as needed for other car makes
];

// Default placeholder image if a logo is not found
$placeholderCarLogo = 'https://placehold.co/100x60/EAEAEA/333333?text=No+Logo';

// Get all car makes for initial display in the right pane
$allCarMakes = getCarMakes();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battery Finder</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS (now mostly handled by inline styles and Tailwind) -->
    <style>
	
	:root {
	  --background-color: #819A91; /* Deep Teal-Blue - A rich, vibrant dark background */
	  --default-color: #D1D8BE; /* Soft Off-White - Excellent readability on dark backgrounds */
	  --heading-color: #8D77AB; /* Pure White - For prominent headings and titles */
	  --accent-color: #27AE60; /* Vivid Emerald Green - Highly saturated and energetic, symbolizing growth */
	  --surface-color: #EEEFE0; /* Muted Teal-Blue - For cards, boxes, providing distinct separation */
	  --contrast-color: #819A91; /* Pure White - Ensures readability on accent and heading colors */
	}

        body {
            font-family: 'Inter', sans-serif;
            display: flex; /* Use flexbox for the main layout */
            min-height: 100vh;
            background-color: var(--background-color); /* bg-gray-100 */
        }
        .main-container {
            display: flex;
            flex-grow: 1;
            max-width: 1200px; /* Max width for content */
            margin: 0 auto; /* Center the container */
            padding: 1.5rem; /* p-6 */
            gap: 1.5rem; /* Space between panes */
        }
        .left-pane {
            width: 30%; /* Slightly wider for filters and battery list */
            min-width: 280px; /* Ensure it doesn't get too narrow */
            background-color: var(--surface-color); /* bg-white */
            border-radius: 0.75rem; /* rounded-xl */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* shadow-lg */
            padding: 1.5rem; /* p-6 */
            display: flex;
            flex-direction: column;
            overflow-y: auto; /* Allow scrolling for battery list */
            max-height: calc(100vh - 3rem); /* Adjust based on padding/margin */
        }
        .right-pane {
            flex-grow: 1; /* Takes remaining space */
            background-color: var(--surface-color);
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            display: flex;
            flex-direction: column; /* Allow content to stack vertically */
            overflow-y: auto; /* Allow scrolling for batteries */
        }
        /* Car logo grid styling */
        .car-logo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); /* Responsive grid */
            gap: 1rem;
            justify-items: center;
            align-items: start;
            padding-top: 1rem;
        }
        .car-logo-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0.5rem;
            border-radius: 0.5rem;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            width: 100%; /* Ensure cards take full grid cell width */
            min-height: 100px; /* Ensure consistent height */
            cursor: pointer; /* Indicate clickable */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .car-logo-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .car-logo-card img {
            max-width: 80px;
            max-height: 50px;
            object-fit: contain;
            margin-bottom: 0.5rem;
        }
        .car-logo-card p {
            font-size: 0.8rem;
            font-weight: 700;
            color: #4b5563;
        }

        /* Battery card styling for the right pane */
        .battery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Responsive grid for batteries */
            gap: 1.5rem;
            padding-top: 1rem;
        }
        .battery-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1rem;
            background-color: #f9fafb; /* bg-gray-50 */
            border-radius: 0.75rem; /* rounded-xl */
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.06); /* shadow-md */
            border: 1px solid #e5e7eb; /* border border-gray-200 */
        }
        .battery-card img {
            width: 8rem; /* w-32 */
            height: 8rem; /* h-32 */
            object-fit: contain;
            border-radius: 0.5rem; /* rounded-lg */
            margin-bottom: 0.75rem; /* mb-3 */
            border: 1px solid #d1d5db; /* border border-gray-300 */
        }
        .battery-card h3 {
            font-size: 1.1rem; /* text-lg */
            font-weight: 600; /* font-semibold */
            color: #1f2937; /* text-gray-900 */
            margin-bottom: 0.25rem;
        }
        .battery-card p {
            font-size: 0.875rem; /* text-sm */
            color: #4b5563; /* text-gray-600 */
            line-height: 1.4;
        }
        .battery-card .price-section p {
            font-size: 0.95rem; /* Slightly larger for prices */
            line-height: 1.3;
        }
        .battery-card .price-section .font-extrabold {
            font-size: 1.1rem; /* Special price stands out */
        }

        /* Car list card styling for compatible cars display */
        .compatible-car-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            padding-top: 1rem;
        }

        .compatible-car-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0.75rem;
            background-color: #f9fafb;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid #e5e7eb;
        }
        .compatible-car-card img {
            max-width: 80px;
            max-height: 50px;
            object-fit: contain;
            margin-bottom: 0.5rem;
        }
        .compatible-car-card h4 {
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.25rem;
        }
        .compatible-car-card p {
            font-size: 0.85rem;
            color: #6b7280;
        }

        /* Battery details section when showing compatible cars */
        .selected-battery-details {
            background-color: #e0f2fe; /* blue-100 */
            border: 1px solid #90cdf4; /* blue-300 */
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        .selected-battery-details img {
            width: 5rem;
            height: 5rem;
            object-fit: contain;
            border-radius: 0.5rem;
            border: 1px solid #bfdbfe; /* blue-200 */
        }
        .selected-battery-details .details-text {
            flex-grow: 1;
        }
        .selected-battery-details .details-text h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e40af; /* blue-800 */
            margin-bottom: 0.25rem;
        }
        .selected-battery-details .details-text p {
            font-size: 0.9rem;
            color: #3b82f6; /* blue-600 */
            line-height: 1.3;
        }
        .selected-battery-details .details-text .price-info {
            font-size: 1rem;
            font-weight: 600;
            color: #10b981; /* green-500 */
            margin-top: 0.5rem;
        }


        /* Dropdown styling */
        select {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            font-size: 1rem;
            color: #374151;
            appearance: none; /* Remove default arrow */
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%236B7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.5em 1.5em;
            cursor: pointer;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        select:focus {
            outline: none;
            border-color: #3b82f6; /* blue-500 */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25); /* ring-blue-200 */
        }
        select:disabled {
            background-color: #f3f4f6; /* gray-100 */
            cursor: not-allowed;
            color: #9ca3af; /* gray-400 */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                padding: 1rem;
                gap: 1rem;
            }
            .left-pane {
                width: 100%;
                min-width: unset;
                max-height: unset; /* Let it expand naturally on mobile */
                margin-bottom: 1rem;
            }
            .right-pane {
                width: 100%;
                min-height: 200px; /* Give some height to the right pane */
            }
            .car-logo-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
            .battery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            .compatible-car-list {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
            .selected-battery-details {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <div class="main-container">
        <!-- Left Pane (Filters) -->
        <div class="left-pane">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Find Your Battery</h2>

            <div class="mb-6 pb-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">By Car</h3>
                <div class="mb-4">
                    <label for="car-make-select" class="block text-sm font-medium text-gray-700 mb-2">Car Maker:</label>
                    <select id="car-make-select" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Select Car Maker</option>
                        <!-- Options will be loaded here by JavaScript -->
                    </select>
                </div>

                <div class="mb-4">
                    <label for="car-model-select" class="block text-sm font-medium text-gray-700 mb-2">Car Model:</label>
                    <select id="car-model-select" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" disabled>
                        <option value="">Select Car Model</option>
                        <!-- Options will be loaded here by JavaScript -->
                    </select>
                </div>
            </div>

            <div class="mb-6 pt-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">By Battery</h3>
                <div class="mb-4">
                    <label for="battery-brand-select" class="block text-sm font-medium text-gray-700 mb-2">Battery Brand:</label>
                    <select id="battery-brand-select" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Select Battery Brand</option>
                        <!-- Options will be loaded here by JavaScript -->
                    </select>
                </div>

                <div class="mb-4">
                    <label for="battery-name-select" class="block text-sm font-medium text-gray-700 mb-2">Battery Name:</label>
                    <select id="battery-name-select" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" disabled>
                        <option value="">Select Battery Name</option>
                        <!-- Options will be loaded here by JavaScript -->
                    </select>
                </div>
            </div>

            <!-- This area in the left pane is now just an instruction -->
            <div id="left-pane-instruction" class="flex flex-col gap-3 flex-grow overflow-y-auto mt-auto pt-4 border-t">
                <p class="text-center text-gray-600">Use filters above to find batteries or compatible cars.</p>
            </div>
        </div>

        <!-- Right Pane (Initially Car Logos, then Car Models, then Battery Details, or Compatible Cars) -->
        <div class="right-pane">
            <button id="back-to-makes-btn" onclick="resetRightPaneToCarLogos()"
                    class="absolute top-6 right-6 px-4 py-2 bg-gray-200 text-gray-800 rounded-md shadow hover:bg-gray-300 transition duration-150 ease-in-out hidden">
                ← Back to Car Brands
            </button>

            <div id="car-logos-display" class="w-full">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Popular Car Brands</h2>
                <div class="car-logo-grid">
                    <?php foreach ($allCarMakes as $make): ?>
                        <?php
                        $logoFilename = $carLogoMap[$make] ?? null;
                        $imagePath = $logoFilename ? 'assets/imgs/carsmake1/' . $logoFilename : $placeholderCarLogo;
                        ?>
                        <div class="car-logo-card" onclick="handleCarBrandClick('<?php echo htmlspecialchars($make); ?>')">
                            <img src="<?php echo htmlspecialchars($imagePath); ?>"
                                 alt="<?php echo htmlspecialchars($make); ?> Logo"
                                 onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholderCarLogo); ?>';">
                            <p><?php echo htmlspecialchars($make); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="car-models-display" class="w-full hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Select a Car Model for <span id="selected-make-name" class="font-extrabold text-blue-700"></span></h2>
                <div id="car-model-grid-container" class="car-logo-grid">
                    <!-- Car models will be loaded here by JavaScript -->
                </div>
            </div>

            <div id="battery-details-right-pane" class="w-full hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Compatible Batteries</h2>
                <div id="battery-grid-container" class="battery-grid">
                    <!-- Batteries will be loaded here by JavaScript -->
                </div>
            </div>

            <div id="compatible-cars-right-pane" class="w-full hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Cars Compatible with Selected Battery</h2>
                <div id="selected-battery-display" class="selected-battery-details hidden">
                    <!-- Selected Battery Details will be loaded here by JavaScript -->
                </div>
                <div id="compatible-car-list-container" class="compatible-car-list">
                    <!-- Compatible cars will be loaded here by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Define a generic placeholder image URL for batteries if their specific image fails to load
        const genericBatteryPlaceholder = 'https://placehold.co/150x150/EAEAEA/333333?text=No+Image';
        const genericCarPlaceholder = 'https://placehold.co/100x60/EAEAEA/333333?text=Car'; // Placeholder for car images

        // PHP-defined car logo map for JavaScript to use
        const carLogoMap = <?php echo json_encode($carLogoMap); ?>;
        const placeholderCarLogo = '<?php echo htmlspecialchars($placeholderCarLogo); ?>';


        /**
         * Helper function to escape HTML entities for display.
         * @param {string} str The string to escape.
         * @returns {string} The escaped string.
         */
        function htmlspecialchars(str) {
            if (typeof str !== 'string') return str; // Return as is if not a string
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return str.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        /**
         * Helper function for number formatting (like PHP's number_format).
         * @param {number} number The number to format.
         * @param {number} decimals The number of decimal places.
         * @returns {string} The formatted number string.
         */
        function numberFormat(number, decimals) {
            return new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        }

        /**
         * Resets and disables the car-related filter dropdowns.
         */
        function resetCarFilters() {
            const carMakeSelect = document.getElementById('car-make-select');
            const carModelSelect = document.getElementById('car-model-select');

            carMakeSelect.value = '';
            carModelSelect.innerHTML = '<option value="">Select Car Model</option>';
            carModelSelect.disabled = true;
        }

        /**
         * Resets and disables the battery-related filter dropdowns.
         */
        function resetBatteryFilters() {
            const batteryBrandSelect = document.getElementById('battery-brand-select');
            const batteryNameSelect = document.getElementById('battery-name-select');

            batteryBrandSelect.value = '';
            batteryNameSelect.innerHTML = '<option value="">Select Battery Name</option>';
            batteryNameSelect.disabled = true;
        }

        /**
         * Manages the visibility of the right pane content sections.
         * @param {string} sectionToShow 'car-logos', 'car-models', 'batteries', or 'compatible-cars'
         */
        function showRightPaneSection(sectionToShow) {
            document.getElementById('car-logos-display').classList.add('hidden');
            document.getElementById('car-models-display').classList.add('hidden'); // New section
            document.getElementById('battery-details-right-pane').classList.add('hidden');
            document.getElementById('compatible-cars-right-pane').classList.add('hidden');
            document.getElementById('back-to-makes-btn').classList.add('hidden'); // Hide back button by default

            if (sectionToShow === 'car-logos') {
                document.getElementById('car-logos-display').classList.remove('hidden');
            } else if (sectionToShow === 'car-models') { // New section
                document.getElementById('car-models-display').classList.remove('hidden');
                document.getElementById('back-to-makes-btn').classList.remove('hidden'); // Show back button
            }
            else if (sectionToShow === 'batteries') {
                document.getElementById('battery-details-right-pane').classList.remove('hidden');
                document.getElementById('back-to-makes-btn').classList.remove('hidden'); // Show back button
            } else if (sectionToShow === 'compatible-cars') {
                document.getElementById('compatible-cars-right-pane').classList.remove('hidden');
                document.getElementById('back-to-makes-btn').classList.remove('hidden'); // Show back button
            }
        }

        /**
         * Resets the right pane to show initial car logos.
         */
        function resetRightPaneToCarLogos() {
            resetCarFilters();
            resetBatteryFilters();
            showRightPaneSection('car-logos');
        }

        // --- Car Filter Functions ---

        /**
         * Loads car makes into the dropdown.
         */
        async function loadCarMakesIntoDropdown() {
            const carMakeSelect = document.getElementById('car-make-select');
            carMakeSelect.innerHTML = '<option value="">Loading Car Makers...</option>'; // Loading state

            try {
                const response = await fetch('get_car_makes_ajax.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const makes = await response.json();

                carMakeSelect.innerHTML = '<option value="">Select Car Maker</option>'; // Reset
                if (makes.length > 0) {
                    makes.forEach(make => {
                        const option = document.createElement('option');
                        option.value = make;
                        option.textContent = make;
                        carMakeSelect.appendChild(option);
                    });
                } else {
                    carMakeSelect.innerHTML = '<option value="">No Car Makers Found</option>';
                }
            } catch (error) {
                console.error('Error loading car makes:', error);
                carMakeSelect.innerHTML = '<option value="">Error loading makers</option>';
            }
        }

        /**
         * Handles the change event for the car make dropdown.
         * @returns {HTMLSelectElement} The car model select element after it's populated.
         */
        async function onMakeSelectChange() {
            const selectedMake = document.getElementById('car-make-select').value;
            const carModelSelect = document.getElementById('car-model-select');
            const batteryGridContainer = document.getElementById('battery-grid-container');

            // Reset model dropdown
            carModelSelect.innerHTML = '<option value="">Select Car Model</option>';
            carModelSelect.disabled = true;
            batteryGridContainer.innerHTML = ''; // Clear previous battery results

            // If a car make is selected, disable battery filters and load models
            if (selectedMake) {
                resetBatteryFilters(); // Reset battery filters when car filter is used
                await loadCarModelsIntoDropdown(selectedMake); // Await model loading
                carModelSelect.disabled = false; // Enable model dropdown once models are loaded
                showRightPaneSection('car-logos'); // Stay on car logos until model is selected (or show models if from tile click)
            } else {
                // If car make is unselected, reset everything and show car logos
                resetCarFilters(); // This will clear the make dropdown too
                showRightPaneSection('car-logos');
            }
            return carModelSelect; // Return the model select element
        }

        /**
         * Loads car models for a selected make into the dropdown.
         * @param {string} make The selected car make.
         */
        async function loadCarModelsIntoDropdown(make) {
            const carModelSelect = document.getElementById('car-model-select');
            carModelSelect.innerHTML = '<option value="">Loading Car Models...</option>'; // Loading state

            try {
                const response = await fetch(`get_car_models_ajax.php?make=${encodeURIComponent(make)}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const models = await response.json();

                carModelSelect.innerHTML = '<option value="">Select Car Model</option>'; // Reset
                if (models.length > 0) {
                    models.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model;
                        option.textContent = model;
                        carModelSelect.appendChild(option);
                    });
                } else {
                    carModelSelect.innerHTML = '<option value="">No Car Models Found</option>';
                }
                return models; // Return models for use by displayCarModelsInRightPane
            } catch (error) {
                console.error('Error loading car models:', error);
                carModelSelect.innerHTML = '<option value="">Error loading models</option>';
                return []; // Return empty array on error
            }
        }

        /**
         * Handles the change event for the car model dropdown.
         */
        async function onModelSelectChange() {
            const selectedMake = document.getElementById('car-make-select').value;
            const selectedModel = document.getElementById('car-model-select').value;

            if (selectedMake && selectedModel) {
                showRightPaneSection('batteries');
                await displayBatteriesInRightPane(selectedMake, selectedModel);
            } else {
                // If model is unselected, revert to showing car models for the current make
                // Or if no make is selected, go back to car logos
                if (selectedMake) {
                    showRightPaneSection('car-models');
                    // Re-display models if user deselects from dropdown
                    displayCarModelsInRightPane(selectedMake);
                } else {
                    showRightPaneSection('car-logos');
                }
            }
        }

        /**
         * Handles the click event on a car brand tile.
         * @param {string} make The car make that was clicked.
         */
        async function handleCarBrandClick(make) {
            resetBatteryFilters(); // Clear battery filters first
            const carMakeSelect = document.getElementById('car-make-select');

            carMakeSelect.value = make; // Set the car make dropdown value

            // Load models into dropdown AND display them as tiles
            await displayCarModelsInRightPane(make);
            carMakeSelect.disabled = false; // Ensure make dropdown is enabled after selection

            showRightPaneSection('car-models'); // Show the models section
        }

        /**
         * Displays car models as clickable tiles in the right pane.
         * @param {string} make The selected car make.
         */
        async function displayCarModelsInRightPane(make) {
            const carModelGridContainer = document.getElementById('car-model-grid-container');
            const selectedMakeNameSpan = document.getElementById('selected-make-name');
            selectedMakeNameSpan.textContent = htmlspecialchars(make);
            carModelGridContainer.innerHTML = '<p class="text-center text-gray-600 col-span-full">Loading models...</p>';

            try {
                const models = await loadCarModelsIntoDropdown(make); // Reuse function to get models
                carModelGridContainer.innerHTML = ''; // Clear loading message

                if (models.length > 0) {
                    models.forEach(model => {
                        const modelCard = document.createElement('div');
                        modelCard.className = 'car-logo-card'; // Reusing styling
                        modelCard.onclick = () => handleCarModelClick(make, model);
						const logoFilename = carLogoMap[make] || null;
						const imagePath = logoFilename ? 'assets/imgs/carsmake1/' + logoFilename : placeholderCarLogo;

                        modelCard.innerHTML = `
                            <img src="${htmlspecialchars(imagePath)}"
                                 alt="${htmlspecialchars(model)} Image"
                                 onerror="this.onerror=null;this.src='${htmlspecialchars(placeholderCarLogo)}';">
                            <p>${htmlspecialchars(model)}</p>
                        `;
                        carModelGridContainer.appendChild(modelCard);
                    });
                } else {
                    carModelGridContainer.innerHTML = '<p class="text-center text-gray-600 col-span-full">No models found for this brand.</p>';
                }
            } catch (error) {
                console.error('Error displaying car models:', error);
                carModelGridContainer.innerHTML = `<p class="text-center text-red-500 col-span-full">Error loading models: ${error.message}</p>`;
            }
        }

        /**
         * Handles the click event on a car model tile.
         * @param {string} make The car make of the clicked model.
         * @param {string} model The car model that was clicked.
         */
        async function handleCarModelClick(make, model) {
            const carMakeSelect = document.getElementById('car-make-select');
            const carModelSelect = document.getElementById('car-model-select');

            carMakeSelect.value = make; // Ensure make is selected
            // Await to ensure models are loaded in dropdown before setting value
            await loadCarModelsIntoDropdown(make);
            carModelSelect.value = model; // Set the model dropdown value

            await onModelSelectChange(); // Trigger the display of compatible batteries
        }


        /**
         * Loads compatible batteries for a selected make and model and displays them in the right pane.
         * @param {string} make The selected car make.
         * @param {string} model The selected car model variant.
         */
        async function displayBatteriesInRightPane(make, model) {
            const batteryGridContainer = document.getElementById('battery-grid-container');
            batteryGridContainer.innerHTML = '<p class="text-center text-gray-600 col-span-full">Loading batteries...</p>';

            try {
                const response = await fetch(`get_batteries_ajax.php?make=${encodeURIComponent(make)}&model_variant=${encodeURIComponent(model)}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const batteries = await response.json();

                batteryGridContainer.innerHTML = ''; // Clear loading message

                if (batteries.length > 0) {
                    batteries.forEach(battery => {
                        const batteryCard = document.createElement('div');
                        batteryCard.className = 'battery-card';

                        let rawImagePath = battery.local_image_path;
                        let displayImagePath;

                        if (rawImagePath) {
                            // Replace Windows backslashes with forward slashes for web paths
                            rawImagePath = rawImagePath.replace(/\\/g, '/');
                            // Encode the URI component to handle spaces and other special characters
                            displayImagePath = encodeURI(rawImagePath);
                        } else {
                            displayImagePath = genericBatteryPlaceholder;
                        }

                        batteryCard.innerHTML = `
                            <img src="${displayImagePath}"
                                 alt="${htmlspecialchars(battery.name || 'Battery Image')}"
                                 onerror="this.onerror=null;this.src='${genericBatteryPlaceholder}';">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">${htmlspecialchars(battery.name || 'N/A')}</h3>
                            <p class="text-sm text-gray-600">Brand: ${htmlspecialchars(battery.brand || 'N/A')}</p>
                            <p class="text-sm text-gray-600">Capacity: ${htmlspecialchars(battery.capacity_ah || 'N/A')} AH</p>
                            <p class="text-sm text-gray-600">Total Warranty: ${htmlspecialchars(battery.total_warranty_months || 'N/A')} Months</p>
                            ${ (battery.full_replacement_warranty_months || battery.pro_rata_warranty_months) ?
                                `<p class="text-xs text-gray-500">
                                    (${htmlspecialchars(battery.full_replacement_warranty_months || '0')} Full +
                                    ${htmlspecialchars(battery.pro_rata_warranty_months || '0')} Pro Rata)
                                </p>` : ''
                            }
                            <div class="mt-2 w-full price-section">
                                ${ battery.price_inr ? `<p class="text-base font-bold text-gray-800">MRP: ₹${numberFormat(battery.price_inr, 2)}</p>` : '' }
                                ${ battery.special_price_inr ? `<p class="text-lg font-extrabold text-green-700">Special Price: ₹${numberFormat(battery.special_price_inr, 2)}</p>` : '' }
                                ${ battery.price_with_exchange_inr ? `<p class="text-sm text-blue-700">With Old Battery: ₹${numberFormat(battery.price_with_exchange_inr, 2)}</p>` : '' }
                                ${ battery.price_without_exchange_inr ? `<p class="text-sm text-blue-700">Without Old Battery: ₹${numberFormat(battery.price_without_exchange_inr, 2)}</p>` : '' }
                            </div>
                        `;
                        batteryGridContainer.appendChild(batteryCard);
                    });
                } else {
                    batteryGridContainer.innerHTML = '<p class="text-center text-gray-600 col-span-full">No compatible batteries found for this model.</p>';
                }
            } catch (error) {
                console.error('Error loading batteries:', error);
                batteryGridContainer.innerHTML = `<p class="text-center text-red-500 col-span-full">Error loading batteries: ${error.message}</p>`;
            }
        }

        // --- Battery Filter Functions ---

        /**
         * Loads battery brands into the dropdown.
         */
        async function loadBatteryBrandsIntoDropdown() {
            const batteryBrandSelect = document.getElementById('battery-brand-select');
            batteryBrandSelect.innerHTML = '<option value="">Loading Brands...</option>';

            try {
                const response = await fetch('get_battery_brands_ajax.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const brands = await response.json();

                batteryBrandSelect.innerHTML = '<option value="">Select Battery Brand</option>';
                if (brands.length > 0) {
                    brands.forEach(brand => {
                        const option = document.createElement('option');
                        option.value = brand;
                        option.textContent = brand;
                        batteryBrandSelect.appendChild(option);
                    });
                } else {
                    batteryBrandSelect.innerHTML = '<option value="">No Brands Found</option>';
                }
            } catch (error) {
                console.error('Error loading battery brands:', error);
                batteryBrandSelect.innerHTML = '<option value="">Error loading brands</option>';
            }
        }

        /**
         * Handles the change event for the battery brand dropdown.
         */
        async function onBatteryBrandSelectChange() {
            const selectedBrand = document.getElementById('battery-brand-select').value;
            const batteryNameSelect = document.getElementById('battery-name-select');
            const compatibleCarListContainer = document.getElementById('compatible-car-list-container');
            const selectedBatteryDisplay = document.getElementById('selected-battery-display');


            // Reset battery name dropdown
            batteryNameSelect.innerHTML = '<option value="">Select Battery Name</option>';
            batteryNameSelect.disabled = true;
            compatibleCarListContainer.innerHTML = ''; // Clear previous car results
            selectedBatteryDisplay.classList.add('hidden'); // Hide battery details


            // If a battery brand is selected, disable car filters and load battery names
            if (selectedBrand) {
                resetCarFilters(); // Reset car filters when battery filter is used
                await loadBatteryNamesIntoDropdown(selectedBrand);
                batteryNameSelect.disabled = false; // Enable battery name dropdown
                showRightPaneSection('car-logos'); // Stay on car logos until battery name is selected
            } else {
                // If battery brand is unselected, reset everything and show car logos
                resetBatteryFilters(); // This will clear the brand dropdown too
                showRightPaneSection('car-logos');
            }
        }

        /**
         * Loads battery names for a selected brand into the dropdown.
         * @param {string} brand The selected battery brand.
         */
        async function loadBatteryNamesIntoDropdown(brand) {
            const batteryNameSelect = document.getElementById('battery-name-select');
            batteryNameSelect.innerHTML = '<option value="">Loading Battery Names...</option>';

            try {
                const response = await fetch(`get_battery_names_by_brand_ajax.php?brand=${encodeURIComponent(brand)}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const names = await response.json();

                batteryNameSelect.innerHTML = '<option value="">Select Battery Name</option>';
                if (names.length > 0) {
                    names.forEach(name => {
                        const option = document.createElement('option');
                        option.value = name;
                        option.textContent = name;
                        batteryNameSelect.appendChild(option);
                    });
                } else {
                    batteryNameSelect.innerHTML = '<option value="">No Battery Names Found</option>';
                }
            } catch (error) {
                console.error('Error loading battery names:', error);
                batteryNameSelect.innerHTML = '<option value="">Error loading names</option>';
            }
        }

        /**
         * Handles the change event for the battery name dropdown.
         */
        async function onBatteryNameSelectChange() {
            const selectedBatteryName = document.getElementById('battery-name-select').value;
            const selectedBatteryDisplay = document.getElementById('selected-battery-display');

            if (selectedBatteryName) {
                showRightPaneSection('compatible-cars');
                selectedBatteryDisplay.classList.remove('hidden'); // Ensure battery details section is visible

                // Fetch and display selected battery details
                await displaySelectedBatteryDetails(selectedBatteryName);
                // Then fetch and display compatible cars
                await displayCompatibleCarsInRightPane(selectedBatteryName);
            } else {
                // If battery name is unselected, revert to showing car logos
                showRightPaneSection('car-logos');
                selectedBatteryDisplay.classList.add('hidden'); // Hide battery details
                document.getElementById('compatible-car-list-container').innerHTML = ''; // Clear cars
            }
        }

        /**
         * Fetches and displays details of the selected battery.
         * @param {string} batteryName The name of the selected battery.
         */
        async function displaySelectedBatteryDetails(batteryName) {
            const selectedBatteryDisplay = document.getElementById('selected-battery-display');
            selectedBatteryDisplay.innerHTML = '<p class="text-center text-gray-600 w-full">Loading battery details...</p>';

            try {
                const response = await fetch(`get_battery_details_ajax.php?battery_name=${encodeURIComponent(batteryName)}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const battery = await response.json(); // Expecting a single battery object

                if (battery && !battery.error) {
                    let rawImagePath = battery.local_image_path;
                    let displayImagePath;

                    if (rawImagePath) {
                        rawImagePath = rawImagePath.replace(/\\/g, '/');
                        displayImagePath = encodeURI(rawImagePath);
                    } else {
                        displayImagePath = genericBatteryPlaceholder;
                    }

                    selectedBatteryDisplay.innerHTML = `
                        <img src="${displayImagePath}"
                             alt="${htmlspecialchars(battery.name || 'Battery Image')}"
                             onerror="this.onerror=null;this.src='${genericBatteryPlaceholder}';">
                        <div class="details-text">
                            <h3>${htmlspecialchars(battery.name || 'N/A')}</h3>
                            <p>Brand: ${htmlspecialchars(battery.brand || 'N/A')}</p>
                            <p>Capacity: ${htmlspecialchars(battery.capacity_ah || 'N/A')} AH</p>
                            <p>Warranty: ${htmlspecialchars(battery.total_warranty_months || 'N/A')} Months</p>
                            ${ (battery.full_replacement_warranty_months || battery.pro_rata_warranty_months) ?
                                `<p class="text-xs text-gray-500">
                                    (${htmlspecialchars(battery.full_replacement_warranty_months || '0')} Full +
                                    ${htmlspecialchars(battery.pro_rata_warranty_months || '0')} Pro Rata)
                                </p>` : ''
                            }
                            <p class="price-info">Special Price: ₹${numberFormat(battery.special_price_inr || 0, 2)}</p>
                        </div>
                    `;
                } else {
                    selectedBatteryDisplay.innerHTML = `<p class="text-center text-red-500 w-full">Battery details not found or error: ${battery.error || 'Unknown error'}</p>`;
                }
            } catch (error) {
                console.error('Error loading selected battery details:', error);
                selectedBatteryDisplay.innerHTML = `<p class="text-center text-red-500 w-full">Error loading battery details: ${error.message}</p>`;
            }
        }


        /**
         * Loads compatible cars for a selected battery name and displays them in the right pane.
         * @param {string} batteryName The selected battery name.
         */
        async function displayCompatibleCarsInRightPane(batteryName) {
            const compatibleCarListContainer = document.getElementById('compatible-car-list-container');
            compatibleCarListContainer.innerHTML = '<p class="text-center text-gray-600 col-span-full">Loading compatible cars...</p>';

            try {
                const response = await fetch(`get_cars_for_battery_ajax.php?battery_name=${encodeURIComponent(batteryName)}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const cars = await response.json();

                compatibleCarListContainer.innerHTML = ''; // Clear loading message

                if (cars.length > 0) {
                    cars.forEach(car => {
                        const carCard = document.createElement('div');
                        carCard.className = 'compatible-car-card';

                        const logoFilename = carLogoMap[car.make] || null;
                        const imagePath = logoFilename ? 'assets/imgs/carsmake/' + logoFilename : placeholderCarLogo;

                        carCard.innerHTML = `
                            <img src="${htmlspecialchars(imagePath)}"
                                 alt="${htmlspecialchars(car.make)} Logo"
                                 onerror="this.onerror=null;this.src='${htmlspecialchars(placeholderCarLogo)}';">
                            <h4>${htmlspecialchars(car.make)}</h4>
                            <p>${htmlspecialchars(car.model_variant)}</p>
                        `;
                        compatibleCarListContainer.appendChild(carCard);
                    });
                } else {
                    compatibleCarListContainer.innerHTML = '<p class="text-center text-gray-600 col-span-full">No cars found compatible with this battery.</p>';
                }
            } catch (error) {
                console.error('Error loading compatible cars:', error);
                compatibleCarListContainer.innerHTML = `<p class="text-center text-red-500 col-span-full">Error loading compatible cars: ${error.message}</p>`;
            }
        }


        // Event Listeners
        document.addEventListener('DOMContentLoaded', () => {
            loadCarMakesIntoDropdown(); // Populate car makes dropdown on page load
            loadBatteryBrandsIntoDropdown(); // Populate battery brands dropdown on page load

            document.getElementById('car-make-select').addEventListener('change', onMakeSelectChange);
            document.getElementById('car-model-select').addEventListener('change', onModelSelectChange);

            document.getElementById('battery-brand-select').addEventListener('change', onBatteryBrandSelectChange);
            document.getElementById('battery-name-select').addEventListener('change', onBatteryNameSelectChange);
        });
    </script>
</body>
</html>
