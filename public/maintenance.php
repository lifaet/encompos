<?php
http_response_code(503);
header('Retry-After: 3600');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Site Under Maintenance</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #111;
            color: #eee;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            text-align: center;
            padding: 30px;
            border-radius: 12px;
            background: #1a1a1a;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        }

        h1 {
            font-size: 2em;
            margin-bottom: 10px;
            color: #00ffc6;
        }

        p {
            font-size: 1.1em;
            color: #bbb;
        }

        .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin: 0 2px;
            border-radius: 50%;
            background: #00ffc6;
            animation: blink 1.4s infinite both;
        }
        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes blink {
            0%, 80%, 100% { opacity: 0; }
            40% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>We'll Be Back Soon</h1>
        <p>Our site is under maintenance. Please check back later</p>
        <div>
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </div>
</body>
</html>
