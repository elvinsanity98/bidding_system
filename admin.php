<?php
// admin.php

$itemsFile = 'items.csv';
$bidsFile = 'bids.csv';
$lockedFile = 'locked.csv';

// Read items
$items = [];
if (($handle = fopen($itemsFile, 'r')) !== FALSE) {
    $headers = fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE) {
        $items[$data[0]] = ['name' => $data[1], 'starting_price' => $data[2]];
    }
    fclose($handle);
}

// Get highest bids
$highestBids = [];
if (($handle = fopen($bidsFile, 'r')) !== FALSE) {
    $headers = fgetcsv($handle);
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

// Get locked items
$lockedItems = [];
if (($handle = fopen($lockedFile, 'r')) !== FALSE) {
    $headers = fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE) {
        $lockedItems[$data[0]] = true;
    }
    fclose($handle);
}

// Handle locking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $itemId = (int)$_POST['item_id'];
    if (isset($items[$itemId]) && !isset($lockedItems[$itemId])) {
        $bidder = isset($highestBids[$itemId]) ? $highestBids[$itemId]['bidder'] : 'None';
        $finalBid = isset($highestBids[$itemId]) ? $highestBids[$itemId]['amount'] : $items[$itemId]['starting_price'];
        $handle = fopen($lockedFile, 'a');
        fputcsv($handle, [$itemId, $bidder, $finalBid]);
        fclose($handle);
        header('Location: admin.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - RBLI Bidding System</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #1E3A8A;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #BFDBFE;
        }
        th {
            background: #2563EB;
            color: white;
        }
        tr:hover {
            background: #E5E7EB;
        }
        button {
            background-color: #2563EB;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #1E3A8A;
        }
        button:disabled {
            background-color: #6B7280;
            cursor: not-allowed;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #2563EB;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin - Lock Items</h1>
        <table>
            <tr>
                <th>Item</th>
                <th>Current Highest Bid</th>
                <th>Bidder</th>
                <th>Action</th>
            </tr>
            <?php foreach ($items as $itemId => $item): ?>
                <tr>
                    <td><?php echo $item['name']; ?></td>
                    <td>₱<?php echo number_format(isset($highestBids[$itemId]) ? $highestBids[$itemId]['amount'] : $item['starting_price'], 2); ?></td>
                    <td><?php echo isset($highestBids[$itemId]) ? $highestBids[$itemId]['bidder'] : 'None'; ?></td>
                    <td>
                        <?php if (!isset($lockedItems[$itemId])): ?>
                            <form action="admin.php" method="POST">
                                <input type="hidden" name="item_id" value="<?php echo $itemId; ?>">
                                <button type="submit">Lock Item</button>
                            </form>
                        <?php else: ?>
                            <button disabled>Locked</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="back-link">
            <a href="index.php">Back to Bidding</a>
        </div>
    </div>
</body>
</html>