<?php
include 'includes/header.php';
require 'includes/db.php';

$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>

<main class="flex-grow max-w-screen-xl mx-auto px-12 md:px-12" style="width: 100%">
    <h2 class="text-3xl font-bold mb-4 text-gray-800">Dashboard</h2>
    <p class="mb-6 text-gray-600">Welcome to the QR Product Dashboard.</p>

    <a href="create_product.php"
       class="inline-block px-5 py-3 bg-teal-600 text-white font-semibold rounded hover:bg-teal-700 transition-colors mb-6">
        + Create New Product
    </a>

    <?php if (count($products) > 0): ?>
        <table class="w-full border text-left text-sm text-gray-700">
            <thead class="bg-gray-100 font-semibold">
            <tr>
                <th class="px-4 py-2 border-b">ID</th>
                <th class="px-4 py-2 border-b">Product Name</th>
                <th class="px-4 py-2 border-b">QR</th>
                <th class="px-4 py-2 border-b">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td class="px-4 py-2 border-b"><?= htmlspecialchars($product['id']) ?></td>
                    <td class="px-4 py-2 border-b"><?= htmlspecialchars($product['name']) ?></td>
                    <td class="px-4 py-2 border-b">
                        <?php if (!empty($product['qr_code']) && file_exists(__DIR__ . '/' . $product['qr_code'])): ?>
                            <img src="assets/qr/product_<?= $product['id'] ?>.png" alt="QR Code" class="h-16 object-contain" />

                        <?php else: ?>
                            <span class="text-gray-500 italic">No image</span>
                        <?php endif; ?>
                    </td>


                    <td class="px-4 py-2 border-b space-x-4">
                        <a href="view_product.php?id=<?= $product['id'] ?>" class="text-blue-600 hover:underline">View</a>
                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="text-yellow-600 hover:underline">Edit</a>
                        <a href="delete_product.php?id=<?= $product['id'] ?>"
                           onclick="return confirm('Are you sure you want to delete this product?')"
                           class="text-red-600 hover:underline">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-gray-500 mt-4">No products found. Start by creating one.</p>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
