<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Brochure Language Selector</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer&display=swap" rel="stylesheet" />
    <style>

        @font-face {
            font-family: 'Gilmer';
            src: url('resource/font/gilmer_regular-webfont.woff2') format('woff2'),
            url('resource/font/gilmer_regular-webfont.woff') format('woff');
            font-weight: normal; /* Assign this to your regular font file */
            font-style: normal;
            font-display: swap; /* Helps prevent invisible text during loading */
        }

        @font-face {
            font-family: 'Gilmer';
            src: url('resource/font/gilmer_bold-webfont.woff2') format('woff2'),
            url('resource/font/gilmer_bold-webfont.woff') format('woff');
            font-weight: bold; /* Assign this to your bold font file */
            font-style: normal;
            font-display: swap;
        }
        body {
            font-family: 'Noto Sans Khmer', sans-serif;
            background-color: #ffffff;
            color: #003b4a;
            display: flex; /* Flexbox for sticky footer */
            flex-direction: column; /* Arrange content vertically */
            min-height: 100vh; /* Ensure body takes full viewport height */
        }


        .top-bar {
            height: 40px;
            background-color: #3ba6a1;
        }

        .logo-container img {
            max-width: 160px;
            height: auto; /* Ensure aspect ratio is maintained */
        }

        .company-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #3ba6a1;
        }

        .slogan {
            font-style: italic;
            font-size: 0.9rem;
            color: #3ba6a1;
        }

        .title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0b2d53;
        }

        .lang-button {
            background-color: #3ba6a1;
            color: #fff;
            display: flex; /* Use flexbox for button content alignment */
            align-items: center; /* Vertically align items */
            justify-content: center; /* Horizontally center items */
            padding: 10px 15px; /* Adjust padding for better button size */
            font-size: 1.1rem; /* Slightly larger font size */
        }

        .lang-button:hover {
            background-color: #2e8b86; /* Darken on hover */
            color: #fff;
        }

        .lang-button img {
            width: 28px; /* Slightly larger flags */
            margin-right: 15px; /* More space between flag and text */
        }

        /* Responsive Footer Icon Styling */
        .footer-icons {
            max-width: 120px; /* Control max size of the 10-year icon */
            height: auto;
        }

        footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-top: auto; /* Push footer to the bottom */
        }

        /* Specific styles for the footer contact numbers */
        footer div div { /* Targets the direct divs inside the contact column */
            white-space: nowrap; /* Prevent phone numbers from wrapping */
        }

        /* Add this CSS to your existing style block or a separate CSS file */
        .vertical-line {
            position: absolute;
            left: 0; /* Position at the start of the column */
            top: 50%; /* Center vertically */
            transform: translateY(-50%); /* Adjust for perfect vertical centering */
            width: 2px; /* Thickness of the line */
            height: 80%; /* Height of the line relative to its parent */
            background-color: #3ba6a1; /* Teal color */
            /* Optional: Add some padding to the right of the line for spacing */
            margin-left: 10px; /* Adjust as needed */
        }



    </style>
</head>
<body>

<div class="top-bar w-100"  ></div>


<div class="container text-center my-4 flex-grow-1">
    <div class="logo-container mb-3">
        <img src="resource/logo.svg" alt="People & Partners Logo" />
    </div>
    <div style="margin-top: 50px"></div>
    <div class="title my-4">ខិត្តប័ណ្ណ / Brochure / 宣传册</div>

    <div class="d-grid gap-3 col-10 col-sm-8 col-md-6 mx-auto"> <button class="btn lang-button">
            <img src="https://flagcdn.com/w40/kh.png" alt="Khmer" /> ភាសាខ្មែរ
        </button>
        <button class="btn lang-button">
            <img src="https://flagcdn.com/w40/gb.png" alt="English" /> English
        </button>
        <button class="btn lang-button">
            <img src="https://flagcdn.com/w40/cn.png" alt="Chinese" /> 中文
        </button>
    </div>
</div>

<footer style="font-family: 'Gilmer', sans-serif; color: #2e8b86;background-color: white">
    <div class="container py-3">
        <div class="row align-items-center"> <div class="col-6 d-flex justify-content-end">
                <img src="resource/10year.svg" alt="10th Anniversary" class="footer-icons"/>
            </div>
            <div class="col-6 position-relative ps-4 ps-md-5" >
                <div class="vertical-line"></div>
                <div style="padding-top: 10px;padding-bottom: 10px;margin-left: 5px">
                    <div style="font-size:x-small;">Contact us</div>
                    <div style="font-size:small; font-weight: bold ">023 21 78 78</div>
                    <div style="font-size:small;  font-weight: bold">015 78 00 78</div>
                </div>

            </div>
        </div>
    </div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>