<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>What The Quiz - <?php echo $title ?? 'Learn Through Quizzes'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/vibe-coding/public/css/style.css">
    
    <!-- Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/vibe-coding" class="text-2xl font-bold text-indigo-600">What The Quiz</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/vibe-coding" class="inline-flex items-center px-1 pt-1 text-gray-900">Home</a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="/vibe-coding/quiz/create" class="inline-flex items-center px-1 pt-1 text-gray-500 hover:text-gray-900">Create Quiz</a>
                            <a href="/vibe-coding/my-quizzes" class="inline-flex items-center px-1 pt-1 text-gray-500 hover:text-gray-900">My Quizzes</a>
                            <a href="/vibe-coding/subscription" class="inline-flex items-center px-1 pt-1 text-gray-500 hover:text-gray-900">Subscription</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/vibe-coding/logout" class="text-gray-500 hover:text-gray-900">Logout</a>
                    <?php else: ?>
                        <a href="/vibe-coding/login" class="text-gray-500 hover:text-gray-900 mr-4">Login</a>
                        <a href="/vibe-coding/register" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="mb-4 p-4 rounded-md <?php echo $_SESSION['flash_type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php 
                echo $_SESSION['flash_message'];
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']);
                ?>
            </div>
        <?php endif; ?>

        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow-lg mt-8">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> What The Quiz. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Custom JavaScript -->
    <script src="/vibe-coding/public/js/main.js"></script>
</body>
</html> 