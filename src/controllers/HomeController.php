<?php
namespace App\Controllers;

class HomeController {
    private $db;

    public function __construct() {
        // Initialize database connection
        $this->db = new \PDO(
            "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASS']
        );
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function index() {
        $data = [];

        if (isset($_SESSION['user_id'])) {
            // Get recent quizzes
            $stmt = $this->db->prepare("
                SELECT id, title 
                FROM quizzes 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $data['recentQuizzes'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get recent attempts
            $stmt = $this->db->prepare("
                SELECT qa.*, q.title as quiz_title 
                FROM quiz_attempts qa 
                JOIN quizzes q ON qa.quiz_id = q.id 
                WHERE qa.user_id = ? 
                ORDER BY qa.started_at DESC 
                LIMIT 5
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $data['recentAttempts'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Load the view
        ob_start();
        extract($data);
        require __DIR__ . '/../views/home.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layouts/main.php';
    }
} 