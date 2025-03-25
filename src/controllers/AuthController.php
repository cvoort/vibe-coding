<?php
namespace App\Controllers;

class AuthController {
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

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $stmt = $this->db->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['flash_message'] = 'Successfully logged in!';
                $_SESSION['flash_type'] = 'success';
                header('Location: /vibe-coding');
                exit;
            } else {
                $_SESSION['flash_message'] = 'Invalid email or password.';
                $_SESSION['flash_type'] = 'error';
            }
        }

        // Load the login view
        ob_start();
        require __DIR__ . '/../views/auth/login.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layouts/main.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($password !== $confirmPassword) {
                $_SESSION['flash_message'] = 'Passwords do not match.';
                $_SESSION['flash_type'] = 'error';
            } else {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $email, $hashedPassword]);
                    
                    $_SESSION['flash_message'] = 'Registration successful! Please log in.';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: /vibe-coding/login');
                    exit;
                } catch (\PDOException $e) {
                    if ($e->getCode() == 23000) { // Duplicate entry
                        $_SESSION['flash_message'] = 'Email already exists.';
                        $_SESSION['flash_type'] = 'error';
                    } else {
                        $_SESSION['flash_message'] = 'Registration failed. Please try again.';
                        $_SESSION['flash_type'] = 'error';
                    }
                }
            }
        }

        // Load the register view
        ob_start();
        require __DIR__ . '/../views/auth/register.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layouts/main.php';
    }

    public function logout() {
        session_destroy();
        header('Location: /vibe-coding');
        exit;
    }
} 