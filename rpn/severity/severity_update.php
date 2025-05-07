<?php
header('Content-Type: application/json; charset=UTF-8');

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Debugging: Output received data
file_put_contents('php://stderr', print_r($data, true));

// Check if data is valid
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'No data received.']);
    exit;
}

// File paths
$csvFile = __DIR__ . '/severity/severity_data.csv';

// Load existing CSV (or initialize)
$fileExists = file_exists($csvFile);
$rows = $fileExists ? array_map('str_getcsv', file($csvFile)) : [];

// Write to CSV logic (rest of the code)

// Success response
echo json_encode(['message' => 'Data stored successfully']);
