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

<div class="back-btn-container">
    <a href="dashboard_citizen.php" class="button outline back-btn">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Back to Dashboard
    </a>
</div>

<div class="mb-4">
    <h1 class="section-title">Constituency Announcements</h1>
</div>

<div style="max-width: 900px; margin: 0 auto;">
    <?php if ($announcements): ?>
        <div style="display: flex; flex-direction: column; gap: 30px;">
            <?php foreach ($announcements as $item): ?>
                <div class="card" style="position: relative; overflow: hidden;">
                    <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--primary-gradient);"></div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                        <h3 style="margin: 0; font-size: 1.4rem; color: var(--primary-dark);"><?= htmlspecialchars($item['title']) ?></h3>
                        <span class="badge primary">Official</span>
                    </div>
                    <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-main); margin-bottom: 24px;">
                        <?= nl2br(htmlspecialchars($item['message'])) ?>
                    </p>
                    <div style="display: flex; align-items: center; gap: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
                        <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="var(--text-muted)" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <small class="text-muted" style="font-weight: 700;"><?= date('F j, Y \a\t g:i a', strtotime($item['created_at'])) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                <h3>No announcements yet</h3>
                <p>Stay tuned! Important updates from your representative will appear here.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>