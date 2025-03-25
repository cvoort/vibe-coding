<?php
namespace App\Controllers;

class QuizController {
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

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Please log in to create quizzes.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /vibe-coding/login');
            exit;
        }

        // Check if user has an active subscription
        $stmt = $this->db->prepare("
            SELECT * FROM subscriptions 
            WHERE user_id = ? 
            AND status = 'active' 
            AND current_period_end > NOW()
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $subscription = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$subscription) {
            $_SESSION['flash_message'] = 'You need an active subscription to create quizzes.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /vibe-coding/subscription');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle quiz creation
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $isPublic = isset($_POST['is_public']) ? 1 : 0;

            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("
                    INSERT INTO quizzes (user_id, title, description, is_public) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$_SESSION['user_id'], $title, $description, $isPublic]);
                $quizId = $this->db->lastInsertId();

                // Handle questions
                foreach ($_POST['questions'] as $index => $question) {
                    $stmt = $this->db->prepare("
                        INSERT INTO questions (quiz_id, question_text, question_type, points) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $quizId,
                        $question['text'],
                        $question['type'],
                        $question['points'] ?? 1
                    ]);
                    $questionId = $this->db->lastInsertId();

                    // Handle answers
                    foreach ($question['answers'] as $answer) {
                        $stmt = $this->db->prepare("
                            INSERT INTO answers (question_id, answer_text, is_correct) 
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([
                            $questionId,
                            $answer['text'],
                            $answer['is_correct'] ?? 0
                        ]);
                    }
                }

                $this->db->commit();
                $_SESSION['flash_message'] = 'Quiz created successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: /vibe-coding/my-quizzes');
                exit;
            } catch (\Exception $e) {
                $this->db->rollBack();
                $_SESSION['flash_message'] = 'Failed to create quiz. Please try again.';
                $_SESSION['flash_type'] = 'error';
            }
        }

        // Load the create quiz view
        ob_start();
        require __DIR__ . '/../views/quiz/create.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layouts/main.php';
    }

    public function play() {
        $quizId = $_GET['id'] ?? null;
        if (!$quizId) {
            $_SESSION['flash_message'] = 'Quiz not found.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /vibe-coding');
            exit;
        }

        // Get quiz details
        $stmt = $this->db->prepare("
            SELECT q.*, u.name as creator_name 
            FROM quizzes q 
            JOIN users u ON q.user_id = u.id 
            WHERE q.id = ?
        ");
        $stmt->execute([$quizId]);
        $quiz = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$quiz) {
            $_SESSION['flash_message'] = 'Quiz not found.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /vibe-coding');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle quiz submission
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['flash_message'] = 'Please log in to submit your answers.';
                $_SESSION['flash_type'] = 'error';
                header('Location: /vibe-coding/login');
                exit;
            }

            try {
                $this->db->beginTransaction();

                // Create quiz attempt
                $stmt = $this->db->prepare("
                    INSERT INTO quiz_attempts (quiz_id, user_id, max_score) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$quizId, $_SESSION['user_id'], $_POST['max_score']]);
                $attemptId = $this->db->lastInsertId();

                // Record answers
                foreach ($_POST['answers'] as $questionId => $answer) {
                    $stmt = $this->db->prepare("
                        INSERT INTO user_answers (quiz_attempt_id, question_id, answer_id, answer_text, is_correct) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $attemptId,
                        $questionId,
                        $answer['answer_id'] ?? null,
                        $answer['text'] ?? null,
                        $answer['is_correct'] ?? 0
                    ]);
                }

                $this->db->commit();
                $_SESSION['flash_message'] = 'Quiz submitted successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: /vibe-coding/quiz/results?id=' . $attemptId);
                exit;
            } catch (\Exception $e) {
                $this->db->rollBack();
                $_SESSION['flash_message'] = 'Failed to submit quiz. Please try again.';
                $_SESSION['flash_type'] = 'error';
            }
        }

        // Get questions and answers
        $stmt = $this->db->prepare("
            SELECT q.*, a.id as answer_id, a.answer_text, a.is_correct 
            FROM questions q 
            LEFT JOIN answers a ON q.id = a.question_id 
            WHERE q.quiz_id = ?
        ");
        $stmt->execute([$quizId]);
        $questions = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!isset($questions[$row['id']])) {
                $questions[$row['id']] = [
                    'id' => $row['id'],
                    'text' => $row['question_text'],
                    'type' => $row['question_type'],
                    'points' => $row['points'],
                    'answers' => []
                ];
            }
            if ($row['answer_id']) {
                $questions[$row['id']]['answers'][] = [
                    'id' => $row['answer_id'],
                    'text' => $row['answer_text'],
                    'is_correct' => $row['is_correct']
                ];
            }
        }

        // Load the play quiz view
        ob_start();
        extract(['quiz' => $quiz, 'questions' => array_values($questions)]);
        require __DIR__ . '/../views/quiz/play.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layouts/main.php';
    }

    public function myQuizzes() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Please log in to view your quizzes.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /vibe-coding/login');
            exit;
        }

        // Get user's quizzes
        $stmt = $this->db->prepare("
            SELECT q.*, 
                   COUNT(DISTINCT qa.id) as attempt_count,
                   AVG(qa.score) as average_score
            FROM quizzes q
            LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id
            WHERE q.user_id = ?
            GROUP BY q.id
            ORDER BY q.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $quizzes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Load the my quizzes view
        ob_start();
        extract(['quizzes' => $quizzes]);
        require __DIR__ . '/../views/quiz/my-quizzes.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layouts/main.php';
    }
} 