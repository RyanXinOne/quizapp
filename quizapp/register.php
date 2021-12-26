<?php
require_once './utility.php';
session_start();
// if logged in, redirect to quiz page
if (isset($_SESSION['username'])) {
    redirect('quiz.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Quiz App - Register</title>
    <meta name="author" content="Yanze Xin">
    <meta name="description" content="An elegant quiz application">
    <link rel="stylesheet" type="text/css" href="<?php echo $pwd . 'css/style.css' ?>">
</head>

<body>
    <h1>Register</h1>
    <?php
    // check if user is submitting the form
    if (isset($_POST['username'])) {
        processRegistrationForm();
    } else {
        renderRegistrationForm();
    }
    ?>
</body>

</html>

<?php
function renderRegistrationForm($message = null)
{
    if (isset($message)) {
        echo '<p>Message from server: ' . $message . '</p>';
    }
    echo '
        <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
            <div class="formRow"><label class="ewlabel" for="i0">Username:</label><input type="text" name="username" id="i0" required></div>
            <div class="formRow"><label class="ewlabel" for="i1">Password:</label><input type="password" name="password" id="i1" required></div>
            <div class="formRow"><label class="ewlabel" for="i2">Confirm Password:</label><input type="password" name="cpassword" id="i2" required></div>
            <div class="formRow"><label class="ewlabel" for="i3">First Name:</label><input type="text" name="firstname" id="i3" required></div>
            <div class="formRow"><label class="ewlabel" for="i4">Last Name:</label><input type="text" name="lastname" id="i4" required></div>
            <div class="formRow"><input type="radio" name="is_staff" value="0" id="r0" checked><label for="r0">I am a student</label>
            <input type="radio" name="is_staff" value="1" id="r1"><label for="r1">I am a staff</label></div>
            <div class="formRow"><input type="submit" value="Register"></div>
        </form>';
    echo '<p><a href="' . $GLOBALS['pwd'] . 'login.php">go to Login</a></p>';
}

function processRegistrationForm()
{
    // validate user input
    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['cpassword']) || empty($_POST['firstname']) || empty($_POST['lastname'])) {
        renderRegistrationForm('Please fill in all the fields.');
        return;
    }
    if ($_POST['password'] !== $_POST['cpassword']) {
        renderRegistrationForm('Passwords do not match.');
        return;
    }
    if (strlen($_POST['password']) < 6) {
        renderRegistrationForm('Password must be at least 6 characters long.');
        return;
    }
    if ($_POST['is_staff'] !== '0' && $_POST['is_staff'] !== '1') {
        renderRegistrationForm('Please select a valid option.');
        return;
    }

    // prepare data for insertion
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $isStaff = $_POST['is_staff'];
    $role = $isStaff ? 'staff' : 'student';
    $pdo = getPDOConnection();

    // check if username already exists
    $stmt = execSQL(
        $pdo,
        'SELECT * FROM user WHERE username = :username',
        ['username' => $username]
    );
    if ($stmt->rowCount() > 0) {
        renderRegistrationForm('Username already exists.');
        return;
    }

    // create a new entry in user table and a new entry in student/staff table
    $pdo->beginTransaction();
    execSQL(
        $pdo,
        'INSERT INTO user (username, password, firstname, lastname, is_staff)
        VALUES (? , ? , ? , ? , ?)',
        [$username, $password, $firstname, $lastname, $isStaff]
    );
    execSQL(
        $pdo,
        'INSERT INTO ' . $role . ' (username) VALUES (:username)',
        ['username' => $username]
    );
    $pdo->commit();

    // success
    echo '
        <p>Hello, ' . $firstname . ' ' . $lastname . '.</p>
        <p>You registered successfully.</p>
        <p>Username: ' . $username . ', Role: ' . $role . '</p>
        <a href="' . $GLOBALS['pwd'] . 'login.php">Login Now</a>';
}
?>