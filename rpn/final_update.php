<?php
header('Content-Type: application/json');

function read_csv(string $path): array {
    if (!file_exists($path)) return [];
    $lines = array_filter(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    return array_map('str_getcsv', $lines);
}

// Paths to input CSVs
$sevCsv = __DIR__ . '/severity/severity_data.csv';
$occCsv = __DIR__ . '/occurrence/occurrence_data.csv';
$detCsv = __DIR__ . '/detection/detection_data.csv';

// Load CSVs into arrays
$sev = read_csv($sevCsv);
$occ = read_csv($occCsv);
$det = read_csv($detCsv);

// Validate structure
if (empty($sev) || empty($occ) || empty($det)) {
    http_response_code(500);
    echo json_encode(['error' => 'One or more CSV files are missing or empty.']);
    exit;
}

$activities = $sev[0];  // Activity names from header
$n = count($activities);  // Number of activities
$userCount = min(count($sev), count($occ), count($det)) - 1;  // Number of users (rows after header)

// Initialize totals
$sumSev = $sumOcc = $sumDet = array_fill(0, $n, 0);

// Accumulate totals from rows
for ($r = 1; $r <= $userCount; $r++) {
    for ($c = 0; $c < $n; $c++) {
        $sumSev[$c] += isset($sev[$r][$c]) && is_numeric($sev[$r][$c]) ? floatval($sev[$r][$c]) : 0;
        $sumOcc[$c] += isset($occ[$r][$c]) && is_numeric($occ[$r][$c]) ? floatval($occ[$r][$c]) : 0;
        $sumDet[$c] += isset($det[$r][$c]) && is_numeric($det[$r][$c]) ? floatval($det[$r][$c]) : 0;
    }
}

// Compute averages
$avgSev = $avgOcc = $avgDet = [];
for ($i = 0; $i < $n; $i++) {
    $avgSev[$i] = $userCount ? round($sumSev[$i] / $userCount, 2) : 0;
    $avgOcc[$i] = $userCount ? round($sumOcc[$i] / $userCount, 2) : 0;
    $avgDet[$i] = $userCount ? round($sumDet[$i] / $userCount, 2) : 0;
}

// Build RPN results
$results = [];
for ($i = 0; $i < $n; $i++) {
    $rpn = (int) round($avgSev[$i] * $avgOcc[$i] * $avgDet[$i]);
    $results[] = [
        'activity'   => $activities[$i],
        'severity'   => number_format($avgSev[$i], 2),
        'occurrence' => number_format($avgOcc[$i], 2),
        'detection'  => number_format($avgDet[$i], 2),
        'rpn'        => $rpn
    ];
}

// Sort results by RPN descending
usort($results, fn($a, $b) => $b['rpn'] <=> $a['rpn']);

// Assign rank
foreach ($results as $idx => &$row) {
    $row['rank'] = $idx + 1;
}
unset($row);

// Write to CSV
$outCsv = __DIR__ . '/final_outcome.csv';
$fp = fopen($outCsv, 'w');
fputcsv($fp, ['Activity', 'Severity', 'Occurrence', 'Detection', 'RPN', 'Rank']);
foreach ($results as $row) {
    fputcsv($fp, [
        $row['activity'],
        $row['severity'],
        $row['occurrence'],
        $row['detection'],
        $row['rpn'],
        $row['rank']
    ]);
}
fclose($fp);

// Return as JSON
echo json_encode([
    'severity_data'   => array_combine($activities, $avgSev),
    'occurrence_data' => array_combine($activities, $avgOcc),
    'detection_data'  => array_combine($activities, $avgDet),
    'results'         => $results
]);
