<?php
session_start();
require_once __DIR__ . '/site_check.php';
require_once __DIR__ . '/database.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    http_response_code(404);
    exit('Quiz not found');
}
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
if (($quiz['status'] ?? 'public') !== 'public' && !$isAdmin) {
    header('Location: index.php');
    exit;
}
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
        'image'   => $q['image'] ?? '',
        'time'    => (int)$q['time_limit'],
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
            <span class="play_counter" id="timerText"></span>
        </div>
        <div class="play_progress"><div class="play_bar" id="bar"></div></div>
        <div class="play_timer"><div class="play_timer_bar" id="timerBar"></div></div>

        <div class="play_question" id="questionText"></div>
        <img class="play_image" id="questionImage" alt="" hidden>
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
        <a href="quizes.php" class="btn">Back to quizzes</a>
    </div>

<?php endif; ?>

</main>

<?php if ($quizData): ?>
<script>
var QUESTIONS = <?= json_encode($quizData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
var IMAGES_URL = 'images/';

var current = 0;
var score = 0;
var locked = false;
var chosen = null;
var timerId = null;

var elQuestion  = document.getElementById('questionText');
var elImage     = document.getElementById('questionImage');
var elAnswers   = document.getElementById('answers');
var elFeedback  = document.getElementById('feedback');
var elNext      = document.getElementById('nextBtn');
var elCounter   = document.getElementById('counter');
var elBar       = document.getElementById('bar');
var elTimerBar  = document.getElementById('timerBar');
var elTimerText = document.getElementById('timerText');

function showQuestion() {
    var q = QUESTIONS[current];
    locked = false;
    chosen = null;

    elQuestion.textContent = q.text;
    elFeedback.textContent = '';
    elFeedback.className = 'play_feedback';
    elNext.style.display = 'none';
    elAnswers.innerHTML = '';
    elCounter.textContent = (current + 1) + ' / ' + QUESTIONS.length;
    elBar.style.width = ((current + 1) / QUESTIONS.length * 100) + '%';

    if (q.image) {
        elImage.src = IMAGES_URL + encodeURIComponent(q.image);
        elImage.hidden = false;
    } else {
        elImage.hidden = true;
        elImage.removeAttribute('src');
    }

    q.answers.forEach(function (a) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'play_btn';

        var text = document.createElement('span');
        text.className = 'play_text';
        text.textContent = a.text;

        btn.appendChild(text);
        btn.addEventListener('click', function () { pick(btn, a); });
        elAnswers.appendChild(btn);
    });

    startTimer(q.time);
}

function pick(btn, answer) {
    if (locked) return;
    chosen = { btn: btn, answer: answer };
    elAnswers.querySelectorAll('.play_btn').forEach(function (b) {
        b.classList.remove('picked');
    });
    btn.classList.add('picked');

    elNext.textContent = (current === QUESTIONS.length - 1) ? 'See result' : 'Next';
    elNext.style.display = 'inline-block';
}

function startTimer(seconds) {
    clearInterval(timerId);
    var end = Date.now() + seconds * 1000;
    elTimerBar.style.transition = 'none';
    elTimerBar.style.width = '100%';
    void elTimerBar.offsetWidth;
    elTimerBar.style.transition = 'width ' + seconds + 's linear';
    elTimerBar.style.width = '0%';

    function tick() {
        var left = Math.max(0, Math.ceil((end - Date.now()) / 1000));
        elTimerText.textContent = left + ' s';
        if (left <= 0) {
            clearInterval(timerId);
            reveal();
        }
    }
    tick();
    timerId = setInterval(tick, 200);
}

function reveal() {
    if (locked) return;
    locked = true;

    clearInterval(timerId);
    var w = getComputedStyle(elTimerBar).width;
    elTimerBar.style.transition = 'none';
    elTimerBar.style.width = w;

    var q = QUESTIONS[current];
    var buttons = elAnswers.querySelectorAll('.play_btn');

    q.answers.forEach(function (a, i) {
        buttons[i].disabled = true;
        buttons[i].classList.remove('picked');
        if (a.correct) {
            buttons[i].classList.add('right');
        } else if (chosen && buttons[i] === chosen.btn) {
            buttons[i].classList.add('wrong');
        } else {
            buttons[i].classList.add('dim');
        }
    });

    if (chosen && chosen.answer.correct) {
        score++;
        elFeedback.textContent = 'Correct!';
        elFeedback.classList.add('ok');
    } else {
        elFeedback.textContent = chosen ? 'Wrong answer' : "Time's up";
        elFeedback.classList.add('bad');
    }

    elNext.textContent = (current === QUESTIONS.length - 1) ? 'See result' : 'Next';
    elNext.style.display = 'inline-block';
}

elNext.addEventListener('click', function () {
    if (!locked) {
        reveal();
        return;
    }
    clearInterval(timerId);
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