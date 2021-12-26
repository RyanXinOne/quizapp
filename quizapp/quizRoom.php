<?php
require_once './utility.php';
session_start();
// if not logged in, redirect to login page
if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

$pdo = getPDOConnection();

// fetch quiz info
if (isset($_GET['id'])) {
    $quizId = $_GET['id'];
} else if (isset($_POST['quizId'])) {
    $quizId = $_POST['quizId'];
    processSubmit();
} else {
    redirect('quiz.php');
}

if ($_SESSION['isStaff']) {
    $stmt = execSQL(
        $pdo,
        'SELECT name, firstname, lastname, duration FROM quiz
        INNER JOIN staff ON author_id = staff.id
        INNER JOIN user ON staff.username = user.username
        WHERE quiz.id = :quizId',
        ['quizId' => $quizId]
    );
} else {
    $stmt = execSQL(
        $GLOBALS['pdo'],
        'SELECT name, firstname, lastname, duration, date, score FROM quiz
        INNER JOIN staff ON author_id = staff.id
        INNER JOIN user ON staff.username = user.username
        LEFT OUTER JOIN attempt ON quiz_id = quiz.id AND stu_id = :stuId
        WHERE quiz.id = :quizId AND is_available = TRUE',
        ['stuId' => $_SESSION['id'], 'quizId' => $quizId]
    );
}
$row = $stmt->fetch();
// check existence and availability of quiz
if ($row === false) {
    redirect('quiz.php');
}
$quizName = $row['name'];
$author = $row['firstname'] . ' ' . $row['lastname'];
$duration = $row['duration'];
// decide rendering mode
if ($_SESSION['isStaff']) {
    $mode = 'r';
} else {
    if (isset($row['date'])) {
        $mode = 'r';
        $date = $row['date'];
        $scoreSum = $row['score'];
        // get maximum score
        $stmt = execSQL(
            $pdo,
            'SELECT SUM(score) FROM question WHERE quiz_id = :quizId',
            ['quizId' => $quizId]
        );
        $maxScore = $stmt->fetchColumn();
    } else {
        $mode = 'w';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Quiz App - Quiz Room</title>
    <meta name="author" content="Yanze Xin">
    <meta name="description" content="An elegant quiz application">
    <link rel="stylesheet" type="text/css" href="<?php echo $pwd . 'css/style.css' ?>">
</head>

<body>
    <?php renderTopBar() ?>
    <h1>Quiz Room</h1>
    <?php
    if (!$_SESSION['isStaff'] && $mode === 'r') {
        echo '<p>You have successfully completed the quiz on ' . $date . '.</p>';
        echo '<p>Your Score: ' . $scoreSum . '/' . $maxScore . '</p>';
    }
    ?>
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
        <div id="quizBar">
            <label>Quiz <?php echo $quizId ?>. <?php echo $quizName ?></label><input type="hidden" name="quizId" value="<?php echo $quizId ?>" />|
            <label>Author: <?php echo $author ?></label>|
            <label>Duration: <?php echo $duration ?> minutes</label>
        </div>
        <div id="questions"><?php renderQuestionsAll() ?></div>
        <input type="submit" value="Submit" <?php echo $GLOBALS['mode'] === 'r' ? 'disabled' : '' ?> />
    </form>
    <a href="<?php echo $pwd . 'quiz.php' ?>"><button class="back">Back to Quizes</button></a>
</body>

</html>

<?php
function renderQuestionsAll()
{
    $stmt = execSQL(
        $GLOBALS['pdo'],
        'SELECT ques_id, name, score FROM question
        WHERE quiz_id = :quizId
        ORDER BY ques_id',
        ['quizId' => $GLOBALS['quizId']]
    );
    if ($stmt->rowCount() === 0) {
        echo '<p>There is no question here.</p>';
    } else {
        while ($row = $stmt->fetch()) {
            echo '<div class="question">';
            renderQuestion($row);
            echo '</div>';
        }
    }
}

function renderQuestion($row)
{
    $quizId = $GLOBALS['quizId'];
    $quesId = $row['ques_id'];
    $quesName = $row['name'];
    $quesScore = $row['score'];
    echo '<div class="score"><label>Score: ' . $quesScore . '</label></div>';
    echo '<div class="questionText"><label>Question ' . $quesId . '. ' . $quesName . '</label></div>';

    $stmt = execSQL(
        $GLOBALS['pdo'],
        'SELECT opt_id, name FROM `option`
        WHERE quiz_id = :quizId AND ques_id = :quesId
        ORDER BY opt_id',
        ['quizId' => $quizId, 'quesId' => $quesId]
    );
    echo '<div class="options">';
    if ($stmt->rowCount() === 0) {
        echo '<p>No options available.</p>';
    } else {
        while ($row = $stmt->fetch()) {
            renderOption($quesId, $row);
        }
    }
    echo '</div>';
}

function renderOption($quesId, $row)
{
    $optId = $row['opt_id'];
    $optName = $row['name'];
    echo '<div class="option">';
    echo '<input type="radio" name="q' . $quesId . '" value="' . $optId . '" id="q' . $quesId . 'o' . $optId . '" ' . ($GLOBALS['mode'] === 'r' ? 'disabled' : '') . ' />';
    echo '<label for="q' . $quesId . 'o' . $optId . '">' . $optName . '</label>';
    echo '</div>';
}

function processSubmit()
{
    $quizId = $GLOBALS['quizId'];
    // check if a student
    if ($_SESSION['isStaff']) {
        redirect('quiz.php');
    } else {
        $stmt = execSQL(
            $GLOBALS['pdo'],
            'SELECT date FROM quiz
            LEFT OUTER JOIN attempt ON quiz_id = quiz.id AND stu_id = :stuId
            WHERE quiz.id = :quizId AND is_available = TRUE',
            ['stuId' => $_SESSION['id'], 'quizId' => $quizId]
        );
    }
    $row = $stmt->fetch();
    // check existence and whether finished
    if ($row === false || isset($row['date'])) {
        redirect('quiz.php');
    }

    // fetch correct answers and question scores
    $stmt = execSQL(
        $GLOBALS['pdo'],
        'SELECT `option`.ques_id, opt_id, score FROM `option`
        INNER JOIN question ON question.quiz_id = `option`.quiz_id AND question.ques_id = `option`.ques_id
        WHERE `option`.quiz_id = :quizId AND is_correct = TRUE',
        ['quizId' => $quizId]
    );
    $scoreSum = 0;
    while ($row = $stmt->fetch()) {
        // check correctness
        if (isset($_POST['q' . $row['ques_id']]) && $_POST['q' . $row['ques_id']] === $row['opt_id']) {
            $scoreSum += $row['score'];
        }
    }

    // insert into attempt
    execSQL(
        $GLOBALS['pdo'],
        'INSERT INTO attempt (quiz_id, stu_id, date, score)
        VALUES (:quizId, :stuId, CURDATE(), :score)',
        ['quizId' => $quizId, 'stuId' => $_SESSION['id'], 'score' => $scoreSum]
    );
}
?>