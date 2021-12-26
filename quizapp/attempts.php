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

$pdo = getPDOConnection();

if (isset($_GET['id'])) {
    $quizId = $_GET['id'];
    $stmt = execSQL(
        $pdo,
        'SELECT name, firstname, lastname, duration, is_available FROM quiz
        INNER JOIN staff ON author_id = staff.id
        INNER JOIN user ON staff.username = user.username
        WHERE quiz.id = :quizId',
        ['quizId' => $quizId]
    );
    $row = $stmt->fetch();
    // check existence of quiz
    if ($row === false) {
        redirect('quiz.php');
    }
    $quizName = $row['name'];
    $author = $row['firstname'] . ' ' . $row['lastname'];
    $duration = $row['duration'];
    $isAvailable = $row['is_available'];
    $attempts = execSQL(
        $GLOBALS['pdo'],
        'SELECT COUNT(*) FROM attempt WHERE quiz_id = :quizId',
        ['quizId' => $quizId]
    )->fetchColumn();
} else {
    redirect('quiz.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Quiz App - Attempts</title>
    <meta name="author" content="Yanze Xin">
    <meta name="description" content="An elegant quiz application">
    <link rel="stylesheet" type="text/css" href="<?php echo $pwd . 'css/style.css' ?>">
</head>

<body>
    <?php renderTopBar() ?>
    <h1>Student Attempts</h1>

    <div id="quizBar">
        <label>Quiz <?php echo $quizId ?>. <?php echo $quizName ?></label>|
        <label>Author: <?php echo $author ?></label>|
        <label>Duration: <?php echo $duration ?> minutes</label>|
        <label>Availability: <?php echo $isAvailable ? 'Yes' : 'No' ?></label>|
        <label>Attempts: <?php echo $attempts ?></label>
    </div>
    <table align="center">
        <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Attempt Date</th>
            <th>Score</th>
        </tr>
        <?php renderAttempts() ?>
    </table>

    <a href="<?php echo $pwd . 'quiz.php' ?>"><button class="back">Back to Quizes</button></a>
</body>

</html>

<?php
function renderAttempts()
{
    $stmt = execSQL(
        $GLOBALS['pdo'],
        'SELECT stu_id, firstname, lastname, date, score FROM attempt
        INNER JOIN student ON stu_id = student.id
        INNER JOIN user on student.username = user.username
        WHERE quiz_id = :quizId',
        ['quizId' => $GLOBALS['quizId']]
    );
    if ($stmt->rowCount() === 0) {
        redirect('quiz.php');
    } else {
        while ($row = $stmt->fetch()) {
            echo '<tr><td>' . $row['stu_id'] . '</td><td>' . $row['firstname'] . ' ' . $row['lastname'] . '</td><td>' . $row['date'] . '</td><td>' . $row['score'] . '</td></tr>';
        }
    }
}
?>