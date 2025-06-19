<?php
require 'includes/db.php';
require 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$id = $_GET['id'] ?? null;
if (!$id) {
    die('Invalid product ID.');
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST']; // e.g. localhost or yourdomain.com
$path = "/view_product.php?id=$id";

$url = "$protocol://$host$path";


try {
    // Create QR code instance
    $qr = new QrCode($url);

    // Create writer instance
    $writer = new PngWriter();

    // Generate QR code image result
    $result = $writer->write($qr);

    // Directory to save QR images
    $saveDir = "assets/qr/";
    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0755, true);
    }

    // File path for the QR code image
    $savePath = $saveDir . "product_{$id}.png";

    // Save the QR code PNG file
    $result->saveToFile($savePath);
} catch (Exception $e) {
    die('Error generating QR code: ' . htmlspecialchars($e->getMessage()));
}

?>

<?php include 'includes/header.php' ?>
    <main class="w-full max-w-screen-xl mx-auto mt-10 p-6 bg-white rounded shadow">

        <center>
            <h2 class="text-3xl font-bold mb-6 text-gray-800">QR Code Generated</h2>
            <img
                    src="<?= htmlspecialchars($savePath) ?>"
                    alt="QR Code for Product <?= htmlspecialchars($id) ?>"
                    class="mb-6 border border-gray-300 rounded"
                    width="250"
                    height="250"
            />

            <a
                    href="view_product.php?id=<?= htmlspecialchars($id) ?>"
                    class="inline-block px-6 py-3 bg-indigo-600 text-white font-semibold rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
                View Product Info
            </a>
        </center>
    </main>
<?php include 'includes/footer.php' ?>