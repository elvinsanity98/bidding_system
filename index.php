<?php
// index.php

// Initialize items.csv if it doesn't exist
$itemsFile = 'items.csv';
if (!file_exists($itemsFile)) {
    $handle = fopen($itemsFile, 'w');
    fputcsv($handle, ['id', 'name', 'starting_price', 'photo_url']);
    // In index.php, inside the block that initializes items.csv
$items = [
    [1, 'Plastic Drum', 500, 'images/plastic_drum.png'],
    [2, 'Plastic Chair', 200, 'images/plastic_chair.png'],
    [3, 'Plastic Table', 300, 'images/plastic_table.png'],
    [4, 'Office Table', 500, 'images/office_table.png'],
    [5, 'Long Table', 500, 'images/long_table.png'],
    [6, 'Office Cabinet', 250, 'images/office_cabinet.png'],
    [7, 'Bamboo Long Chair', 600, 'images/bamboo_chair.png'],
    [8, 'Organ (Brand: Yamaha)', 2000, 'images/yamaha_organ.png'],
];
    foreach ($items as $item) {
        fputcsv($handle, $item);
    }
    fclose($handle);
}

// Initialize bids.csv if it doesn't exist
$bidsFile = 'bids.csv';
if (!file_exists($bidsFile)) {
    $handle = fopen($bidsFile, 'w');
    fputcsv($handle, ['item_id', 'bidder_name', 'bid_amount', 'timestamp']);
    fclose($handle);
}

// Initialize locked.csv if it doesn't exist
$lockedFile = 'locked.csv';
if (!file_exists($lockedFile)) {
    $handle = fopen($lockedFile, 'w');
    fputcsv($handle, ['item_id', 'bidder_name', 'final_bid']);
    fclose($handle);
}

// Function to get current highest bids
function getHighestBids($bidsFile) {
    $highestBids = [];
    if (($handle = fopen($bidsFile, 'r')) !== FALSE) {
        $headers = fgetcsv($handle); // Skip headers
        while (($data = fgetcsv($handle)) !== FALSE) {
            $itemId = $data[0];
            $bidAmount = (float)$data[2];
            if (!isset($highestBids[$itemId]) || $bidAmount > $highestBids[$itemId]['amount']) {
                $highestBids[$itemId] = [
                    'amount' => $bidAmount,
                    'bidder' => $data[1]
                ];
            }
        }
        fclose($handle);
    }
    return $highestBids;
}

// Function to get locked items
function getLockedItems($lockedFile) {
    $lockedItems = [];
    if (($handle = fopen($lockedFile, 'r')) !== FALSE) {
        $headers = fgetcsv($handle); // Skip headers
        while (($data = fgetcsv($handle)) !== FALSE) {
            $lockedItems[$data[0]] = [
                'bidder' => $data[1],
                'final_bid' => (float)$data[2]
            ];
        }
        fclose($handle);
    }
    return $lockedItems;
}

$highestBids = getHighestBids($bidsFile);
$lockedItems = getLockedItems($lockedFile);

// Read items
$items = [];
if (($handle = fopen($itemsFile, 'r')) !== FALSE) {
    $headers = fgetcsv($handle); // Skip headers
    while (($data = fgetcsv($handle)) !== FALSE) {
        $items[] = [
            'id' => $data[0],
            'name' => $data[1],
            'starting_price' => $data[2],
            'photo_url' => $data[3],
        ];
    }
    fclose($handle);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RBLI Bidding System by Bongzkie</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        body {
            background-color: #F0F4F8;
            color: #1E3A8A;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #1E3A8A;
            margin-bottom: 10px;
            font-size: 2.5rem;
            font-weight: 600;
        }
        p.subtitle {
            text-align: center;
            color: #3B82F6;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .item.locked {
            background: #E5E7EB;
            opacity: 0.9;
        }
        .item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .item-content {
            padding: 20px;
        }
        .item h2 {
            color: #1E3A8A;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .item p {
            color: #4B5563;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        .current-bid, .locked-message {
            color: #2563EB;
            font-weight: 600;
        }
        .bid-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }
        .bid-form input {
            padding: 10px;
            border: 1px solid #BFDBFE;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }
        .bid-form input:focus {
            outline: none;
            border-color: #2563EB;
        }
        .bid-form button {
            background-color: #2563EB;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .bid-form button:hover {
            background-color: #1E3A8A;
        }
        .admin-link {
            text-align: center;
            margin-top: 20px;
        }
        .admin-link a {
            color: #2563EB;
            text-decoration: none;
            font-weight: 500;
        }
        .admin-link a:hover {
            text-decoration: underline;
        }
    </style>
    <script src="script.js"></script>
</head>
<body>
    <div class="container">
        <h1>RBLI Bidding System by Bongzkie</h1>
        <p class="subtitle">Welcome to the live bidding auction for disposed items. Place your bids and see updates in real-time.</p>
        
        <div class="items-grid">
            <?php foreach ($items as $item): ?>
                <?php
                $itemId = $item['id'];
                $isLocked = isset($lockedItems[$itemId]);
                $currentBid = $isLocked ? $lockedItems[$itemId]['final_bid'] : (isset($highestBids[$itemId]) ? $highestBids[$itemId]['amount'] : $item['starting_price']);
                $currentBidder = $isLocked ? $lockedItems[$itemId]['bidder'] : (isset($highestBids[$itemId]) ? $highestBids[$itemId]['bidder'] : 'None');
                $minBid = $currentBid + 1; // Bid must be higher
                ?>
                <div class="item <?php echo $isLocked ? 'locked' : ''; ?>" data-item-id="<?php echo $itemId; ?>">
                    <img src="<?php echo $item['photo_url']; ?>" alt="<?php echo $item['name']; ?>">
                    <div class="item-content">
                        <h2><?php echo $item['name']; ?></h2>
                        <p>Starting Price: ₱<?php echo number_format($item['starting_price'], 2); ?></p>
                        <?php if ($isLocked): ?>
                            <p class="locked-message">Locked to: <span class="bidder"><?php echo $currentBidder; ?></span> at ₱<span class="bid-amount"><?php echo number_format($currentBid, 2); ?></span></p>
                        <?php else: ?>
                            <p class="current-bid">Current Highest Bid: ₱<span class="bid-amount"><?php echo number_format($currentBid, 2); ?></span> by <span class="bidder"><?php echo $currentBidder; ?></span></p>
                            <form class="bid-form" action="bid.php" method="POST">
                                <input type="hidden" name="item_id" value="<?php echo $itemId; ?>">
                                <input type="text" name="bidder_name" placeholder="Your Name" required>
                                <input type="number" name="bid_amount" min="<?php echo $minBid; ?>" placeholder="Bid Amount (min ₱<?php echo number_format($minBid, 2); ?>)" step="0.01" required>
                                <button type="submit">Place Bid</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="admin-link">
            <a href="admin.php">Admin: Lock Items</a>
        </div>
    </div>

    <script>
        // Start polling for updates
        startPolling();
    </script>
</body>
</html>