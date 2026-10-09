<?php
session_start();
require_once __DIR__ . '/site_check.php';
require_once __DIR__ . '/database.php';

$id = (int)($_GET['id'] ?? 0);

// Quiz
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    http_response_code(404);
    exit('Quiz not found');
}

// Questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_number, id");
$stmt->execute([$id]);
$questions = $stmt->fetchAll();
$stmt = $pdo->prepare("SELECT id, answer_text, is_correct FROM answers WHERE question_id = ? ORDER BY id");
$quizData = [];
foreach ($questions as $q) {
    $stmt->execute([$q['id']]);
    $answers = [];
    foreach ($stmt->fetchAll() as $a) {
        $answers[] = [
            'id'      => (int)$a['id'],
            'text'    => $a['answer_text'],
            'correct' => (bool)$a['is_correct'],
        ];
    }
    $quizData[] = [
        'id'      => (int)$q['id'],
        'text'    => $q['question_text'],
        'answers' => $answers,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['title']) ?> - Quizzity</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">QUIZZITY</a>

    <nav class="nav">
        <a href="index.php">Quizzes</a>
        <a href="leaderboards.php">Leaderboards</a>
    </nav>
</header>

<main class="play">

<?php if (!$quizData): ?>

    <div class="play_result" style="display:block">
        <h1><?= htmlspecialchars($quiz['title']) ?></h1>
        <p>This quiz has no questions yet.</p>
        <a href="index.php" class="btn btn_dark">Back to quizzes</a>
    </div>

<?php else: ?>

    <div id="game">
        <div class="play_top">
            <h1 class="play_title"><?= htmlspecialchars($quiz['title']) ?></h1>
            <span class="play_counter" id="counter"></span>
        </div>
        <div class="play_progress"><div class="play_bar" id="bar"></div></div>

        <div class="play_question" id="questionText"></div>
        <div class="play_answers" id="answers"></div>

        <div class="play_footer">
            <span class="play_feedback" id="feedback"></span>
            <button class="play_next" id="nextBtn" type="button">Next</button>
        </div>
    </div>

    <div class="play_result" id="result">
        <h1><?= htmlspecialchars($quiz['title']) ?></h1>
        <div class="play_score" id="scoreText"></div>
        <p id="scoreMsg"></p>
        <a href="quiz.php?id=<?= $id ?>" class="btn btn_play">Play again</a>
        <a href="index.php" class="btn">Back to quizzes</a>
    </div>

<?php endif; ?>

</main>

<?php if ($quizData): ?>
<script>
var QUESTIONS = <?= json_encode($quizData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

var current = 0;
var score = 0;
var locked = false;

var elQuestion = document.getElementById('questionText');
var elAnswers  = document.getElementById('answers');
var elFeedback = document.getElementById('feedback');
var elNext     = document.getElementById('nextBtn');
var elCounter  = document.getElementById('counter');
var elBar      = document.getElementById('bar');

function showQuestion() {
    var q = QUESTIONS[current];
    locked = false;

    elQuestion.textContent = q.text;
    elFeedback.textContent = '';
    elFeedback.className = 'play_feedback';
    elNext.style.display = 'none';
    elAnswers.innerHTML = '';
    elCounter.textContent = (current + 1) + ' / ' + QUESTIONS.length;
    elBar.style.width = ((current + 1) / QUESTIONS.length * 100) + '%';

    q.answers.forEach(function (a, i) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'play_btn';

        var text = document.createElement('span');
        text.className = 'play_text';
        text.textContent = a.text;

        btn.appendChild(text);
        btn.addEventListener('click', function () { choose(btn, a); });
        elAnswers.appendChild(btn);
    });
}

function choose(btn, answer) {
    if (locked) return;
    locked = true;

    var q = QUESTIONS[current];
    var buttons = elAnswers.querySelectorAll('.play_btn');

    q.answers.forEach(function (a, i) {
        buttons[i].disabled = true;
        if (a.correct) {
            buttons[i].classList.add('right');
        } else if (buttons[i] === btn) {
            buttons[i].classList.add('wrong');
        } else {
            buttons[i].classList.add('dim');
        }
    });

    if (answer.correct) {
        score++;
        elFeedback.textContent = 'Correct!';
        elFeedback.classList.add('ok');
    } else {
        elFeedback.textContent = 'Wrong answer';
        elFeedback.classList.add('bad');
    }

    elNext.textContent = (current === QUESTIONS.length - 1) ? 'See result' : 'Next';
    elNext.style.display = 'inline-block';
    elNext.focus();
}

elNext.addEventListener('click', function () {
    current++;
    if (current < QUESTIONS.length) {
        showQuestion();
    } else {
        finish();
    }
});

function finish() {
    document.getElementById('game').style.display = 'none';
    document.getElementById('scoreText').textContent = score + ' / ' + QUESTIONS.length;

    var pct = score / QUESTIONS.length;
    document.getElementById('scoreMsg').textContent =
        pct === 1 ? 'Perfect!' : pct >= 0.5 ? 'GJ!' : 'Try again!';
    document.getElementById('result').style.display = 'block';
}

showQuestion();
</script>
<?php endif; ?>

</body>
</html>