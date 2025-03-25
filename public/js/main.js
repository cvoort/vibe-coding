// Initialize Stripe
const stripe = Stripe(document.querySelector('meta[name="stripe-publishable-key"]').content);

// Handle quiz answer selection
document.addEventListener('DOMContentLoaded', function() {
    const answerOptions = document.querySelectorAll('.answer-option');
    answerOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            answerOptions.forEach(opt => opt.classList.remove('selected'));
            // Add selected class to clicked option
            this.classList.add('selected');
        });
    });

    // Handle quiz progress bar
    const progressBar = document.querySelector('.progress-bar-fill');
    if (progressBar) {
        const totalQuestions = parseInt(progressBar.dataset.total);
        const currentQuestion = parseInt(progressBar.dataset.current);
        const progress = (currentQuestion / totalQuestions) * 100;
        progressBar.style.width = `${progress}%`;
    }

    // Handle flash message auto-hide
    const flashMessage = document.querySelector('.flash-message');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            setTimeout(() => {
                flashMessage.remove();
            }, 300);
        }, 5000);
    }

    // Handle subscription form
    const subscriptionForm = document.getElementById('subscription-form');
    if (subscriptionForm) {
        subscriptionForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const { error } = await stripe.createPaymentMethod({
                type: 'card',
                card: elements.getElement('card'),
            });

            if (error) {
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
            } else {
                this.submit();
            }
        });
    }

    // Handle quiz timer
    const quizTimer = document.getElementById('quiz-timer');
    if (quizTimer) {
        let timeLeft = parseInt(quizTimer.dataset.time);
        const timerInterval = setInterval(() => {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            quizTimer.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                document.getElementById('quiz-form').submit();
            }
        }, 1000);
    }
});

// Handle quiz navigation
function navigateToQuestion(questionNumber) {
    const questions = document.querySelectorAll('.question-container');
    questions.forEach((question, index) => {
        if (index === questionNumber - 1) {
            question.style.display = 'block';
        } else {
            question.style.display = 'none';
        }
    });
}

// Handle quiz submission
function submitQuiz() {
    const form = document.getElementById('quiz-form');
    const answers = [];
    document.querySelectorAll('.answer-option.selected').forEach(option => {
        answers.push(option.dataset.answerId);
    });
    
    if (answers.length === 0) {
        alert('Please select an answer before submitting.');
        return;
    }
    
    form.submit();
} 