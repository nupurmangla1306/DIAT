<?php
// severity_update.php
header('Content-Type: application/json; charset=UTF-8');

$csvFile = __DIR__ . '/severity_data.csv';
$data    = json_decode(file_get_contents('php://input'), true);

if (!isset($data['severity']) || !is_numeric($data['severity'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing severity value.']);
    exit;
}

$severity = floatval($data['severity']);

// Load existing CSV or prepare new structure
$fileExists = file_exists($csvFile);
$rows = $fileExists ? array_map('str_getcsv', file($csvFile)) : [];

if (!$fileExists) {
    $rows[] = ['severity']; // Header row
}

$rows[] = [$severity]; // New severity value

// Save to CSV
$fp = fopen($csvFile, 'w');
foreach ($rows as $r) {
    fputcsv($fp, $r);
}
fclose($fp);

echo json_encode(['message' => 'Severity value stored successfully.']);
