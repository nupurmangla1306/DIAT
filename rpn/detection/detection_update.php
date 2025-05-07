<?php
// detection_update.php
header('Content-Type: application/json; charset=UTF-8');

$csvFile = __DIR__ . '/detection_data.csv';
$data    = json_decode(file_get_contents('php://input'), true);

if (!isset($data['detection']) || !is_numeric($data['detection'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing detection value.']);
    exit;
}

$detection = floatval($data['detection']);

// Load existing CSV or prepare new structure
$fileExists = file_exists($csvFile);
$rows = $fileExists ? array_map('str_getcsv', file($csvFile)) : [];

if (!$fileExists) {
    $rows[] = ['detection']; // Header row
}

$rows[] = [$detection]; // New detection value

// Save to CSV
$fp = fopen($csvFile, 'w');
foreach ($rows as $r) {
    fputcsv($fp, $r);
}
fclose($fp);

echo json_encode(['message' => 'Detection value stored successfully.']);
