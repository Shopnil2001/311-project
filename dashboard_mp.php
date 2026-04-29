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

$recentComplaints = [];
$stmt = $mysqli->prepare('SELECT complaints.id, complaints.subject, complaint_status.name AS status_name, complaints.response, citizens.name AS citizen_name, complaints.created_at FROM complaints JOIN citizens ON complaints.citizen_id = citizens.id JOIN sectors ON citizens.sector_id = sectors.id LEFT JOIN complaint_status ON complaints.status_id = complaint_status.id WHERE sectors.mp_id = ? ORDER BY complaints.created_at DESC LIMIT 6');
$stmt->bind_param('i', $mpId);
$stmt->execute();
$recentComplaints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<section class="hero">
    <h1>MP Dashboard</h1>
    <p>Manage constituency issues, respond to complaints, and publish project updates.</p>
</section>
<section class="grid">
    <div class="card">
        <h2>Summary</h2>
        <ul>
            <li>Registered citizens: <?= $citizenCount ?></li>
            <li>Complaints submitted: <?= $complaintCount ?></li>
            <li>Crime reports: <?= $crimeCount ?></li>
            <li>Projects published: <?= $projectCount ?></li>
        </ul>
    </div>
    <div class="card">
        <h2>Quick actions</h2>
        <p><a class="button" href="manage_complaints.php">Review Complaints</a></p>
        <p><a class="button" href="manage_projects.php">Add Projects</a></p>
    </div>
    <div class="card">
        <h2>Recent complaints</h2>
        <?php if ($recentComplaints): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Citizen</th><th>Subject</th><th>Status</th><th>Response</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentComplaints as $complaint): ?>
                            <tr>
                                <td><?= htmlspecialchars($complaint['citizen_name']) ?></td>
                                <td><?= htmlspecialchars($complaint['subject']) ?></td>
                                <td><?= htmlspecialchars($complaint['status_name'] ?: 'New') ?></td>
                                <td><?= htmlspecialchars($complaint['response'] ?: 'No response yet') ?></td>
                                <td><?= htmlspecialchars($complaint['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No complaints yet.</p>
        <?php endif; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>