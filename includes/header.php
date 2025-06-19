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

<header class="bg-teal-600 shadow-sm py-3 px-4 md:px-6">
    <nav class="max-w-screen-xl mx-auto flex justify-between items-center">
        <a href="index.php" class="text-white text-xl font-bold tracking-tight">
            QR System
        </a>
        <div class="flex space-x-3 items-center">
            <a href="index.php" class="text-teal-100 hover:text-white transition duration-200 text-sm">Dashboard</a>
            <a href="logout.php" class="text-red-100 hover:text-white transition duration-200 text-sm">Logout</a>
        </div>
    </nav>
</header>



