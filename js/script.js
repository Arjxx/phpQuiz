let currentQuestionIndex = 0;
let questions = []; 
let answers = [];  // Store user answers

function renderQuestion(index) {
    const question = questions[index];
    let html = `<h2>${question.question_text}</h2>`;
    question.options.forEach((option, idx) => {
        let checked = answers[index] === idx ? 'checked' : '';
        html += `<label><input type="radio" name="answer" value="${idx}" ${checked}>${option}</label><br>`;
    });
    document.getElementById('question-container').innerHTML = html;
}

function renderNavBar() {
    let html = '';
    questions.forEach((_, idx) => {
        let className = answers[idx] !== undefined ? 'nav-item attended' : 'nav-item';
        html += `<span class="${className}" onclick="goToQuestion(${idx})">${idx + 1}</span>`;
    });
    document.getElementById('navigation-bar').innerHTML = html;
}

function navigate(direction) {
    saveAnswer();
    currentQuestionIndex += direction;
    if (currentQuestionIndex < 0) currentQuestionIndex = 0;
    if (currentQuestionIndex >= questions.length) currentQuestionIndex = questions.length - 1;
    renderQuestion(currentQuestionIndex);
    renderNavBar();
}

function saveAnswer() {
    let answer = document.querySelector('input[name="answer"]:checked');
    if (answer) {
        answers[currentQuestionIndex] = parseInt(answer.value);
    }
}

function skip() {
    navigate(1);
}

function goToQuestion(index) {
    saveAnswer();
    currentQuestionIndex = index;
    renderQuestion(currentQuestionIndex);
    renderNavBar();
}

// For testing purposes, populate with dummy data
// In a real scenario, you'd fetch this from your backend.
questions = [
    {question_text: "What is 2+2?", options: ["3", "4", "5"], correct: 1},
    {question_text: "What is 3+3?", options: ["5", "6", "7"], correct: 1}
];

// Initial render
renderQuestion(currentQuestionIndex);
renderNavBar();



//saveAnswer

function saveAnswer() {
    let answer = document.querySelector('input[name="answer"]:checked');
    if (answer) {
        answers[currentQuestionIndex] = parseInt(answer.value);
    }
}

//calculate the score

function calculateScore() {
    let score = 0;
    answers.forEach((answer, index) => {
        if(answer === questions[index].correct) {
            score += 1;
        } else {
            score -= 0.3;
        }
    });
    return score;
}

//Storing User Responses:

async function submitQuiz() {
    let score = calculateScore();
    let response = await fetch('submit_quiz.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({score: score, answers: answers})
    });
    
    let result = await response.json();
    if(result.success) {
        alert('Quiz submitted successfully. Your score is: ' + score);
    } else {
        alert('There was an error submitting your quiz.');
    }
}
