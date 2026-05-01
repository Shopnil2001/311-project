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
        <a class="brand" href="index.php">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="url(#brand-gradient)"/>
                <path d="M2 17L12 22L22 17M2 12L12 17L22 12" stroke="url(#brand-gradient)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <defs>
                    <linearGradient id="brand-gradient" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#6366f1"/>
                        <stop offset="1" stop-color="#a855f7"/>
                    </linearGradient>
                </defs>
            </svg>
            <span style="letter-spacing: -1px;">Constituency<span style="font-weight: 400; opacity: 0.8;">Hub</span></span>
        </a>
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
                <a href="logout.php" class="nav-btn">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" class="nav-btn">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">