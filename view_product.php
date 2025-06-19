<?php
include 'includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo '<p class="text-danger">Invalid Product ID.</p>';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo '<p class="text-danger">Product not found.</p>';
    exit;
}

$subs = $pdo->prepare("SELECT * FROM product_subs WHERE product_id = ?");
$subs->execute([$id]);
$sub_records = $subs->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($product['name']) ?> - Brochure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer&display=swap" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alegreya:ital,wght@0,400..900;1,400..900&family=Battambang:wght@100;300;400;700;900&family=Bayon&family=Hanuman:wght@100;300;400;700;900&family=Noto+Serif+Khmer:wght@100..900&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Gilmer';
            src: url('resource/font/gilmer_regular-webfont.woff2') format('woff2'),
            url('resource/font/gilmer_regular-webfont.woff') format('woff');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Gilmer';
            src: url('resource/font/gilmer_bold-webfont.woff2') format('woff2'),
            url('resource/font/gilmer_bold-webfont.woff') format('woff');
            font-weight: bold;
            font-style: normal;
            font-display: swap;
        }

        body {
            font-family: 'Noto Sans Khmer', sans-serif;
            background-color: #ffffff;
            color: #003b4a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .top-bar {
            height: 40px;
            background-color: #3ba6a1;
        }

        .logo-container img {
            max-width: 250px;
        }

        .title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0b2d53;
        }

        .lang-button {
            background-color: #3ba6a1;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: left;
            padding: 10px 15px;
            font-size: 1.1rem;
            padding-left: 45%;
        }
        /* Small screens (mobile) adjustments */
        /* Small screens (mobile) */
        /* Extra Small screens (very small phones) */
        @media (max-width: 480px) {
            .lang-button {
                background-color: #3ba6a1;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: left;
                padding: 8px 10px;
                font-size: 0.9rem;
                padding-left: 35%;
            }
        }

        /* Small screens (phones) */
        @media (min-width: 481px) and (max-width: 640px) {
            .lang-button {
                background-color: #3ba6a1;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: left;
                padding: 10px 15px;
                font-size: 1rem;
                padding-left: 36%;
            }
        }

        /* Medium screens (tablets) */
        @media (min-width: 641px) and (max-width: 1024px) {
            .lang-button {
                background-color: #3ba6a1;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: left;
                padding: 10px 20px;
                font-size: 1.1rem;
                padding-left: 35%;
            }
        }

        /* Large screens (small desktops) */
        @media (min-width: 1025px) and (max-width: 1280px) {
            .lang-button {
                background-color: #3ba6a1;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: left;
                padding: 10px 25px;
                font-size: 1.2rem;
                padding-left: 39%;
            }
        }

        /* Extra Large screens (large desktops, widescreens) */
        @media (min-width: 1281px) {
            .lang-button {
                background-color: #3ba6a1;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: left;
                padding: 12px 30px;
                font-size: 1.3rem;
                padding-left: 40%;
            }
        }


        .lang-button:hover {
            background-color: #2e8b86;
            color: #fff;
        }

        .lang-button img {
            width: 28px;
            margin-right: 15px;
        }

        .footer-icons {
            max-width: 120px;
            height: auto;
        }

        footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-top: auto;
        }

        footer div div {
            white-space: nowrap;
        }

        .vertical-line {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 2px;
            height: 80%;
            background-color: #3ba6a1;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<div class="top-bar w-100"></div>

<div class="container text-center my-4 flex-grow-1">
    <div class="logo-container mb-3">
        <img src="resource/logo.svg" alt="People & Partners Logo" />
    </div>
    <div style="margin-top: 50px"></div>
    <div class="title my-4" style="color: #094568;font-weight: bold">ខិត្តប័ណ្ណ / Brochure / 宣传册</div>
    <!-- Modal -->
    <div class="modal fade" id="missingFileModal" tabindex="-1" aria-labelledby="missingFileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #3ba6a1; color: white;">
                    <h5 class="modal-title" id="missingFileModalLabel">File Not Available</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    The file for this language version is currently not available.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>


    <div class="d-grid gap-3 col-10 col-sm-8 col-md-6 mx-auto">
        <?php
        foreach ($sub_records as $sub) {
            $language_text = trim($sub['language_text']);

            // Default flag and display language
            $flag = 'gb';
            $display_lang = 'english';

            if ($language_text === 'ភាសាខ្មែរ') {
                $flag = 'kh';
                $display_lang = 'ភាសាខ្មែរ';
            } elseif ($language_text === 'English') {
                $flag = 'gb';
                $display_lang = 'English';
            } elseif ($language_text === '中文') {
                $flag = 'cn';
                $display_lang = '中文';
            } else {
                // Fallbacks for any other language texts
                $flag = 'gb'; // default to English flag
                $display_lang = htmlspecialchars($language_text);
            }

            if (!empty($sub['file_path'])) {
                // If file path exists
                echo '<a href="' . htmlspecialchars($sub['file_path']) . '" class="btn lang-button" target="_blank">';
                echo '<img src="https://flagcdn.com/w40/' . $flag . '.png" alt="' . htmlspecialchars($display_lang) . '" />';
                echo htmlspecialchars($display_lang);
                echo '</a>';
            } else {
                // If file path is missing
                echo '<button class="btn lang-button" onclick="showMissingFileAlert()">';
                echo '<img src="https://flagcdn.com/w40/' . $flag . '.png" alt="' . htmlspecialchars($display_lang) . '" />';
                echo htmlspecialchars($display_lang);
                echo '</button>';
            }
        }
        ?>
    </div>

</div>

<footer style="font-family: 'Gilmer', sans-serif; color: #2e8b86; background-color: white">
    <div class="container py-3">
        <div class="row align-items-center">
            <div class="col-6 d-flex justify-content-end">
                <img src="resource/10year.svg" alt="10th Anniversary" class="footer-icons"/>
            </div>
            <div class="col-6 position-relative ps-4 ps-md-5">
                <div class="vertical-line"></div>
                <div style="padding-top: 10px;padding-bottom: 10px;margin-left: 5px">
                    <div style="font-size:x-small;">Contact us</div>
                    <div style="font-size:small; font-weight: bold">023 21 78 78</div>
                    <div style="font-size:small; font-weight: bold">015 78 00 78</div>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showMissingFileAlert() {
        var myModal = new bootstrap.Modal(document.getElementById('missingFileModal'));
        myModal.show();
    }
</script>


</body>
</html>
