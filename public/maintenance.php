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
      font-family: "Segoe UI", sans-serif;
      background: #f5f6f8;
      color: #333;
    }

    .container {
      text-align: center;
      padding: 20px;
    }

    .icon {
      font-size: 60px;
      margin-bottom: 20px;
    }

    h1 {
      font-size: 2.2em;
      margin: 0 0 10px;
      color: #2c3e50;
    }

    p {
      font-size: 1.1em;
      color: #6c757d;
      margin-bottom: 25px;
    }

    .btn-group {
      display: flex;
      justify-content: center;
      gap: 12px;
    }

    .btn {
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 1em;
      border: 2px solid transparent;
      transition: 0.2s;
    }

    .btn-primary {
      background: #1e293b;
      color: #fff;
      border-color: #1e293b;
    }
    .btn-primary:hover {
      background: #0f172a;
    }

    .btn-outline {
      background: #fff;
      color: #1e293b;
      border-color: #1e293b;
    }
    .btn-outline:hover {
      background: #f1f5f9;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="icon">⚙️</div>
    <h1>Site is under maintenance</h1>
    <p>We’re working hard to improve the user experience. Stay tuned!</p>
    <div class="btn-group">
      <button class="btn btn-primary" onclick="location.href='mailto:support@example.com'">Contact Us</button>
      <button class="btn btn-outline" onclick="location.reload()">Reload</button>
    </div>
  </div>
</body>
</html>
