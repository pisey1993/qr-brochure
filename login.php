<?php
session_start();

// Database connection setup
// In a real application, this would ideally be in a separate includes/db.php file.
try {
    $host = 'peoplenpartners.net'; // Your database host
    $db = 'support_request'; // Your database name (as provided)
    $user = 'root'; // Your database username (as provided)
    $pass = 'RootP@ssw0rd'; // Your database password (as provided)
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In a production environment, log the error and show a generic message.
    // For development, you can show the full error for debugging.
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare SQL statement to find user by 'name' column (common in Laravel for username)
    $stmt = $pdo->prepare("SELECT id, name, password FROM users WHERE name = :name");
    $stmt->execute([':name' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        // Laravel typically uses bcrypt for password hashing.
        // Use password_verify() to securely check the entered password against the hashed password from the database.
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['name']; // Use $user['name'] for session as it's the username
            header('Location: index.php');
            exit;
        } else {
            // Password does not match
            $error = "Invalid username or password.";
        }
    } else {
        // User not found in the database
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QR Brochure System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc; /* Simpler, light background consistent with other pages */
            display: flex;
            flex-direction: column; /* Use column to allow for header and footer */
            min-height: 100vh;
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

<!-- Header Section (still hidden, as per previous request) -->
<header class="bg-white shadow-sm py-3 px-4 md:px-6 hidden">
    <!-- Content removed as per request -->
</header>

<main class="flex-grow flex flex-col items-center justify-center p-4">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-100 transition-all duration-300 ease-in-out hover:shadow-xl">
        <!-- Logo inside the form container -->
        <div class="mb-6 text-center">
            <img src="resource/logo.svg" alt="QR System Logo" class="max-w-[220px] h-auto mx-auto">
        </div>

        <h2 class="text-2xl font-extrabold mb-8 text-gray-800 text-center tracking-tight">
            QR Brochure System
        </h2>

        <?php if (!empty($error)): ?>
            <p class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded-md mb-6 text-sm">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label for="username" class="block text-gray-700 font-semibold mb-2 text-sm">Username</label>
                <input
                        type="text"
                        id="username"
                        name="username"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400 transition duration-150 ease-in-out text-sm placeholder-gray-400"
                        placeholder="Enter your username"
                        required
                >
            </div>

            <div>
                <label for="password" class="block text-gray-700 font-semibold mb-2 text-sm">Password</label>
                <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400 transition duration-150 ease-in-out text-sm placeholder-gray-400"
                        placeholder="Enter your password"
                        required
                >
            </div>

            <button
                    type="submit"
                    class="w-full bg-teal-600 text-white py-2.5 rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2 transition duration-150 ease-in-out text-base font-bold shadow-md"
            >
                Login
            </button>
        </form>
    </div>
</main>
</body>
</html>
