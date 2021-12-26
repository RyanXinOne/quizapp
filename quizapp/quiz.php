<?php
require_once './utility.php';
session_start();
// if not logged in, redirect to login page
if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

$pdo = getPDOConnection();

if ($_SESSION['isStaff'] && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    processDelete();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Quiz App - Quizes</title>
    <meta name="author" content="Yanze Xin">
    <meta name="description" content="An elegant quiz application">
    <link rel="stylesheet" type="text/css" href="<?php echo $pwd . 'css/style.css' ?>">
</head>

<body>
    <?php renderTopBar() ?>
    <h1>Quizes</h1>
    <?php showQuizePage() ?>
</body>

</html>

<?php
function showQuizePage()
{
    // fetch user information from session variables
    $isStaff = $_SESSION['isStaff'];
    // render create button
    if ($isStaff) {
        echo '<p><a href="' . $GLOBALS['pwd'] . 'quizEditor.php"><button>Create New Quiz</button></a></p>';
    }

    // fetch all quizes
    if ($isStaff) {
        $stmt = execSQL(
            $GLOBALS['pdo'],
            'SELECT quiz.id, name, author_id, firstname, lastname, duration, is_available FROM quiz
            INNER JOIN staff ON author_id = staff.id
            INNER JOIN user ON staff.username = user.username'
        );
    } else {
        $stmt = execSQL(
            $GLOBALS['pdo'],
            'SELECT quiz.id, name, firstname, lastname, duration, date, score FROM quiz
            INNER JOIN staff ON author_id = staff.id
            INNER JOIN user ON staff.username = user.username
            LEFT OUTER JOIN attempt ON quiz_id = quiz.id AND stu_id = :stuId
            WHERE is_available = TRUE',
            ['stuId' => $_SESSION['id']]
        );
    }
    if ($stmt->rowCount() === 0) {
        echo '<p>No quizes available.</p>';
    } else {
        renderQuizTable($stmt);
    }
}

function renderQuizTable($stmt)
{
    $isStaff = $_SESSION['isStaff'];
    echo '<table align="center"><tr>';
    echo '<th>ID</th>';
    echo '<th>Name</th>';
    echo '<th>Author</th>';
    echo '<th>Duration(H:M)</th>';
    if ($isStaff) {
        echo '<th>Availability</th>';
        echo '<th>Attempts</th>';
    }
    if (!$isStaff) {
        echo '<th>Attempt Date</th>';
        echo '<th>Score</th>';
    }
    echo '<th>Operation</th>';
    echo '</tr>';
    while ($row = $stmt->fetch()) {
        echo '<tr>';
        // ID
        echo '<td>' . $row['id'] . '</td>';
        // Name
        echo '<td>' . $row['name'] . '</td>';
        // Author
        echo '<td>' . $row['firstname'] . ' ' . $row['lastname'] . '</td>';
        // Duration
        echo '<td>' . minutesToTime($row['duration']) . '</td>';
        if ($isStaff) {
            // Availability
            echo '<td>' . ($row['is_available'] ? 'Yes' : 'No') . '</td>';
            // Attempts
            $attempts = execSQL(
                $GLOBALS['pdo'],
                'SELECT COUNT(*) FROM attempt WHERE quiz_id = :quizId',
                ['quizId' => $row['id']]
            )->fetchColumn();
            echo '<td>';
            if ($attempts > 0) {
                echo '<a href="' . $GLOBALS['pwd'] . 'attempts.php?id=' . $row['id'] . '">' . $attempts . '</a>';
            } else {
                echo $attempts;
            }
            echo '</td>';
        }
        if (!$isStaff) {
            // Attempt Date
            echo '<td>' . (isset($row['date']) ? $row['date'] : '-') . '</td>';
            // Score
            echo '<td>' . (isset($row['score']) ? $row['score'] : '-') . '</td>';
        }
        // Operation
        if ($isStaff) {
            echo '<td><a href="' . $GLOBALS['pwd'] . 'quizRoom.php?id=' . $row['id'] . '">View</a>';
            if ($_SESSION['id'] === $row['author_id']) {
                echo ' <a href="' . $GLOBALS['pwd'] . 'quizEditor.php?id=' . $row['id'] . '">Edit</a>
                    <a href="' . $_SERVER['PHP_SELF'] . '?id=' . $row['id'] . '&action=delete">Delete</a>';
            }
            echo '</td>';
        } else {
            echo '<td><a href="' . $GLOBALS['pwd'] . 'quizRoom.php?id=' . $row['id'] . '">' . (isset($row['date']) ? 'View' : 'Start') . '</a></td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}

function processDelete()
{
    $quizId = $_GET['id'];
    // check existence of quiz and proper author
    $stmt = execSQL(
        $GLOBALS['pdo'],
        'SELECT author_id FROM quiz WHERE id = :quizId',
        ['quizId' => $quizId]
    );
    $row = $stmt->fetch();
    if ($row === false || $row['author_id'] !== $_SESSION['id']) {
        return;
    }

    // delete quiz
    execSQL(
        $GLOBALS['pdo'],
        'DELETE FROM quiz WHERE id = :quizId',
        ['quizId' => $quizId]
    );
    redirect('quiz.php');
}
?>