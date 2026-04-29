<?php
require_once 'includes/db.php';
requireRole('mp');
$mpId = $_SESSION['user']['id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_report'])) {
        $reportId = intval($_POST['report_id'] ?? 0);
        if ($reportId > 0) {
            $delete = $mysqli->prepare('DELETE FROM crime_reports WHERE id = ?');
            $delete->bind_param('i', $reportId);
            if ($delete->execute()) {
                header('Location: manage_crime.php?deleted=1');
                exit;
            }
            $error = 'Unable to delete crime report.';
        }
    } elseif (isset($_POST['toggle_review'])) {
        $reportId = intval($_POST['report_id'] ?? 0);
        $reviewed = intval($_POST['reviewed'] ?? 0);
        if ($reportId > 0) {
            $stmt = $mysqli->prepare('UPDATE crime_reports SET is_reviewed = ?, reviewed_at = ? WHERE id = ?');
            $reviewedAt = $reviewed ? date('Y-m-d H:i:s') : null;
            $stmt->bind_param('isi', $reviewed, $reviewedAt, $reportId);
            if ($stmt->execute()) {
                header('Location: manage_crime.php?updated=1');
                exit;
            }
            $error = 'Unable to update report status.';
        }
    }
}

$reports = $mysqli->query("SELECT crime_reports.id, crime_reports.description, crime_reports.is_anonymous, crime_reports.media_path, crime_reports.media_type, crime_reports.is_reviewed, crime_reports.reviewed_at, crime_reports.created_at, crime_categories.name AS category_name, citizens.name AS citizen_name FROM crime_reports JOIN citizens ON crime_reports.citizen_id = citizens.id JOIN sectors ON citizens.sector_id = sectors.id LEFT JOIN crime_categories ON crime_reports.category_id = crime_categories.id WHERE sectors.mp_id = $mpId ORDER BY crime_reports.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Manage Crime Reports</h1>
    <?php if (!empty($_GET['updated'])): ?><div class="alert" style="background:#d1fae5;color:#064e3b;">Report updated.</div><?php endif; ?>
    <?php if (!empty($_GET['deleted'])): ?><div class="alert" style="background:#d1fae5;color:#064e3b;">Report deleted.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Citizen</th><th>Category</th><th>Description</th><th>Media</th><th>Reviewed</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?= $report['is_anonymous'] ? 'Anonymous' : htmlspecialchars($report['citizen_name']) ?></td>
                        <td><?= htmlspecialchars($report['category_name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($report['description'])) ?></td>
                        <td>
                            <?php if ($report['media_path']): ?>
                                <?php if ($report['media_type'] === 'video'): ?>
                                    <video width="180" controls><source src="<?= htmlspecialchars($report['media_path']) ?>" type="video/mp4">Your browser does not support video.</video>
                                <?php else: ?>
                                    <img src="<?= htmlspecialchars($report['media_path']) ?>" alt="Crime media" style="max-width:180px; max-height:120px; display:block;">
                                <?php endif; ?>
                            <?php else: ?>
                                <span>No media</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $report['is_reviewed'] ? 'Yes' : 'No' ?></td>
                        <td><?= htmlspecialchars($report['created_at']) ?></td>
                        <td>
                            <form method="post" action="manage_crime.php" style="display:inline-block; margin-right:8px;">
                                <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                <input type="hidden" name="reviewed" value="<?= $report['is_reviewed'] ? 0 : 1 ?>">
                                <button class="button" type="submit" name="toggle_review"><?= $report['is_reviewed'] ? 'Unmark' : 'Mark Reviewed' ?></button>
                            </form>
                            <form method="post" action="manage_crime.php" style="display:inline-block;">
                                <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                <button class="button" type="submit" name="delete_report" value="1" style="background:#dc2626;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include 'includes/footer.php'; ?>