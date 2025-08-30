<?php
// maintenance.php

http_response_code(503);
header('Retry-After: 3600');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>🚧 Site Under Maintenance</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        .container {
            text-align: center;
            padding: 40px;
            border-radius: 16px;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.6);
            animation: fadeIn 1.5s ease-in-out;
        }

        h1 {
            font-size: 2.8em;
            margin-bottom: 15px;
            color: #00ffe0;
            text-shadow: 0 0 15px #00ffe0, 0 0 30px #00aaff;
            animation: glowPulse 2s infinite alternate;
        }

        p {
            font-size: 1.2em;
            color: #ddd;
            margin: 10px 0;
        }

        .loader {
            margin: 30px auto 0;
            width: 60px;
            height: 60px;
            border: 6px solid rgba(255,255,255,0.2);
            border-top: 6px solid #00ffe0;
            border-radius: 50%;
            animation: spin 1.5s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes glowPulse {
            from { text-shadow: 0 0 10px #00ffe0, 0 0 20px #00aaff; }
            to   { text-shadow: 0 0 20px #00ffe0, 0 0 40px #00aaff; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚡ We'll Be Back Soon ⚡</h1>
        <p>Our system is currently undergoing scheduled maintenance.</p>
        <p>We’ll be back online shortly. Thanks for your patience 🚀</p>
        <div class="loader"></div>
    </div>
</body>
</html>
