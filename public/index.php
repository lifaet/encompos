<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Custom maintenance mode check
if (!empty($_ENV['MAINTENANCE']) && $_ENV['MAINTENANCE'] === 'true') {
    http_response_code(503);
    header('Retry-After: 3600');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Site Under Maintenance</title>
        <style>
            body {
              margin: 0; height: 100vh; display: flex;
              align-items: center; justify-content: center;
              font-family: "Segoe UI", sans-serif;
              background: #f5f6f8; color: #333;
            }
            .container { text-align: center; padding: 20px; }
            .icon { font-size: 60px; margin-bottom: 20px; }
            h1 { font-size: 2.2em; margin: 0 0 10px; color: #2c3e50; }
            p { font-size: 1.1em; color: #6c757d; margin-bottom: 25px; }
            .btn-group { display: flex; justify-content: center; gap: 12px; }
            .btn {
              padding: 10px 20px; border-radius: 6px;
              font-weight: 600; cursor: pointer; font-size: 1em;
              border: 2px solid transparent; transition: 0.2s;
            }
            .btn-primary { background: #1e293b; color: #fff; border-color: #1e293b; }
            .btn-primary:hover { background: #0f172a; }
            .btn-outline { background: #fff; color: #1e293b; border-color: #1e293b; }
            .btn-outline:hover { background: #f1f5f9; }
        </style>
    </head>
    <body>
      <div class="container">
        <div class="icon">⚙️</div>
        <h1>Site is under maintenance</h1>
        <p>We’re working hard to improve the user experience. Stay tuned!</p>
        <div class="btn-group">
          <button class="btn btn-primary" onclick="location.href='mailto:support@encomgrid.com'">Contact Us</button>
          <button class="btn btn-outline" onclick="location.reload()">Reload</button>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
