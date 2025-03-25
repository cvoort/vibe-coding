<?php
namespace App\Controllers;

class SubscriptionController {
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
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Please log in to manage your subscription.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /vibe-coding/login');
            exit;
        }

        // Get current subscription status
        $stmt = $this->db->prepare("
            SELECT * FROM subscriptions 
            WHERE user_id = ? 
            AND status = 'active' 
            AND current_period_end > NOW()
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $subscription = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Load the subscription view
        ob_start();
        extract(['subscription' => $subscription]);
        require __DIR__ . '/../views/subscription/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layouts/main.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Please log in to subscribe.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /vibe-coding/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Create Stripe customer
                $user = $this->getUser($_SESSION['user_id']);
                $customer = \Stripe\Customer::create([
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'source' => $_POST['stripeToken']
                ]);

                // Create subscription
                $subscription = \Stripe\Subscription::create([
                    'customer' => $customer->id,
                    'items' => [['price' => $_ENV['STRIPE_PRICE_ID']]],
                    'expand' => ['latest_invoice.payment_intent'],
                    'metadata' => ['user_id' => $_SESSION['user_id']]
                ]);

                // Save subscription details to database
                $stmt = $this->db->prepare("
                    INSERT INTO subscriptions (
                        user_id, 
                        stripe_subscription_id, 
                        stripe_customer_id, 
                        status, 
                        current_period_start, 
                        current_period_end
                    ) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?))
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $subscription->id,
                    $customer->id,
                    $subscription->status,
                    $subscription->current_period_start,
                    $subscription->current_period_end
                ]);

                $_SESSION['flash_message'] = 'Subscription created successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: /vibe-coding/subscription');
                exit;
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Failed to create subscription. Please try again.';
                $_SESSION['flash_type'] = 'error';
            }
        }

        // Load the create subscription view
        ob_start();
        require __DIR__ . '/../views/subscription/create.php';
        $content = ob_get_clean();

        require __DIR__ . '/../views/layouts/main.php';
    }

    public function cancel() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = 'Please log in to cancel your subscription.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /vibe-coding/login');
            exit;
        }

        // Get current subscription
        $stmt = $this->db->prepare("
            SELECT * FROM subscriptions 
            WHERE user_id = ? 
            AND status = 'active' 
            AND current_period_end > NOW()
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $subscription = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$subscription) {
            $_SESSION['flash_message'] = 'No active subscription found.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /vibe-coding/subscription');
            exit;
        }

        try {
            // Cancel subscription in Stripe
            $stripeSubscription = \Stripe\Subscription::retrieve($subscription['stripe_subscription_id']);
            $stripeSubscription->cancel();

            // Update subscription status in database
            $stmt = $this->db->prepare("
                UPDATE subscriptions 
                SET status = 'canceled' 
                WHERE id = ?
            ");
            $stmt->execute([$subscription['id']]);

            $_SESSION['flash_message'] = 'Subscription canceled successfully.';
            $_SESSION['flash_type'] = 'success';
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = 'Failed to cancel subscription. Please try again.';
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: /vibe-coding/subscription');
        exit;
    }

    private function getUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
} 