<?php
require_once 'includes/db.php';
requireRole('citizen');
$citizenId = $_SESSION['user']['id'];
$stmt = $mysqli->prepare('SELECT announcements.title, announcements.message, announcements.created_at FROM announcements JOIN mps ON announcements.mp_id = mps.id JOIN sectors ON sectors.mp_id = mps.id JOIN citizens ON citizens.sector_id = sectors.id WHERE citizens.id = ? ORDER BY announcements.created_at DESC');
$stmt->bind_param('i', $citizenId);
$stmt->execute();
$announcements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Announcements</h1>
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
        <p>No announcements available for your sector.</p>
    <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>