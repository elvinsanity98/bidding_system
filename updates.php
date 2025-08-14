<?php
// updates.php

$bidsFile = 'bids.csv';
$itemsFile = 'items.csv';
$lockedFile = 'locked.csv';

// Get all highest bids and bidders
$highestBids = [];
$highestBidders = [];
if (($handle = fopen($bidsFile, 'r')) !== FALSE) {
    $headers = fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE) {
        $itemId = (int)$data[0];
        $bidAmount = (float)$data[2];
        $bidder = $data[1];
        if (!isset($highestBids[$itemId]) || $bidAmount > $highestBids[$itemId]) {
            $highestBids[$itemId] = $bidAmount;
            $highestBidders[$itemId] = $bidder;
        }
    }
    fclose($handle);
}

// Get starting prices for items without bids
$startingPrices = [];
if (($handle = fopen($itemsFile, 'r')) !== FALSE) {
    $headers = fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE) {
        $itemId = (int)$data[0];
        $startingPrices[$itemId] = (float)$data[2];
    }
    fclose($handle);
}

// Get locked items
$lockedItems = [];
if (($handle = fopen($lockedFile, 'r')) !== FALSE) {
    $headers = fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE) {
        $itemId = (int)$data[0];
        $lockedItems[$itemId] = [
            'bidder' => $data[1],
            'final_bid' => (float)$data[2]
        ];
    }
    fclose($handle);
}

// Prepare JSON data
$data = [];
foreach ($startingPrices as $itemId => $startPrice) {
    $isLocked = isset($lockedItems[$itemId]);
    $data[$itemId] = [
        'amount' => $isLocked ? $lockedItems[$itemId]['final_bid'] : (isset($highestBids[$itemId]) ? $highestBids[$itemId] : $startPrice),
        'bidder' => $isLocked ? $lockedItems[$itemId]['bidder'] : (isset($highestBidders[$itemId]) ? $highestBidders[$itemId] : 'None'),
        'locked' => $isLocked
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>