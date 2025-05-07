<?php
// occurrence_update.php
header('Content-Type: application/json; charset=UTF-8');

$csvFile = __DIR__ . '/occurrence_data.csv';
$data    = json_decode(file_get_contents('php://input'), true);

if (!isset($data['occurrence']) || !is_numeric($data['occurrence'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing occurrence value.']);
    exit;
}

$occurrence = floatval($data['occurrence']);

// Load existing CSV or prepare new structure
$fileExists = file_exists($csvFile);
$rows = $fileExists ? array_map('str_getcsv', file($csvFile)) : [];

if (!$fileExists) {
    $rows[] = ['occurrence']; // Header row
}

$rows[] = [$occurrence]; // New occurrence value

// Save to CSV
$fp = fopen($csvFile, 'w');
foreach ($rows as $r) {
    fputcsv($fp, $r);
}
fclose($fp);

echo json_encode(['message' => 'Occurrence value stored successfully.']);
