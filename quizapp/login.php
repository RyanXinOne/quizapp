<?php
require_once './utility.php';
session_start();
// if logged in, redirect to quiz page
$loggedout = false;
if (isset($_SESSION['username'])) {
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        // logout user
        unset($_SESSION['username']);
        session_unset();
        $loggedout = true;
    } else {
        redirect('quiz.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Quiz App - Login</title>
    <meta name="author" content="Yanze Xin">
    <meta name="description" content="An elegant quiz application">
    <link rel="stylesheet" type="text/css" href="<?php echo $pwd . 'css/style.css' ?>">
</head>

<body>
    <h1>Login</h1>
    <?php
    // check if user is submitting the form
    if (isset($_POST['username'])) {
        processLoginForm();
    } else {
        renderLoginForm($loggedout ? 'You have logged out successfully.' : null);
    }
    ?>
</body>

</html>

<?php
function renderLoginForm($message = null)
{
    if (isset($message)) {
        echo '<p>Message from server: ' . $message . '</p>';
    }
    echo '
        <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
            <div class="formRow"><label class="ewlabel" for="i0">Username:</label><input type="text" name="username" id="i0" required></div>
            <div class="formRow"><label class="ewlabel" for="i1">Password:</label><input type="password" name="password" id="i1" required></div>
            <div class="formRow"><input type="submit" value="Login"></div>
        </form>';
    echo '<p><a href="' . $GLOBALS['pwd'] . 'register.php">go to Register</a></p>';
}

function processLoginForm()
{
    // validate user input
    if (empty($_POST['username']) || empty($_POST['password'])) {
        renderLoginForm('Please fill in all the fields.');
        return;
    }

    // prepare data for query
    $username = $_POST['username'];
    $password = $_POST['password'];
    $pdo = getPDOConnection();

    // get hashed password from database and verify
    $stmt = execSQL(
        $pdo,
        'SELECT password FROM user WHERE username = :username',
        ['username' => $username]
    );
    $row = $stmt->fetch();
    if ($row === false || !password_verify($password, $row['password'])) {
        renderLoginForm('Invalid username or password.');
        return;
    }

    // success, set session variables and redirect to quiz page
    $_SESSION['username'] = $username;
    $stmt = execSQL(
        $pdo,
        'SELECT firstname, lastname, is_staff FROM user
        WHERE username = :username',
        ['username' => $username]
    );
    $row = $stmt->fetch();
    $_SESSION['firstname'] = $row['firstname'];
    $_SESSION['lastname'] = $row['lastname'];
    $_SESSION['isStaff'] = $row['is_staff'];
    $stmt = execSQL(
        $pdo,
        'SELECT id FROM ' . ($row['is_staff'] ? 'staff' : 'student') . '
        WHERE username = :username',
        ['username' => $username]
    );
    $row = $stmt->fetch();
    $_SESSION['id'] = $row['id'];
    redirect('quiz.php');
}
?>