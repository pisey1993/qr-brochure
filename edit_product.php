<?php
require 'includes/db.php';

// Get product ID from URL, terminate if not found
$id = $_GET['id'] ?? null;
if (!$id) {
    die("Error: Missing product ID. Please provide a valid product ID to edit.");
}

// Fetch product details from the database
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    die("Error: Product not found with the provided ID.");
}

$success = null; // Variable to hold success/error messages

// --- Handle Deletion of a Sub-Record ---
if (isset($_GET['delete_sub'])) {
    $sub_id_to_delete = $_GET['delete_sub'];

    // First, fetch the sub-record to get its file_path if any
    $subStmt = $pdo->prepare("SELECT file_path FROM product_subs WHERE id = ? AND product_id = ?");
    $subStmt->execute([$sub_id_to_delete, $id]);
    $subToDelete = $subStmt->fetch();

    if ($subToDelete) {
        // Construct the full file path and delete the file from the server
        if (!empty($subToDelete['file_path'])) {
            $fullFilePath = __DIR__ . '/' . $subToDelete['file_path'];
            if (file_exists($fullFilePath)) {
                unlink($fullFilePath); // Delete the file
                // Also attempt to remove the parent directory if it's empty (sub_record_id directory)
                $parentDir = dirname($fullFilePath);
                // Check if directory exists and only contains '.' and '..'
                if (is_dir($parentDir) && count(array_diff(scandir($parentDir), array('.', '..'))) == 0) {
                    rmdir($parentDir);
                }
                // Also check and remove the product_id directory if empty
                $productDir = dirname($parentDir); // Get uploads/sub_records/{product_id}/
                if (is_dir($productDir) && count(array_diff(scandir($productDir), array('.', '..'))) == 0) {
                    rmdir($productDir);
                }
            }
        }

        // Delete the sub-record from the database
        $deleteStmt = $pdo->prepare("DELETE FROM product_subs WHERE id = ? AND product_id = ?");
        if ($deleteStmt->execute([$sub_id_to_delete, $id])) {
            $success = "✅ Sub-record deleted successfully!";
        } else {
            error_log("Failed to delete sub-record ID: $sub_id_to_delete for product ID: $id");
            $success = "❌ Failed to delete sub-record. Please try again.";
        }
    } else {
        $success = "⚠️ Sub-record not found or does not belong to this product.";
    }
    // Redirect to clear the GET parameters and prevent re-deletion on refresh
    header("Location: edit_product.php?id=$id" . ($success ? "&status=" . urlencode($success) : ""));
    exit;
}

// --- Handle Form Submission for Updating Product Name ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product_name'])) {
    $name = $_POST['name'] ?? ''; // Get product name from POST, default to empty string
    $name = trim($name); // Trim whitespace

    if (!empty($name)) {
        // Update product name in the database
        $stmt = $pdo->prepare("UPDATE products SET name = ? WHERE id = ?");
        if ($stmt->execute([$name, $id])) {
            $product['name'] = $name; // Update the product variable to reflect the new name immediately
            $success = "✅ Product name updated successfully!";
        } else {
            // Log database error if update fails
            error_log("Failed to update product name for ID: $id");
            $success = "❌ Failed to update product name. Please try again.";
        }
    } else {
        $success = "⚠️ Product name cannot be empty.";
    }
    // Redirect to prevent form resubmission on refresh
    header("Location: edit_product.php?id=$id" . ($success ? "&status=" . urlencode($success) : ""));
    exit;
}

// --- Handle Form Submission for Adding New Sub-Record ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sub_record'])) {
    $new_language_text = $_POST['new_language_text'] ?? '';
    $new_language_text = trim($new_language_text);

    if (!empty($new_language_text)) {
        $filePath = null; // Initialize file path to null for new sub-record

        // Insert sub-record first to get its ID, then handle file upload
        $stmt = $pdo->prepare("INSERT INTO product_subs (product_id, language_text, file_path) VALUES (?, ?, ?)");
        $stmt->execute([$id, $new_language_text, null]); // Insert with null file_path initially
        $newSubRecordId = $pdo->lastInsertId(); // Get the ID of the newly inserted sub-record

        // Define base upload directory
        $uploadBaseDir = __DIR__ . '/uploads/sub_records/';
        if (!is_dir($uploadBaseDir)) {
            mkdir($uploadBaseDir, 0755, true);
        }

        // Create a specific folder for each sub-record file using product and sub-record IDs
        $folderPath = $uploadBaseDir . "{$id}/{$newSubRecordId}/";
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        // Check if a file was uploaded for the new sub-record
        if (isset($_FILES['new_subfile']['name']) && !empty($_FILES['new_subfile']['name'])) {
            $originalName = basename($_FILES['new_subfile']['name']);
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $filename = $newSubRecordId . '.' . $extension; // Name the file after the sub-record ID
            $targetPath = $folderPath . $filename;

            if (move_uploaded_file($_FILES['new_subfile']['tmp_name'], $targetPath)) {
                // Construct the relative path to store in the database
                $filePath = "uploads/sub_records/{$id}/{$newSubRecordId}/" . $filename;

                // Update the newly created sub-record with the actual file path
                $updateStmt = $pdo->prepare("UPDATE product_subs SET file_path = ? WHERE id = ?");
                $updateStmt->execute([$filePath, $newSubRecordId]);
                $success = "✅ New sub-record added and file uploaded successfully!";
            } else {
                error_log("Failed to move uploaded file for new sub-record ID: $newSubRecordId");
                $success = "⚠️ New sub-record added, but failed to upload file.";
            }
        } else {
            $success = "✅ New sub-record added successfully (no file uploaded).";
        }
    } else {
        $success = "⚠️ New sub-record text cannot be empty.";
    }
    // Redirect to prevent form resubmission on refresh
    header("Location: edit_product.php?id=$id" . ($success ? "&status=" . urlencode($success) : ""));
    exit;
}

// Retrieve status message from GET parameter after redirect
if (isset($_GET['status'])) {
    $success = htmlspecialchars($_GET['status']);
}

// Fetch associated sub-records for the product (after any potential modification)
$subsStmt = $pdo->prepare("SELECT * FROM product_subs WHERE product_id = ?");
$subsStmt->execute([$id]);
$subRecords = $subsStmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - QR System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc; /* Simpler, light background */
            min-height: 100vh; /* Ensure full viewport height */
            display: flex;
            flex-direction: column;
        }
        /* Custom scrollbar for a more refined look */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1; /* Lighter scrollbar */
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            backdrop-filter: blur(5px); /* Blurred background */
            display: flex; /* Use flex for centering */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            width: 90%;
            max-width: 550px; /* Wider modal width */
            transform: translateY(-30px);
            opacity: 0;
            animation: fadeInDown 0.2s forwards;
        }

        @keyframes fadeInDown {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Close button */
        .close-button {
            color: #6b7280;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            font-weight: bold;
            transition: color 0.2s ease;
        }

        .close-button:hover,
        .close-button:focus {
            color: #1f2937;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<?php include 'includes/header.php'; ?>

<main class="flex-grow w-full max-w-screen-lg mx-auto p-4 md:p-8 lg:p-12 mt-0 mb-10">
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
        <div class="p-6 md:p-8 lg:p-10">
            <h1 class="text-3xl font-extrabold mb-8 text-gray-800 text-center tracking-tight">
                Edit Product <span class="text-teal-600">Details</span>
            </h1>

            <?php if ($success): ?>
                <div class="bg-gradient-to-r <?php echo strpos($success, '✅') !== false ? 'from-green-100 to-green-200 border-green-300 text-green-800' : (strpos($success, '⚠️') !== false ? 'from-yellow-100 to-yellow-200 border-yellow-300 text-yellow-800' : 'from-red-100 to-red-200 border-red-300 text-red-800'); ?> px-5 py-3 rounded-lg mb-6 shadow-md text-base font-medium animate-fade-in-down" role="alert">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <!-- Product Name Section -->
                <div>
                    <label for="name" class="block text-gray-700 font-semibold mb-2 text-base">Product Name</label>
                    <input
                            type="text"
                            id="name"
                            name="name"
                            required
                            value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-teal-400 transition duration-150 ease-in-out text-sm"
                            placeholder="e.g., Quantum Leap Smartwatch"
                    >
                </div>

                <!-- Update Product Name Button -->
                <button
                        type="submit"
                        name="update_product_name"
                        class="w-full py-2 bg-teal-600 text-white font-semibold rounded-md shadow-md hover:bg-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-400 transition duration-150 ease-in-out text-base"
                >
                    Update Product Name
                </button>
            </form>

            <!-- Sub-Records Section (Display Existing) -->
            <div class="mt-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-700 border-b border-gray-200 pb-3 pr-3">Associated Sub-Records</h2>
                    <button
                            type="button"
                            onclick="showAddSubRecordModal()"
                            class="inline-flex items-center px-4 py-2 bg-teal-600 text-white font-medium rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-500 transition duration-150 ease-in-out text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add New
                    </button>
                </div>

                <?php if (count($subRecords) === 0): ?>
                    <div class="bg-gray-100 border border-gray-200 text-gray-700 px-5 py-3 rounded-lg text-center text-sm shadow-sm">
                        <p>No sub-records found for this product. Click "Add New" to add one!</p>
                    </div>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($subRecords as $sub): ?>
                            <li class="border border-gray-200 p-4 rounded-lg flex flex-col md:flex-row justify-between items-center bg-gray-50 shadow-sm transition duration-150 hover:shadow-md hover:border-teal-300">
                                <span class="text-gray-800 font-medium text-sm mb-2 md:mb-0 md:mr-4 flex-grow"><?= htmlspecialchars($sub['language_text'] ?? 'N/A') ?></span>
                                <div class="flex flex-col md:flex-row space-y-1 md:space-y-0 md:space-x-3 mt-2 md:mt-0">
                                    <a href="edit_subrecord.php?id=<?= htmlspecialchars($sub['id']) ?>"
                                       class="inline-flex items-center px-3 py-1.5 bg-teal-600 text-white font-medium rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-500 cursor-pointer transition duration-150 ease-in-out text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Edit
                                    </a>
                                    <a href="edit_product.php?id=<?= htmlspecialchars($id) ?>&delete_sub=<?= htmlspecialchars($sub['id']) ?>"
                                       onclick="return confirm('Are you sure you want to delete this sub-record? This action cannot be undone and will also delete its associated file.');"
                                       class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500 cursor-pointer transition duration-150 ease-in-out text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Delete
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="mt-8 text-center">
                <a href="index.php" class="inline-flex items-center px-5 py-2 bg-gray-600 text-white font-medium rounded-md shadow-md hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-400 transition duration-150 ease-in-out text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>


<!-- Add New Sub-Record Modal -->
<div id="addSubRecordModal" class="modal hidden">
    <div class="modal-content">
        <span class="close-button" onclick="hideAddSubRecordModal()">&times;</span>
        <h2 class="text-xl font-extrabold mb-5 text-gray-800 text-center">Add New Sub-Record</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="flex flex-col space-y-2">
                <div>
                    <label for="new_language_text_modal" class="block text-gray-700 font-semibold mb-1 text-sm">Languages Title</label>
                    <select
                            name="new_language_text"
                            id="new_language_text_modal"
                            required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-800 focus:outline-none focus:ring-1 focus:ring-teal-400 transition duration-150 text-sm"
                    >
                        <option value="" disabled selected>Select Language</option>
                        <option value="ភាសាខ្មែរ">ភាសាខ្មែរ</option>
                        <option value="English">English</option>
                        <option value="中文">中文</option>
<!--                        <option value="Français">Français</option>-->
<!--                        <option value="Español">Español</option>-->
<!--                        <option value="Deutsch">Deutsch</option>-->
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 flex-shrink-0">
                        <input
                                type="file"
                                name="new_subfile"
                                id="new_subfile_modal"
                                class="hidden"
                        >
                        <span id="new_subfile_label_modal" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white font-medium rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-500 cursor-pointer transition duration-150 ease-in-out text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Upload File (Optional)
                        </span>
                    </label>
                </div>
            </div>
            <button
                    type="submit"
                    name="add_sub_record"
                    class="w-full py-2 bg-teal-600 text-white font-semibold rounded-md shadow-md hover:bg-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-400 transition duration-150 ease-in-out text-base"
            >
                Add New Sub-Record
            </button>
        </form>
    </div>
</div>

<script>
    // Get the modal and the close button
    const addSubRecordModal = document.getElementById('addSubRecordModal');

    // Function to display the modal
    function showAddSubRecordModal() {
        addSubRecordModal.classList.remove('hidden');
        // Reset form fields when opening modal
        document.getElementById('new_language_text_modal').value = ''; // Clear select
        document.getElementById('new_subfile_modal').value = ''; // Clear file input
        document.getElementById('new_subfile_label_modal').innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            Upload File (Optional)
        `;
    }

    // Function to hide the modal
    function hideAddSubRecordModal() {
        addSubRecordModal.classList.add('hidden');
    }

    // Close the modal if the user clicks outside of the modal content
    window.onclick = function(event) {
        if (event.target == addSubRecordModal) {
            hideAddSubRecordModal();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Function to update the file input label for the modal form
        const updateModalFileInputLabel = (fileInputId, labelId) => {
            const fileInput = document.getElementById(fileInputId);
            const labelSpan = document.getElementById(labelId);

            if (fileInput && labelSpan) {
                fileInput.addEventListener('change', (event) => {
                    if (event.target.files.length > 0) {
                        labelSpan.innerHTML = `
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            File: ${event.target.files[0].name}
                        `;
                    } else {
                        labelSpan.innerHTML = `
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Upload File (Optional)
                        `;
                    }
                });
            }
        };

        // Apply to the new sub-record file input within the modal
        updateModalFileInputLabel('new_subfile_modal', 'new_subfile_label_modal');
    });
</script>

</body>
</html>
