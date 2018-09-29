<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    // to test the PR checker
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';

$settings['settings']['renderer'] = [
    'template_path' => __DIR__ . '/../src/app/client/dist/',
];

$app = new \Slim\App($settings);

$c = $app->getContainer();
$c['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        $data = [
            'message' => $exception->getMessage()
        ];
        
        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write(json_encode($data));
    };
};


// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
