<?php
require_once 'includes/db.php';
requireRole('citizen');
$user = $_SESSION['user'];
$citizenId = $user['id'];
$citizen = [];
$mp = null;
$announcements = [];
$projects = [];
$recentComplaints = [];
$recentSuggestions = [];

$stmt = $mysqli->prepare('SELECT citizens.name, citizens.email, citizens.phone, sectors.name AS sector_name, constituencies.name AS constituency_name, mps.id AS mp_id, mps.name AS mp_name, mps.email AS mp_email FROM citizens LEFT JOIN sectors ON citizens.sector_id = sectors.id LEFT JOIN constituencies ON sectors.constituency_id = constituencies.id LEFT JOIN mps ON sectors.mp_id = mps.id WHERE citizens.id = ?');
$stmt->bind_param('i', $citizenId);
$stmt->execute();
$citizen = $stmt->get_result()->fetch_assoc();

if ($citizen && $citizen['mp_id']) {
    $mp = [
        'id' => $citizen['mp_id'],
        'name' => $citizen['mp_name'],
        'email' => $citizen['mp_email'],
    ];
    $announceStmt = $mysqli->prepare('SELECT title, message, created_at FROM announcements WHERE mp_id = ? ORDER BY created_at DESC LIMIT 3');
    $announceStmt->bind_param('i', $mp['id']);
    $announceStmt->execute();
    $announcements = $announceStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $projectStmt = $mysqli->prepare('SELECT title, description, status, created_at FROM mp_projects WHERE mp_id = ? ORDER BY created_at DESC LIMIT 3');
    $projectStmt->bind_param('i', $mp['id']);
    $projectStmt->execute();
    $projects = $projectStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$complaintCount = (int) $mysqli->query("SELECT COUNT(*) AS total FROM complaints WHERE citizen_id = $citizenId")->fetch_assoc()['total'];
$suggestionCount = (int) $mysqli->query("SELECT COUNT(*) AS total FROM suggestions WHERE citizen_id = $citizenId")->fetch_assoc()['total'];
$crimeCount = (int) $mysqli->query("SELECT COUNT(*) AS total FROM crime_reports WHERE citizen_id = $citizenId")->fetch_assoc()['total'];

$complaintStmt = $mysqli->prepare('SELECT complaints.subject, complaint_status.name AS status_name, complaints.created_at FROM complaints LEFT JOIN complaint_status ON complaints.status_id = complaint_status.id WHERE complaints.citizen_id = ? ORDER BY complaints.created_at DESC LIMIT 4');
$complaintStmt->bind_param('i', $citizenId);
$complaintStmt->execute();
$recentComplaints = $complaintStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$suggestionStmt = $mysqli->prepare('SELECT title, created_at FROM suggestions WHERE citizen_id = ? ORDER BY created_at DESC LIMIT 4');
$suggestionStmt->bind_param('i', $citizenId);
$suggestionStmt->execute();
$recentSuggestions = $suggestionStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<section class="hero">
    <h1>Welcome back, <?= htmlspecialchars($citizen['name']) ?></h1>
    <p>Sector: <?= htmlspecialchars($citizen['sector_name']) ?> | Constituency: <?= htmlspecialchars($citizen['constituency_name']) ?></p>
</section>
<section class="grid">
    <div class="card">
        <h2>Quick actions</h2>
        <p><a class="button" href="submit_complaint.php">Submit Complaint</a></p>
        <p><a class="button" href="submit_suggestion.php">Submit Suggestion</a></p>
        <p><a class="button" href="submit_crime.php">Report Crime</a></p>
        <p><a class="button" href="manage_my_submissions.php">My Submissions</a></p>
        <p><a class="button" href="view_announcements.php">Announcements</a></p>
        <p><a class="button" href="view_projects.php">Projects</a></p>
    </div>
    <div class="card">
        <h2>Activity overview</h2>
        <ul>
            <li><strong><?= $complaintCount ?></strong> complaints filed</li>
            <li><strong><?= $suggestionCount ?></strong> suggestions sent</li>
            <li><strong><?= $crimeCount ?></strong> crime reports</li>
        </ul>
    </div>
    <div class="card">
        <h2>Your MP</h2>
        <?php if ($mp): ?>
            <p><strong><?= htmlspecialchars($mp['name']) ?></strong></p>
            <p>Email: <?= htmlspecialchars($mp['email']) ?></p>
        <?php else: ?>
            <p>Your MP is not assigned yet. Check announcements for the latest updates.</p>
        <?php endif; ?>
    </div>
</section>
<section class="grid">
    <div class="card">
        <h2>Recent complaints</h2>
        <?php if ($recentComplaints): ?>
            <ul>
                <?php foreach ($recentComplaints as $item): ?>
                    <li><strong><?= htmlspecialchars($item['subject']) ?></strong> — <?= htmlspecialchars($item['status_name'] ?: 'New') ?><br><small><?= htmlspecialchars($item['created_at']) ?></small></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No complaints submitted yet.</p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2>Recent suggestions</h2>
        <?php if ($recentSuggestions): ?>
            <ul>
                <?php foreach ($recentSuggestions as $item): ?>
                    <li><strong><?= htmlspecialchars($item['title']) ?></strong><br><small><?= htmlspecialchars($item['created_at']) ?></small></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No suggestions submitted yet.</p>
        <?php endif; ?>
    </div>
</section>
<section class="grid">
    <div class="card">
        <h2>Latest announcements</h2>
        <?php if ($announcements): ?>
            <?php foreach ($announcements as $item): ?>
                <div>
                    <strong><?= htmlspecialchars($item['title']) ?></strong>
                    <p><?= nl2br(htmlspecialchars($item['message'])) ?></p>
                    <small><?= htmlspecialchars($item['created_at']) ?></small>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No announcements yet.</p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2>Latest projects</h2>
        <?php if ($projects): ?>
            <?php foreach ($projects as $project): ?>
                <div>
                    <strong><?= htmlspecialchars($project['title']) ?></strong>
                    <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                    <span class="status-pill"><?= htmlspecialchars($project['status']) ?></span>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No project updates yet.</p>
        <?php endif; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
