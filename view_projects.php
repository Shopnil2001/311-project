<?php
require_once 'includes/db.php';
requireRole('citizen');
$citizenId = $_SESSION['user']['id'];
$stmt = $mysqli->prepare('SELECT mp_projects.title, mp_projects.description, mp_projects.status, mp_projects.created_at FROM mp_projects JOIN mps ON mp_projects.mp_id = mps.id JOIN sectors ON sectors.mp_id = mps.id JOIN citizens ON citizens.sector_id = sectors.id WHERE citizens.id = ? ORDER BY mp_projects.created_at DESC');
$stmt->bind_param('i', $citizenId);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Development Projects</h1>
    <?php if ($projects): ?>
        <?php foreach ($projects as $project): ?>
            <div>
                <strong><?= htmlspecialchars($project['title']) ?></strong>
                <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                <span class="status-pill"><?= htmlspecialchars($project['status']) ?></span>
                <small><?= htmlspecialchars($project['created_at']) ?></small>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No project updates have been published yet.</p>
    <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>