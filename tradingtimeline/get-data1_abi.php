<?php

header('Access-Control-Allow-Origin: *');
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://sheets.googleapis.com/v4/spreadsheets/1ZhgPgVwcgnBc5cN3lYEupwDCFNSHpcI6-RUwQdWKN60/values/Trials?key=AIzaSyDhOS3VJZ66Utl0lnHbSK8gH0BXz-wxRoU');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($curl);
curl_close($curl);

$dataFromServer = json_decode($response, true);

$type = 'A';

if(isset($_GET['type'])){
    $type = $_GET['type'];
}

$filteredData = [];
foreach ($dataFromServer['values'] as $index => $cell) {

    if ($index === 0) {
        continue;
    }
    
    $link = trim($cell[2]);
    $links = explode('/', $link);
    $nct = end($links);
    $fdaDesignation = trim($cell[9]) === 'None' ? 'None' : trim($cell[9]);
    $platform = trim($cell[10]);
    
    if (strpos($platform, 'Immunocytokine') !== false) {
        $partnerCompanies = !isset($cell[11]) ? null : trim($cell[11]);
        
        $entry = [
            'platform' => $platform,
            'ibrxDrug' => trim($cell[6]),
            'partnerDrug' => trim($cell[7]),
            'indication' => trim($cell[0]),
            'nct' => '<a href="' . $link . '">' . $nct . '</a>'
        ];
        
        if ($type === 'A') {
            $entry['phase'] = trim($cell[5]);
            $entry['fdaDesignation'] = $fdaDesignation;
        }
        
        $filteredData[] = $entry;
    }
}

function compare($a, $b) {
    $fields = ['platform', 'ibrxDrug', 'partnerDrug', 'indication'];
    global $type;
    if ($type === 'A') {
        $fields = array_merge($fields, ['phase', 'fdaDesignation']);
    }
    
    foreach ($fields as $field) {
        $cmp = strcmp($a[$field], $b[$field]);
        if ($cmp != 0) {
            return $cmp;
        }
    }
    return 0;
}

usort($filteredData, 'compare');

$csvData = array_map(function($item) {
    return implode(',', array_values($item));
}, $filteredData);

$filteredCsvData = [];

foreach ($csvData as $csvRow) {
    $filteredCsvData[] = $csvRow;
}

echo json_encode([
    'csv' => $filteredCsvData
]);