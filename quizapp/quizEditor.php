<?php
require_once './utility.php';
session_start();
// if not logged in, redirect to login page
if (!isset($_SESSION['username'])) {
    redirect('login.php');
}
// if a student, redirect to quiz page
if (!$_SESSION['isStaff']) {
    redirect('quiz.php');
}

$message = null;
$pdo = getPDOConnection();

$quizId = null;
if (isset($_POST['quizId'])) {
    $quizId = processSave();
} else if (isset($_GET['id'])) {
    $quizId = $_GET['id'];
}

if (isset($quizId)) {
    // edit an existing quiz
    $stmt = execSQL(
        $pdo,
        'SELECT name, author_id, duration, is_available FROM quiz
        WHERE id = :quizId',
        ['quizId' => $quizId]
    );
    $row = $stmt->fetch();
    // check existence of quiz and proper author
    if ($row === false || $row['author_id'] !== $_SESSION['id']) {
        redirect('quiz.php');
    }
    $quizName = $row['name'];
    $author = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
    $duration = $row['duration'];
    $isAvailable = $row['is_available'];
} else {
    // create a new quiz
    $quizId = -1;
    $quizName = 'New Quiz';
    $author = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
    $duration = 60;
    $isAvailable = '1';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Quiz App - Quiz Editor</title>
    <meta name="author" content="Yanze Xin">
    <meta name="description" content="An elegant quiz application">
    <script type="text/javascript" src="<?php echo $pwd . 'js/quizEditor.js' ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $pwd . 'css/style.css' ?>">
</head>

<body>
    <?php renderTopBar() ?>
    <h1>Quiz Editor</h1>
    <?php
    if (isset($message)) {
        echo '<p>Message from server: ' . $message . '</p>';
    }
    ?>
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
        <div id="quizBar">
            <label><?php echo $quizId > -1 ? 'Quiz ID:' : 'Create New Quiz' ?></label><input type="<?php echo $quizId > -1 ? 'text' : 'hidden' ?>" name="quizId" value="<?php echo $quizId ?>" readonly />
            <label for="i1">Quiz Name:</label><input id="i1" type="text" name="quizName" value="<?php echo $quizName ?>" required />
            <label>Author:</label><input type="text" value="<?php echo $author ?>" readonly />
            <label for="i2">Duration(mins):</label><input id="i2" type="number" min="1" name="duration" value="<?php echo $duration ?>" required />
            <label>Availability:</label><input id="r1" type="radio" name="isAvailable" value="1" <?php echo $isAvailable ? 'checked' : '' ?> /><label for="r1">Yes</label><input id="r2" type="radio" name="isAvailable" value="0" <?php echo !$isAvailable ? 'checked' : '' ?> /><label for="r2">No</label>
        </div>
        <div id="questions" class="editor"><?php renderQuestionsAll() ?></div>
        <input type="button" value="Add Question" onclick="addQuestion()" /><br>
        <input type="submit" value="Save" />
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
    echo '<div class="score"><label>Score:</label><input type="number" min="0" name="q' . $quesId . 'score" value="' . $quesScore . '" required /></div>';
    echo '<div class="questionText"><label>Question ' . $quesId . '</label><textarea name="q' . $quesId . '" placeholder="Question Texts" required>' . $quesName . '</textarea></div>';

    $stmt = execSQL(
        $GLOBALS['pdo'],
        'SELECT opt_id, name, is_correct FROM `option`
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
    echo '<input type="button" value="Add Option" onclick="addOption(' . $quesId . ')" />';
    echo '<input type="button" value="Delete Question" onclick="deleteQuestion(' . $quesId . ')" />';
}

function renderOption($quesId, $row)
{
    $optId = $row['opt_id'];
    $optName = $row['name'];
    $isCorrect = $row['is_correct'];
    echo '<div class="option">';
    echo '<input type="button" value="-" onclick="deleteOption(' . $quesId . ',' . $optId . ')" />';
    echo '<input type="radio" name="q' . $quesId . 'ans" value="' . $optId . '" ' . ($isCorrect ? 'checked' : '') . ' required />';
    echo '<textarea name="q' . $quesId . 'o' . $optId . '" placeholder="Option Texts" required>' . $optName . '</textarea>';
    echo '</div>';
}

function processSave()
{
    $quizId = $_POST['quizId'];
    if ($quizId > -1) {
        // if being edited, check existence of quiz and proper author
        $stmt = execSQL(
            $GLOBALS['pdo'],
            'SELECT author_id FROM quiz WHERE id = :quizId',
            ['quizId' => $quizId]
        );
        $row = $stmt->fetch();
        if ($row === false || $row['author_id'] !== $_SESSION['id']) {
            redirect('quiz.php');
        }
    }

    $GLOBALS['pdo']->beginTransaction();

    // update quiz table
    $quizName = $_POST['quizName'];
    $duration = $_POST['duration'];
    $isAvailable = $_POST['isAvailable'];
    if ($quizId == -1) {
        execSQL(
            $GLOBALS['pdo'],
            'INSERT INTO quiz (name, duration, author_id, is_available)
            VALUES (:quizName, :duration, :authorId, :isAvailable)',
            ['quizName' => $quizName, 'duration' => $duration, 'authorId' => $_SESSION['id'], 'isAvailable' => $isAvailable]
        );
        // get new quizId
        $quizId = $GLOBALS['pdo']->lastInsertId();
    } else {
        execSQL(
            $GLOBALS['pdo'],
            'UPDATE quiz SET name = :quizName, duration = :duration, is_available = :isAvailable
            WHERE id = :quizId',
            ['quizName' => $quizName, 'duration' => $duration, 'isAvailable' => $isAvailable, 'quizId' => $quizId]
        );
    }

    // delete existing questions
    execSQL(
        $GLOBALS['pdo'],
        'DELETE FROM question WHERE quiz_id = :quizId',
        ['quizId' => $quizId]
    );
    // update question table
    $quesId = 1;
    while (isset($_POST['q' . $quesId])) {
        $quesName = $_POST['q' . $quesId];
        $quesScore = $_POST['q' . $quesId . 'score'];
        execSQL(
            $GLOBALS['pdo'],
            'INSERT INTO question (quiz_id, ques_id, name, score)
            VALUES (:quizId, :quesId, :quesName, :quesScore)',
            ['quizId' => $quizId, 'quesId' => $quesId, 'quesName' => $quesName, 'quesScore' => $quesScore]
        );

        // update option table
        $optId = 1;
        while (isset($_POST['q' . $quesId . 'o' . $optId])) {
            $optName = $_POST['q' . $quesId . 'o' . $optId];
            $isCorrect = $_POST['q' . $quesId . 'ans'] == $optId ? 1 : 0;
            execSQL(
                $GLOBALS['pdo'],
                'INSERT INTO `option` (quiz_id, ques_id, opt_id, name, is_correct)
                VALUES (:quizId, :quesId, :optId, :optName, :isCorrect)',
                ['quizId' => $quizId, 'quesId' => $quesId, 'optId' => $optId, 'optName' => $optName, 'isCorrect' => $isCorrect]
            );
            $optId++;
        }
        $quesId++;
    }

    $GLOBALS['pdo']->commit();

    $GLOBALS['message'] = 'Quiz saved successfully.';
    return $quizId;
}
?>