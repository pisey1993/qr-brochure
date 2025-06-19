<?php
require 'includes/db.php';

$sub_id = $_GET['id'] ?? null;
if (!$sub_id) {
    die("Error: Missing sub-record ID. Please provide a valid sub-record ID to edit.");
}

// Fetch sub-record details
$stmt = $pdo->prepare("SELECT * FROM product_subs WHERE id = ?");
$stmt->execute([$sub_id]);
$subRecord = $stmt->fetch();
if (!$subRecord) {
    die("Error: Sub-record not found.");
}

$success = null; // Variable to hold success/error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language_text = $_POST['language_text'] ?? '';
    $language_text = trim($language_text);

    if (empty($language_text)) {
        $success = "⚠️ Language text cannot be empty.";
    } else {
        $currentFilePath = $subRecord['file_path']; // Get current file path from DB

        // Define base upload directory for sub-record files
        $uploadBaseDir = __DIR__ . '/uploads/sub_records/';
        // Get product ID for the sub-record to ensure correct folder structure
        $product_id_for_sub = $subRecord['product_id'];
        $subRecordFolder = "{$product_id_for_sub}/{$sub_id}/";
        $uploadDir = $uploadBaseDir . $subRecordFolder;

        // Ensure the directory exists
        // Ensure web server has write permissions to this directory
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create upload directory: " . $uploadDir);
                $success = "❌ Server error: Failed to create upload directory.";
                // Exit or prevent further file operations if directory cannot be created
            }
        }

        $filePathToSave = $currentFilePath; // Assume current path unless a new file is successfully uploaded

        // Handle file upload if a new file is provided
        if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {
            $fileError = $_FILES['file']['error'];

            switch ($fileError) {
                case UPLOAD_ERR_OK: // File uploaded successfully
                    $originalName = basename($_FILES['file']['name']);
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $filename = $originalName; // Name the file after the sub-record ID
                    $targetPath = $uploadDir . $filename;

                    // Ensure the uploaded file is a valid upload before moving
                    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                            // Delete old file if it exists and is different from the new one
                            if (!empty($currentFilePath) && file_exists(__DIR__ . '/' . $currentFilePath) && (__DIR__ . '/' . $currentFilePath) !== $targetPath) {
                                unlink(__DIR__ . '/' . $currentFilePath);
                            }
                            $filePathToSave = 'uploads/sub_records/' . $subRecordFolder . $filename; // Relative path for DB
                        } else {
                            error_log("Failed to move uploaded file for sub-record ID: $sub_id. Target path: $targetPath");
                            $success = "⚠️ Failed to move uploaded file. Check server permissions.";
                        }
                    } else {
                        $success = "⚠️ Uploaded file is not a valid upload. Possible attack or internal error.";
                    }
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $success = "⚠️ Uploaded file is too large. Max size allowed is " . ini_get('upload_max_filesize') . ".";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $success = "⚠️ File upload was interrupted. Please try again.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    // This case should ideally not be hit if !empty($_FILES['file']['name']) check is thorough,
                    // but it's good for completeness if the file input was somehow empty.
                    // If no file was selected, we just keep the old path, so no specific error for this case is needed
                    // unless a file is *required*. Since it's 'Optional', this isn't an error.
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $success = "❌ Server error: Missing a temporary folder for uploads.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $success = "❌ Server error: Failed to write file to disk. Check server permissions.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $success = "❌ Server error: A PHP extension stopped the file upload.";
                    break;
                default:
                    $success = "❌ An unknown file upload error occurred. Error code: " . $fileError;
                    break;
            }
        }

        // Only proceed with DB update if no critical upload error occurred that prevents saving
        // And if the directory creation was successful.
        if (strpos($success, '❌') === false && strpos($success, '⚠️ Failed to move uploaded file') === false) {
            // Update DB with new language text and potentially new file path
            $updateStmt = $pdo->prepare("UPDATE product_subs SET language_text = ?, file_path = ? WHERE id = ?");
            if ($updateStmt->execute([$language_text, $filePathToSave, $sub_id])) {
                // Refetch updated record to display latest data on the page
                $stmt->execute([$sub_id]);
                $subRecord = $stmt->fetch();
                if ($success === null) { // Only set success if no previous upload warning
                    $success = "✅ Sub-record updated successfully!";
                }
            } else {
                error_log("Failed to update sub-record ID: $sub_id in database.");
                $success = "❌ Failed to update sub-record. Please try again.";
            }
        }
    }
    // Redirect to prevent form resubmission on refresh
    header("Location: edit_subrecord.php?id=$sub_id" . ($success ? "&status=" . urlencode($success) : ""));
    exit;
}

// Retrieve status message from GET parameter after redirect
if (isset($_GET['status'])) {
    $success = htmlspecialchars($_GET['status']);
}

?>



<?php
// Includes the header, assuming it provides navigation and branding
include 'includes/header.php';
?>


<main class="flex-grow w-full max-w-screen-lg mx-auto p-4 md:p-8 lg:p-12 mt-0 mb-10">
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
        <div class="p-6 md:p-8 lg:p-10">
            <h1 class="text-3xl font-extrabold mb-8 text-gray-800 text-center tracking-tight">
                Edit Sub-Record <span class="text-gray-800">Details</span> <!-- Changed span color to text-gray-800 -->
            </h1>

            <?php if ($success): ?>
                <div class="bg-gradient-to-r <?php echo strpos($success, '✅') !== false ? 'from-green-100 to-green-200 border-green-300 text-green-800' : (strpos($success, '⚠️') !== false ? 'from-yellow-100 to-yellow-200 border-yellow-300 text-yellow-800' : 'from-red-100 to-red-200 border-red-300 text-red-800'); ?> px-5 py-3 rounded-lg mb-6 shadow-md text-base font-medium animate-fade-in-down" role="alert">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Language Text Section -->
                <div>
                    <label for="language_text" class="block text-gray-700 font-semibold mb-2 text-base">Language Text</label>
                    <input
                            type="text"
                            id="language_text"
                            name="language_text"
                            required
                            value="<?= htmlspecialchars($subRecord['language_text'] ?? '') ?>"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-teal-400 transition duration-150 ease-in-out text-sm"
                            placeholder="e.g., English Product Manual"
                    />
                </div>

                <!-- Attached File Section -->
                <div class="border border-gray-200 rounded-md p-4 bg-gray-50">
                    <label class="block text-gray-700 font-semibold mb-2 text-base">Current Attached File</label>
                    <?php if (!empty($subRecord['file_path']) && file_exists(__DIR__ . '/' . $subRecord['file_path'])): ?>
                        <div class="flex items-center space-x-2 text-sm text-gray-700">
                            <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <a href="<?= htmlspecialchars($subRecord['file_path']) ?>" target="_blank" class="text-teal-600 hover:underline font-medium">
                                <?= htmlspecialchars(basename($subRecord['file_path'])) ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600 text-sm">No file currently attached to this sub-record.</p>
                    <?php endif; ?>
                </div>

                <!-- Replace File Section -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-base">Replace File (Optional)</label>
                    <label class="block text-sm text-gray-700 flex-shrink-0">
                        <input
                                type="file"
                                name="file"
                                id="replace_file"
                                accept=".pdf, .doc, .docx, .txt, .jpg, .jpeg, .png"
                                class="hidden"
                        />
                        <span id="replace_file_label" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white font-medium rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-500 cursor-pointer transition duration-150 ease-in-out text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Choose New File
                        </span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: PDF, Word, Text, Image files (JPG, PNG)</p>
                </div>

                <div class="flex flex-col items-center mt-8 space-y-2">
                    <button
                            type="submit"
                            class="w-48 inline-flex justify-center items-center px-5 py-1.5 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-400 transition duration-150 ease-in-out text-sm"
                    >
                        <svg
                                class="w-4 h-4 mr-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Update
                    </button>

                    <a
                            href="edit_product.php?id=<?= htmlspecialchars($subRecord['product_id']) ?>"
                            class="w-48 inline-flex justify-center items-center px-5 py-1.5 bg-gray-600 text-white font-semibold rounded-lg shadow-md hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-400 transition duration-150 ease-in-out text-sm"
                    >
                        <svg
                                class="w-4 h-4 mr-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path>
                        </svg>
                        Back to Product
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
    document.addEventListener('DOMContentLoaded', () => {
        const replaceFileInput = document.getElementById('replace_file');
        const replaceFileLabel = document.getElementById('replace_file_label');

        if (replaceFileInput && replaceFileLabel) {
            replaceFileInput.addEventListener('change', (event) => {
                if (event.target.files.length > 0) {
                    replaceFileLabel.innerHTML = `
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        File Selected: ${event.target.files[0].name}
                    `;
                } else {
                    replaceFileLabel.innerHTML = `
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Choose New File
                    `;
                }
            });
        }
    });
</script>

</body>
</html>
