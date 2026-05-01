<?php
require_once 'includes/db.php';
requireRole('mp');
$mpId = $_SESSION['user']['id'];

function scalarQuery($mysqli, $sql, $types = null, $params = []) {
    $stmt = $mysqli->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? (int) $result['total'] : 0;
}

$citizenCount = scalarQuery($mysqli, 'SELECT COUNT(DISTINCT citizens.id) AS total FROM citizens JOIN sectors ON citizens.sector_id = sectors.id WHERE sectors.mp_id = ?', 'i', [$mpId]);
$complaintCount = scalarQuery($mysqli, 'SELECT COUNT(*) AS total FROM complaints JOIN citizens ON complaints.citizen_id = citizens.id JOIN sectors ON citizens.sector_id = sectors.id WHERE sectors.mp_id = ?', 'i', [$mpId]);
$crimeCount = scalarQuery($mysqli, 'SELECT COUNT(*) AS total FROM crime_reports JOIN citizens ON crime_reports.citizen_id = citizens.id JOIN sectors ON citizens.sector_id = sectors.id WHERE sectors.mp_id = ?', 'i', [$mpId]);
$projectCount = scalarQuery($mysqli, 'SELECT COUNT(*) AS total FROM mp_projects WHERE mp_id = ?', 'i', [$mpId]);
$suggestionCount = scalarQuery($mysqli, 'SELECT COUNT(*) AS total FROM suggestions JOIN citizens ON suggestions.citizen_id = citizens.id JOIN sectors ON citizens.sector_id = sectors.id WHERE sectors.mp_id = ?', 'i', [$mpId]);
$reviewedCrimeCount = 0;
$columnResult = $mysqli->query("SHOW COLUMNS FROM crime_reports LIKE 'is_reviewed'");
if ($columnResult && $columnResult->num_rows > 0) {
    $reviewedCrimeCount = scalarQuery($mysqli, 'SELECT COUNT(*) AS total FROM crime_reports JOIN citizens ON crime_reports.citizen_id = citizens.id JOIN sectors ON citizens.sector_id = sectors.id WHERE sectors.mp_id = ? AND crime_reports.is_reviewed = 1', 'i', [$mpId]);
}

$recentComplaints = [];
$stmt = $mysqli->prepare('SELECT complaints.id, complaints.subject, complaint_status.name AS status_name, complaints.response, citizens.name AS citizen_name, complaints.created_at FROM complaints JOIN citizens ON complaints.citizen_id = citizens.id JOIN sectors ON citizens.sector_id = sectors.id LEFT JOIN complaint_status ON complaints.status_id = complaint_status.id WHERE sectors.mp_id = ? ORDER BY complaints.created_at DESC LIMIT 6');
$stmt->bind_param('i', $mpId);
$stmt->execute();
$recentComplaints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>

<div class="mb-4" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 40px; box-shadow: var(--shadow-sm); border-top: 4px solid var(--primary);">
    <h1 style="font-size: 2.2rem; margin-bottom: 8px; color: var(--text-main);">MP Dashboard</h1>
    <p class="text-muted" style="font-size: 1.1rem;">Manage constituency issues, respond to citizens, and publish development updates.</p>
</div>

<div class="grid mb-4">
    <!-- Quick Stats Summary -->
    <div class="card" style="grid-column: span 1;">
        <h2 style="font-size: 1.4rem; margin-bottom: 24px; border-bottom: 2px solid var(--border); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
            Constituency Stats
        </h2>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; background: #f8fafc; border-radius: var(--radius);">
                <span style="color: var(--text-muted); font-weight: 500;">Registered Citizens</span>
                <span class="badge primary" style="font-size: 0.9rem;"><?= $citizenCount ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; background: #f8fafc; border-radius: var(--radius);">
                <span style="color: var(--text-muted); font-weight: 500;">Total Complaints</span>
                <span class="badge" style="font-size: 0.9rem; background: #fee2e2; color: #dc2626;"><?= $complaintCount ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; background: #f8fafc; border-radius: var(--radius);">
                <span style="color: var(--text-muted); font-weight: 500;">Crime Reports</span>
                <span class="badge" style="font-size: 0.9rem; background: #fecaca; color: #991b1b;"><?= $crimeCount ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; background: #f8fafc; border-radius: var(--radius);">
                <span style="color: var(--text-muted); font-weight: 500;">Suggestions</span>
                <span class="badge success" style="font-size: 0.9rem;"><?= $suggestionCount ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; background: #f8fafc; border-radius: var(--radius);">
                <span style="color: var(--text-muted); font-weight: 500;">Published Projects</span>
                <span class="badge primary" style="font-size: 0.9rem;"><?= $projectCount ?></span>
            </div>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="card" style="grid-column: span 1;">
        <h2 style="font-size: 1.4rem; margin-bottom: 24px; border-bottom: 2px solid var(--border); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            Quick Management
        </h2>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <a class="button" href="manage_complaints.php">Review Complaints</a>
            <a class="button" style="background: #dc2626;" href="manage_crime.php">Review Crime Reports</a>
            <a class="button outline" style="color: var(--primary); border-color: var(--primary);" href="manage_suggestions.php">Browse Suggestions</a>
            <a class="button" style="background: var(--secondary);" href="manage_projects.php">Manage Development Projects</a>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="card" style="grid-column: span 2;">
        <h2 style="font-size: 1.4rem; margin-bottom: 20px; border-bottom: 2px solid var(--border); padding-bottom: 12px;">Recent Complaints</h2>
        <?php if ($recentComplaints): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Citizen</th><th>Subject</th><th>Status</th><th>Last Date</th><th style="text-align: right;">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentComplaints as $complaint): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($complaint['citizen_name']) ?></td>
                                <td><?= htmlspecialchars($complaint['subject']) ?></td>
                                <td>
                                    <span class="badge <?= strtolower($complaint['status_name']) === 'resolved' ? 'success' : 'primary' ?>">
                                        <?= htmlspecialchars($complaint['status_name'] ?: 'New') ?>
                                    </span>
                                </td>
                                <td class="text-muted text-sm"><?= date('M d, Y', strtotime($complaint['created_at'])) ?></td>
                                <td style="text-align: right;">
                                    <a href="manage_complaints.php?id=<?= $complaint['id'] ?>" class="read-more">View Details &rarr;</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="padding: 32px; text-align: center; background: #f8fafc; border-radius: var(--radius); border: 1px dashed var(--border);">
                <p class="text-muted">No complaints received yet for your constituency.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>