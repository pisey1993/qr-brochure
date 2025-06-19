<?php
require 'vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

$productId = $_GET['id'] ?? null;

if (!$productId || !file_exists('data.json')) {
    die("Product not found.");
}

$products = json_decode(file_get_contents('data.json'), true);

if (!isset($products[$productId])) {
    die("Product not found.");
}

$product = $products[$productId];

// Generate QR code for product URL (link back to this page)
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
    "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$qrResult = Builder::create()
    ->writer(new PngWriter())
    ->data($url)
    ->size(300)
    ->margin(10)
    ->build();

$qrDataUri = $qrResult->getDataUri();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Detail - <?= htmlspecialchars($product['name']) ?></title>
</head>
<body>
<a href="index.php">Back to Products</a>
<h1>Product: <?= htmlspecialchars($product['name']) ?></h1>

<h2>QR Code</h2>
<img src="<?= $qrDataUri ?>" alt="QR Code">

<h2>Sub Records</h2>
<?php if (empty($product['sub_records'])): ?>
    <p>No sub records found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($product['sub_records'] as $sub): ?>
            <li>
                <strong><?= htmlspecialchars($sub['title']) ?></strong><br>
                Files:
                <ul>
                    <?php foreach (['khmer', 'english', 'chinese'] as $lang): ?>
                        <li>
                            <?= ucfirst($lang) ?>:
                            <?php if (!empty($sub['files'][$lang])): ?>
                                <a href="<?= htmlspecialchars($sub['files'][$lang]) ?>" target="_blank">View File</a>
                            <?php else: ?>
                                No file
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</body>
</html>
