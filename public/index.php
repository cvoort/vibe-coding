<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize Stripe if the secret key is available
if (isset($_ENV['STRIPE_SECRET_KEY'])) {
    \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
}

// Start session
session_start();

// Basic routing
$request = $_SERVER['REQUEST_URI'];
$basePath = '/vibe-coding'; // Adjust this based on your setup

// Remove base path from request
$request = str_replace($basePath, '', $request);

// Route to appropriate controller
switch ($request) {
    case '':
    case '/':
        require __DIR__ . '/../src/controllers/HomeController.php';
        $controller = new App\Controllers\HomeController();
        $controller->index();
        break;
        
    case '/login':
        require __DIR__ . '/../src/controllers/AuthController.php';
        $controller = new App\Controllers\AuthController();
        $controller->login();
        break;
        
    case '/register':
        require __DIR__ . '/../src/controllers/AuthController.php';
        $controller = new App\Controllers\AuthController();
        $controller->register();
        break;
        
    case '/quiz/create':
        require __DIR__ . '/../src/controllers/QuizController.php';
        $controller = new App\Controllers\QuizController();
        $controller->create();
        break;
        
    case '/quiz/play':
        require __DIR__ . '/../src/controllers/QuizController.php';
        $controller = new App\Controllers\QuizController();
        $controller->play();
        break;
        
    case '/subscription':
        require __DIR__ . '/../src/controllers/SubscriptionController.php';
        $controller = new App\Controllers\SubscriptionController();
        $controller->index();
        break;
        
    case '/my-quizzes':
        require __DIR__ . '/../src/controllers/QuizController.php';
        $controller = new App\Controllers\QuizController();
        $controller->myQuizzes();
        break;
        
    default:
        http_response_code(404);
        require __DIR__ . '/../src/views/404.php';
        break;
} 