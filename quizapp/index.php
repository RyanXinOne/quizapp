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
    <title>My Quiz App</title>
    <meta name="author" content="Yanze Xin">
    <meta name="description" content="An elegant quiz application">
    <link rel="stylesheet" type="text/css" href="<?php echo $pwd . 'css/style.css' ?>">
</head>

<body>
    <h1>My Quiz App</h1>
    <p>Welcome to my quiz application!</p>
    <a href="<?php echo $pwd ?>login.php"><button>Login</button></a>
    <a href="<?php echo $pwd ?>register.php"><button>Register</button></a>
</body>

</html>