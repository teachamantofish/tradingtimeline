<?php

header('Access-Control-Allow-Origin: *');
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://sheets.googleapis.com/v4/spreadsheets/1ZhgPgVwcgnBc5cN3lYEupwDCFNSHpcI6-RUwQdWKN60/values/Trials?key=AIzaSyDhOS3VJZ66Utl0lnHbSK8gH0BXz-wxRoU');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($curl);

// Convert the response to an array
$responseArray = json_decode($response, true);

$data = [];
foreach ($responseArray['values'] as $cell) {
    // get specific cells and add create a variable
    $link = trim($cell[2]);
    $links = explode('/', $link);
    $nct = end($links);
    $fdaDesignation = trim($cell[9]);
    if ($fdaDesignation == 'None') {
        $fdaDesignation = 'None';
    }
    $platform = trim($cell[10]);
    if (strpos($platform, 'Immunocytokine') !== false) {
        if (!isset($cell[11])) {
            $partnerCompanies = null;
        } else {
            $partnerCompanies = trim($cell[11]);
        }
        $data[] = [
            'platform' => trim($cell[10]),
            'ibrxDrug' => trim($cell[6]),
            'partnerDrug' => trim($cell[7]),
            'indication' => trim($cell[0]),
            'phase' => trim($cell[5]),
            'fdaDesignation' => $fdaDesignation,
            'nct' => '<a href="' . $link . '">' . $nct . '</a>'
        ];
    }
                    // Sort the cells in each row and create a hierarchy to graph. 
                    // This is needed because there may be A1, A2, A1, A3, A1. We need A1, A1, A1, A2, A3.
                    // strcmp is case-sensitive. If you need case-insensitive sorting, consider using strcasecmp instead.
                    usort($data, function ($a, $b) {
                        return strcmp($a['platform'], $b['platform'])
                            ?: strcmp($a['ibrxDrug'], $b['ibrxDrug'])
                            ?: strcmp($a['partnerDrug'], $b['partnerDrug'])
                            ?: strcmp($a['indication'], $b['indication'])
                            ?: strcmp($a['phase'], $b['phase'])
                            ?: strcmp($a['nct'], $b['nct'])
                            ?: strcmp($a['fdaDesignation'], $b['fdaDesignation']);
                    });
}

$csv = [];
foreach ($data as $item) {
    $csv[] = implode(',', $item);
}

curl_close($curl);

// Output the CSV data
echo implode("\n", $csv);