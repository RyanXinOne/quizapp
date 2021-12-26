<?php
// get relative path of parent working directory in request uri
$pwd = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/') + 1);

/**
 * Create PDO connection to mysql database
 */
function getPDOConnection()
{
    $host = 'localhost';
    $dbname = 'quizapp';
    $username = 'root';
    $password = 'password';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        return $pdo;
    } catch (PDOException $e) {
        echo '<p>Failed to connect to database: ' . $e->getMessage() . '</p>';
        die();
    }
}

/**
 * Execute a query and return the statement object
 */
function execSQL($pdo, $sql, $params = null)
{
    $stmt = $pdo->prepare($sql);
    if (!$stmt || !$stmt->execute($params)) {
        echo '<p>Failed to execute statement: ' . ($stmt ? $stmt->errorInfo()[2] : '') . '</p>';
        die();
    }
    return $stmt;
}

/**
 * Transform minutes into readable time format
 */
function minutesToTime($minutes)
{
    $hours = floor($minutes / 60);
    $minutes = $minutes % 60;
    return sprintf('%02d:%02d', $hours, $minutes);
}

/**
 * Redirect user to specific page
 */
function redirect($page)
{
    header('Location: ' . $GLOBALS['pwd'] . $page);
    die();
}

/**
 * Render top bar of quiz application, can only be called when logged in
 */
function renderTopBar()
{
    if (isset($_SESSION['username'])) {
        // render top bar
        echo '<div id="topbar">';
        echo '<span>Hello, ' . $_SESSION['firstname'] . ' ' . $_SESSION['lastname'] . ' | ' . 'Username: ' . $_SESSION['username'] . ' | Role: ' . ($_SESSION['isStaff'] ? 'staff' : 'student') . '</span>';
        echo ' | <a href="' . $GLOBALS['pwd'] . 'login.php?action=logout">Logout</a>';
        echo '</div>';
    }
}
