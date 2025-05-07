<?php
header('Content-Type: application/json; charset=UTF-8');

// Get the raw POST data (sent by JavaScript)
$data = json_decode(file_get_contents('php://input'), true);

// Debugging: Output received data
file_put_contents('php://stderr', print_r($data, true));

// Check if data is valid
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'No data received.']);
    exit;
}

// Path to the CSV file
$csvFile = __DIR__ . '/severity/severity_data.csv';

// Load the existing CSV file (or initialize)
$fileExists = file_exists($csvFile);
$rows = $fileExists ? array_map('str_getcsv', file($csvFile)) : [];

// If the file does not exist or is empty, create a new header row
if (!$fileExists || empty($rows)) {
    $rows[] = array_keys($data);  // Assumes keys of $data are the column headers
}

// Extract the header and data rows
$headers = $rows[0];

// Update the values in the existing rows
foreach ($data as $parameter => $value) {
    // Check if the parameter exists in the headers
    $colIndex = array_search($parameter, $headers);
    if ($colIndex !== false) {
        // Update the value in the first empty row (for simplicity)
        $rows[1][$colIndex] = $value; // Assuming user data goes to row 1
    }
}

// Save the updated rows back to the CSV file
$fp = fopen($csvFile, 'w');
foreach ($rows as $row) {
    fputcsv($fp, $row);
}
fclose($fp);

// Return success response
echo json_encode(['message' => 'Data stored successfully']);
?>
