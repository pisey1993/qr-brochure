<?php

include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['product_name'];

    // --- Start of QR Code Name Generation and Saving Logic ---
    // 1. Insert product first with temporary/NULL qr_code
    // Ensure your 'products' table has a 'qr_code' VARCHAR column
    $stmt = $pdo->prepare("INSERT INTO products (name, qr_code) VALUES (?, ?)");
    $stmt->execute([$name, null]);

    // 2. Get the inserted product ID
    $product_id = $pdo->lastInsertId();

    // 3. Generate the QR code name using product name and ID
    // Sanitize filename for the QR code to ensure it's web-friendly and safe
    $safeName = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $name);
    // Define the relative path where the QR code image will be stored
    // This assumes a 'assets/qr/' directory relative to your web root.
    $qrCodeName = 'assets/qr/product_' . $product_id . '.png'; // Use forward slashes for web paths

    // 4. Update the product with the correct QR code name
    $stmt = $pdo->prepare("UPDATE products SET qr_code = ? WHERE id = ?");
    $stmt->execute([$qrCodeName, $product_id]);
    // --- End of QR Code Name Generation and Saving Logic ---

    // Make sure upload folder exists for sub-record files
    // This path is absolute based on the current script's directory
    $uploadDir = __DIR__ . '/uploads/sub_records/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // Create directory with read/write/execute permissions for owner
    }

    // Process each sub-record submitted through the form
    // Check if language_text and subfile arrays exist and have content
    if (isset($_POST['language_text']) && is_array($_POST['language_text'])) {
        for ($i = 0; $i < count($_POST['language_text']); $i++) {
            $text = $_POST['language_text'][$i];
            $filePath = null; // Initialize file path to null for each sub-record

            // Insert sub-record first to get its ID, then handle file upload
            $stmt = $pdo->prepare("INSERT INTO product_subs (product_id, language_text, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $text, null]); // Insert with null file_path initially
            $subRecordId = $pdo->lastInsertId(); // Get the ID of the newly inserted sub-record

            // Create a specific folder for each sub-record file using product and sub-record IDs
            $folderPath = __DIR__ . "/uploads/sub_records/{$product_id}/{$subRecordId}/";
            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0755, true); // Create folder if it doesn't exist
            }

            // Check if a file was uploaded for the current sub-record
            // Ensure $_FILES['subfile']['name'] exists and is an array before accessing index $i
            if (isset($_FILES['subfile']['name'][$i]) && !empty($_FILES['subfile']['name'][$i])) {
                $originalName = basename($_FILES['subfile']['name'][$i]); // Get original filename
                $extension = pathinfo($originalName, PATHINFO_EXTENSION); // Get file extension
                $filename = $originalName; // Name the file after the sub-record ID
                $targetPath = $folderPath . $filename; // Full path to save the file

                // Move the uploaded file
                if (move_uploaded_file($_FILES['subfile']['tmp_name'][$i], $targetPath)) {
                    // Construct the relative path to store in the database
                    $filePath = "uploads/sub_records/{$product_id}/{$subRecordId}/" . $filename;

                    // Update the sub-record with the actual file path
                    $stmt = $pdo->prepare("UPDATE product_subs SET file_path = ? WHERE id = ?");
                    $stmt->execute([$filePath, $subRecordId]);
                } else {
                    // Log an error if file upload fails
                    error_log("Failed to move uploaded file for sub-record ID: $subRecordId");
                }
            }
        }
    }


    // Redirect to the QR generation page, passing the product_id and the generated qr_name
    header("Location: generate_qr.php?id=$product_id&qr_name=" . urlencode($qrCodeName));
    exit; // Crucial to exit after a header redirect
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Create Product - QR System</title>
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
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<?php
// Includes the header, assuming it provides navigation and branding
// Simplified header for a cleaner look
?>
<?php include 'includes/header.php' ?>

<main class="flex-grow w-full max-w-screen-xl mx-auto p-4 md:p-6 lg:p-8 mt-0 mb-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
        <div class="p-6 md:p-8 lg:p-10">
            <h2 class="text-3xl font-extrabold mb-8 text-gray-800 text-center tracking-tight">
                Create New Product
            </h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-8">
                <!-- Product Name Section -->
                <div>
                    <label for="product_name" class="block text-gray-700 font-semibold mb-2 text-base">Product
                        Name</label>
                    <input
                            type="text"
                            id="product_name"
                            name="product_name"
                            required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-teal-400 transition duration-150 ease-in-out text-sm"
                            placeholder="e.g., Simple Widget"
                    >
                </div>

                <!-- Sub Records Section -->
                <div>
                    <h3 class="text-xl font-semibold mb-6 text-gray-700 border-b border-gray-200 pb-3">Add
                        Languages</h3>
                    <div id="sub-records" class="space-y-6">
                        <div class="flex flex-col md:flex-row md:items-center md:space-x-4 border border-gray-200 rounded-md p-4 bg-gray-50">
                            <select
                                    name="language_text[]"
                                    required
                                    class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-gray-800 focus:outline-none focus:ring-1 focus:ring-teal-400 transition duration-150 text-sm mb-3 md:mb-0"
                            >
                                <option value="" disabled selected>Select Language</option>
                                <option value="ភាសាខ្មែរ">ភាសាខ្មែរ</option>
                                <option value="English">English</option>
                                <option value="中文">中文</option>
                                <!--                                <option value="Français">Français</option>-->
                                <!--                                <option value="Español">Español</option>-->
                                <!--                                <option value="Deutsch">Deutsch</option>-->
                            </select>

                            <label class="block text-sm text-gray-700 flex-shrink-0">
                                <input
                                        type="file"
                                        name="subfile[]"
                                        class="hidden"
                                >
                                <span class="inline-flex items-center px-4 py-2 bg-teal-600 text-white font-medium rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-500 cursor-pointer transition duration-150 ease-in-out text-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round"
                                                                                  stroke-linejoin="round"
                                                                                  stroke-width="2"
                                                                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    Upload Document
                                </span>
                            </label>
                        </div>
                    </div>
                    <button
                            type="button"
                            onclick="addSub()"
                            class="mt-6 inline-flex items-center px-5 py-2 bg-teal-700 text-white font-medium rounded-md shadow-sm hover:bg-teal-800 focus:outline-none focus:ring-1 focus:ring-teal-500 transition duration-150 ease-in-out text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Language Record
                    </button>
                </div>

                <div class="mt-8 text-center flex flex-col items-center space-y-4 max-w-xs mx-auto">
                    <button
                            type="submit"
                            class="inline-flex items-center justify-center w-64 px-5 py-2 bg-teal-600 text-white font-medium rounded-md shadow-md hover:bg-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-400 transition duration-150 ease-in-out text-sm"
                    >
                        Save Product & Generate QR
                    </button>
                    <a href="index.php"
                       class="inline-flex items-center justify-center w-64 px-5 py-2 bg-gray-600 text-white font-medium rounded-md shadow-md hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-400 transition duration-150 ease-in-out text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path>
                        </svg>
                        Back to Dashboard
                    </a>
                </div>


            </form>
        </div>
    </div>
</main>

<?php
// Includes the footer
?>
<footer class="bg-gray-100 text-gray-600 py-4 px-4 md:px-6 mt-8 border-t border-gray-200">
    <div class="max-w-screen-xl mx-auto text-center text-xs">
        &copy; <?php echo date('Y'); ?> QR System. All rights reserved.
    </div>
</footer>

<script>
    /**
     * Adds a new sub-record input field dynamically to the form.
     */
    function addSub() {
        const container = document.getElementById('sub-records');
        const div = document.createElement('div');
        div.className = "flex flex-col md:flex-row md:items-center md:space-x-4 border border-gray-200 rounded-md p-4 bg-gray-50";
        div.innerHTML = `
            <select
                name="language_text[]"
                required
                class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-gray-800 focus:outline-none focus:ring-1 focus:ring-teal-400 transition duration-150 text-sm mb-3 md:mb-0"
            >
                <option value="" disabled selected>Select Language</option>
                <option value="ភាសាខ្មែរ">ភាសាខ្មែរ</option>
                <option value="English">English</option>
                <option value="中文">中文</option>
<!--                <option value="Français">Français</option>-->
<!--                <option value="Español">Español</option>-->
<!--                <option value="Deutsch">Deutsch</option>-->
            </select>

            <label class="block text-sm text-gray-700 flex-shrink-0">
                <input
                    type="file"
                    name="subfile[]"
                    class="hidden"
                >
                <span class="inline-flex items-center px-4 py-2 bg-teal-600 text-white font-medium rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-500 cursor-pointer transition duration-150 ease-in-out text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Upload Document
                </span>
            </label>
            <button type="button" onclick="this.closest('.flex-col').remove()" class="ml-4 p-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-1 focus:ring-red-400 transition duration-150 ease-in-out text-sm flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;
        container.appendChild(div);

        // Attach event listener to the newly created file input for dynamic text update
        const newFileInput = div.querySelector('input[type="file"]');
        const newSpan = div.querySelector('span');
        if (newFileInput && newSpan) {
            newFileInput.addEventListener('change', (event) => {
                if (event.target.files.length > 0) {
                    // Update span text to show selected file name
                    newSpan.innerHTML = `
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        File: ${event.target.files[0].name}
                    `;
                } else {
                    // Revert span text if no file is selected
                    newSpan.innerHTML = `
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Upload Document
                    `;
                }
            });
        }
    }

    // Initial setup for existing file input labels on page load (for the first sub-record row)
    document.addEventListener('DOMContentLoaded', () => {
        // Target the first/initial sub-record row's file input
        const initialSubRecordDiv = document.querySelector('#sub-records > div:first-child');
        if (initialSubRecordDiv) {
            const fileInput = initialSubRecordDiv.querySelector('input[type="file"]');
            const span = initialSubRecordDiv.querySelector('span');
            if (fileInput && span) {
                fileInput.addEventListener('change', (event) => {
                    if (event.target.files.length > 0) {
                        span.innerHTML = `
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            File: ${event.target.files[0].name}
                        `;
                    } else {
                        span.innerHTML = `
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Upload Document
                        `;
                    }
                });
            }
        }
    });
</script>

</body>
</html>
