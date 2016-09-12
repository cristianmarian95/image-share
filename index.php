<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/src/vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/src/configs/settings.php';
$app = new \Slim\App($settings);

// Set up initialization
require __DIR__ . '/src/bootstrap/initialization.php';

// Register middleware
require __DIR__ . '/src/bootstrap/middleware.php';

// Register routes
require __DIR__ . '/src/bootstrap/routes.php';

// Run app
$app->run();
