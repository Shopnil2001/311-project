<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Constituency Management</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="site-header">
    <div class="container header-content">
        <a class="brand" href="index.php">Constituency Hub</a>
        <nav class="main-nav">
            <a href="index.php">Home</a>
            <?php if ($user): ?>
                <?php if ($user['role'] === 'citizen'): ?>
                    <a href="dashboard_citizen.php">My Dashboard</a>
                    <a href="manage_my_submissions.php">My Submissions</a>
                    <a href="view_announcements.php">Announcements</a>
                    <a href="view_projects.php">Projects</a>
                <?php elseif ($user['role'] === 'mp'): ?>
                    <a href="dashboard_mp.php">MP Dashboard</a>
                    <a href="manage_complaints.php">Complaints</a>
                    <a href="manage_crime.php">Crime reports</a>
                    <a href="manage_suggestions.php">Suggestions</a>
                    <a href="manage_projects.php">Projects</a>
                <?php elseif ($user['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php">Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">