<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

/**
 * Determines which bins are due based on the week number
 * @return array Bin collection data
 */
function getBinCollection() {
    $currentWeek = date('W');
    $isEvenWeek = $currentWeek % 2 === 0;
    
    // Didcot alternates between:
    // Even weeks: Green (recycling) and Grey (general waste)
    // Odd weeks: Green (recycling) and Brown (garden/food waste)
    return [
        'date' => date('D j M'),
        'bins' => [
            'green' => true, // Recycling is collected every week
            'grey' => $isEvenWeek,
            'brown' => !$isEvenWeek
        ]
    ];
}

echo json_encode(getBinCollection()); 