<?php
require_once 'includes/db.php';
requireRole('mp');
$mpId = $_SESSION['user']['id'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaintId = intval($_POST['complaint_id'] ?? 0);
    $statusId = intval($_POST['status_id'] ?? 0);
    $response = trim($_POST['response'] ?? '');
    if ($complaintId > 0 && $statusId > 0) {
        $update = $mysqli->prepare('UPDATE complaints SET status_id = ?, response = ? WHERE id = ?');
        $update->bind_param('isi', $statusId, $response, $complaintId);
        if ($update->execute()) {
            header('Location: manage_complaints.php?updated=1');
            exit;
        }
        $error = 'Unable to update complaint status.';
    } else {
        $error = 'Please choose a complaint and status.';
    }
}
$complaints = $mysqli->query("SELECT complaints.id, citizens.name AS citizen_name, complaint_categories.name AS category_name, complaints.subject, complaints.message, complaint_status.name AS status_name, complaints.response, complaints.created_at FROM complaints JOIN citizens ON complaints.citizen_id = citizens.id JOIN sectors ON citizens.sector_id = sectors.id LEFT JOIN complaint_categories ON complaints.category_id = complaint_categories.id LEFT JOIN complaint_status ON complaints.status_id = complaint_status.id WHERE sectors.mp_id = $mpId ORDER BY complaints.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$statuses = $mysqli->query('SELECT id, name FROM complaint_status ORDER BY id')->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Manage Complaints</h1>
    <?php if (!empty($_GET['updated'])): ?><div class="alert" style="background:#d1fae5;color:#064e3b;">Complaint updated.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Citizen</th><th>Subject</th><th>Category</th><th>Status</th><th>Response</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $complaint): ?>
                    <tr>
                        <td><?= htmlspecialchars($complaint['citizen_name']) ?></td>
                        <td><?= htmlspecialchars($complaint['subject']) ?></td>
                        <td><?= htmlspecialchars($complaint['category_name']) ?></td>
                        <td><?= htmlspecialchars($complaint['status_name'] ?: 'New') ?></td>
                        <td><?= htmlspecialchars($complaint['response'] ?: 'No response yet') ?></td>
                        <td><?= htmlspecialchars($complaint['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <form method="post" action="manage_complaints.php">
        <label for="complaint_id">Select complaint</label>
        <select id="complaint_id" name="complaint_id" required>
            <option value="">Choose one</option>
            <?php foreach ($complaints as $complaint): ?>
                <option value="<?= $complaint['id'] ?>"><?= htmlspecialchars($complaint['subject']) ?> (<?= htmlspecialchars($complaint['citizen_name']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <label for="status_id">Status</label>
        <select id="status_id" name="status_id" required>
            <option value="">Choose status</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= $status['id'] ?>"><?= htmlspecialchars($status['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="response">Response message</label>
        <textarea id="response" name="response"></textarea>
        <button type="submit">Update Complaint</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>