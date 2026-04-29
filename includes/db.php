<?php
session_start();
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'digital_constituency';
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

function getUser()
{
    return $_SESSION['user'] ?? null;
}

function requireLogin()
{
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($role)
{
    requireLogin();
    $userRole = $_SESSION['user']['role'] ?? null;
    if (is_array($role)) {
        if (!in_array($userRole, $role, true)) {
            header('Location: index.php');
            exit;
        }
    } else {
        if ($userRole !== $role) {
            header('Location: index.php');
            exit;
        }
    }
}
?>