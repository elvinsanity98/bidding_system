<?php
// bid.php

$bidsFile = 'bids.csv';
$itemsFile = 'items.csv';
$lockedFile = 'locked.csv';

// Check if item is locked
$lockedItems = [];
if (($handle = fopen($lockedFile, 'r')) !== FALSE) {
    $headers = fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE) {
        $lockedItems[$data[0]] = true;
    }
    fclose($handle);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = (int)$_POST['item_id'];
    $bidderName = trim($_POST['bidder_name']);
    $bidAmount = (float)$_POST['bid_amount'];
    $timestamp = date('Y-m-d H:i:s');

    if (isset($lockedItems[$itemId])) {
        echo "This item is locked and no longer accepts bids.";
        exit;
    }

    if (empty($bidderName) || $bidAmount <= 0) {
        echo "Invalid bid.";
        exit;
    }

    // Get current highest bid
    $highestBids = [];
    if (($handle = fopen($bidsFile, 'r')) !== FALSE) {
        $headers = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== FALSE) {
            $currItemId = $data[0];
            $currBid = (float)$data[2];
            if (!isset($highestBids[$currItemId]) || $currBid > $highestBids[$currItemId]) {
                $highestBids[$currItemId] = $currBid;
            }
        }
        fclose($handle);
    }

    // Get starting price if no bids
    $startingPrice = 0;
    if (($handle = fopen($itemsFile, 'r')) !== FALSE) {
        $headers = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== FALSE) {
            if ((int)$data[0] === $itemId) {
                $startingPrice = (float)$data[2];
                break;
            }
        }
        fclose($handle);
    }

    $currentHighest = isset($highestBids[$itemId]) ? $highestBids[$itemId] : $startingPrice;

    if ($bidAmount <= $currentHighest) {
        echo "Bid must be higher than current highest bid of ₱$currentHighest.";
        exit;
    }

    // Append new bid
    $handle = fopen($bidsFile, 'a');
    fputcsv($handle, [$itemId, $bidderName, $bidAmount, $timestamp]);
    fclose($handle);

    // Redirect back to index
    header('Location: index.php');
    exit;
}
?>