<?php
// maintenance.php

// Set the HTTP response code to 503 (Service Unavailable)
http_response_code(503);

// Set a Retry-After header (optional, indicates when the site might be back online)
header('Retry-After: 3600'); // 3600 seconds = 1 hour

// Display the maintenance message
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
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
            color: #333;
            padding: 50px;
        }
        h1 {
            font-size: 2.5em;
            color: #ff6f61;
        }
        p {
            font-size: 1.2em;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>We'll Be Back Soon!</h1>
        <p>Our website is currently undergoing scheduled maintenance. We apologize for the inconvenience and appreciate your patience.</p>
        <p>Please check back later.</p>
    </div>
</body>
</html>
