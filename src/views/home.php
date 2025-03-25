<?php $title = 'Home'; ?>

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Welcome to What The Quiz</h1>
            <p class="text-xl text-gray-600 mb-8">Create and play interactive quizzes to enhance your learning experience</p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="space-x-4">
                    <a href="/vibe-coding/register" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Get Started
                    </a>
                    <a href="/vibe-coding/login" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Sign In
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-gray-50 px-6 py-12">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="text-center">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Create Quizzes</h3>
                        <p class="text-gray-600">Design engaging quizzes with multiple question types and instant feedback.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="text-center">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Play Quizzes</h3>
                        <p class="text-gray-600">Take quizzes created by others and track your progress over time.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="text-center">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Learn & Grow</h3>
                        <p class="text-gray-600">Enhance your knowledge through interactive learning experiences.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="bg-white px-6 py-12">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Your Recent Activity</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Recent Quizzes -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Quizzes</h3>
                        <?php if (isset($recentQuizzes) && !empty($recentQuizzes)): ?>
                            <ul class="space-y-2">
                                <?php foreach ($recentQuizzes as $quiz): ?>
                                    <li>
                                        <a href="/vibe-coding/quiz/play?id=<?php echo $quiz['id']; ?>" class="text-indigo-600 hover:text-indigo-800">
                                            <?php echo htmlspecialchars($quiz['title']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-gray-600">No recent quizzes found.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Attempts -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Attempts</h3>
                        <?php if (isset($recentAttempts) && !empty($recentAttempts)): ?>
                            <ul class="space-y-2">
                                <?php foreach ($recentAttempts as $attempt): ?>
                                    <li>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($attempt['quiz_title']); ?></span>
                                        <span class="text-gray-600"> - Score: <?php echo $attempt['score']; ?>/<?php echo $attempt['max_score']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-gray-600">No recent attempts found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div> 