<?php
require 'includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("❌ Missing product ID.");
}

try {
    // Get QR code file name from database
    $stmt = $pdo->prepare("SELECT qr_code FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception("❌ Product not found.");
    }

    // Delete QR code image if exists
    if (!empty($product['qr_code'])) {
        $qrPath = __DIR__ . '/assets/qr/' . $product['qr_code'];
        if (file_exists($qrPath)) {
            unlink($qrPath);
        }
    }

    // Get sub-record file paths
    $subs = $pdo->prepare("SELECT file_path FROM product_subs WHERE product_id = ?");
    $subs->execute([$id]);

    foreach ($subs->fetchAll() as $sub) {
        $file = $sub['file_path'];
        if (!empty($file)) {
            $fullPath = __DIR__ . '/' . $file;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    // Optionally: delete sub-record folders
    $subFolder = __DIR__ . "/uploads/sub_records/$id";
    if (is_dir($subFolder)) {
        // Recursively delete folder
        function deleteFolder($folder) {
            foreach (glob($folder . '/*') as $file) {
                if (is_dir($file)) {
                    deleteFolder($file);
                } else {
                    unlink($file);
                }
            }
            rmdir($folder);
        }
        deleteFolder($subFolder);
    }

    // Delete DB records
    $pdo->prepare("DELETE FROM product_subs WHERE product_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

    header("Location: index.php");
    exit;
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    exit;
}
