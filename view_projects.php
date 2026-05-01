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

<div class="back-btn-container">
    <a href="dashboard_citizen.php" class="button outline back-btn">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Back to Dashboard
    </a>
</div>

<div class="mb-4">
    <h1 class="section-title">Development Projects</h1>
</div>

<div style="margin-bottom: 60px;">
    <?php if ($projects): ?>
        <div class="grid">
            <?php foreach ($projects as $project): ?>
                <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                    <div style="height: 220px; background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/The_padma_bridge_02.jpg/1280px-The_padma_bridge_02.jpg'); background-size: cover; background-position: center; position: relative;">
                        <div style="position: absolute; top: 20px; right: 20px;">
                            <span class="badge <?= strtolower($project['status']) === 'completed' ? 'success' : 'primary' ?>" style="box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(4px);">
                                <?= htmlspecialchars($project['status']) ?>
                            </span>
                        </div>
                    </div>
                    <div style="padding: 30px; flex: 1; display: flex; flex-direction: column;">
                        <h3 style="font-size: 1.4rem; margin-bottom: 15px; color: var(--text-main);"><?= htmlspecialchars($project['title']) ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 25px; flex: 1;">
                            <?= nl2br(htmlspecialchars($project['description'])) ?>
                        </p>
                        <div style="padding-top: 20px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 24px; height: 24px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="var(--text-muted)" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                </div>
                                <small class="text-muted" style="font-weight: 700;"><?= date('M d, Y', strtotime($project['created_at'])) ?></small>
                            </div>
                            <a href="#" class="read-more" style="font-weight: 800;">Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <h3>No active projects</h3>
                <p>We are constantly working on new developments. Check back soon for updates on infrastructure and community projects.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>