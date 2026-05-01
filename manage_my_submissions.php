<?php
require_once 'includes/db.php';
requireRole('citizen');
$user = $_SESSION['user'];
$citizenId = $user['id'];
$error = '';
$success = '';
$editMode = null;
$editItem = null;

$complaintCategories = [];
$crimeCategories = [];
$statuses = [];

$categoryResult = $mysqli->query('SELECT id, name FROM complaint_categories ORDER BY name');
while ($row = $categoryResult->fetch_assoc()) {
    $complaintCategories[] = $row;
}
$crimeCategoryResult = $mysqli->query('SELECT id, name FROM crime_categories ORDER BY name');
while ($row = $crimeCategoryResult->fetch_assoc()) {
    $crimeCategories[] = $row;
}
$statusResult = $mysqli->query('SELECT id, name FROM complaint_status ORDER BY id');
while ($row = $statusResult->fetch_assoc()) {
    $statuses[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_complaint'])) {
        $complaintId = intval($_POST['complaint_id'] ?? 0);
        if ($complaintId > 0) {
            $stmt = $mysqli->prepare('DELETE FROM complaints WHERE id = ? AND citizen_id = ?');
            $stmt->bind_param('ii', $complaintId, $citizenId);
            if ($stmt->execute()) {
                $success = 'Complaint deleted successfully.';
            } else {
                $error = 'Unable to delete complaint.';
            }
        }
    } elseif (isset($_POST['update_complaint'])) {
        $complaintId = intval($_POST['complaint_id'] ?? 0);
        $categoryId = intval($_POST['category_id'] ?? 0);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $statusId = intval($_POST['status_id'] ?? 0);
        if ($complaintId > 0 && $categoryId > 0 && $subject !== '' && $message !== '' && $statusId > 0) {
            $stmt = $mysqli->prepare('UPDATE complaints SET category_id = ?, subject = ?, message = ?, status_id = ? WHERE id = ? AND citizen_id = ?');
            $stmt->bind_param('issiii', $categoryId, $subject, $message, $statusId, $complaintId, $citizenId);
            if ($stmt->execute()) {
                $success = 'Complaint updated successfully.';
            } else {
                $error = 'Unable to update complaint.';
            }
        } else {
            $error = 'Please complete all complaint fields.';
        }
    } elseif (isset($_POST['delete_suggestion'])) {
        $suggestionId = intval($_POST['suggestion_id'] ?? 0);
        if ($suggestionId > 0) {
            $stmt = $mysqli->prepare('DELETE FROM suggestions WHERE id = ? AND citizen_id = ?');
            $stmt->bind_param('ii', $suggestionId, $citizenId);
            if ($stmt->execute()) {
                $success = 'Suggestion deleted successfully.';
            } else {
                $error = 'Unable to delete suggestion.';
            }
        }
    } elseif (isset($_POST['update_suggestion'])) {
        $suggestionId = intval($_POST['suggestion_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($suggestionId > 0 && $title !== '' && $description !== '') {
            $stmt = $mysqli->prepare('UPDATE suggestions SET title = ?, description = ? WHERE id = ? AND citizen_id = ?');
            $stmt->bind_param('ssii', $title, $description, $suggestionId, $citizenId);
            if ($stmt->execute()) {
                $success = 'Suggestion updated successfully.';
            } else {
                $error = 'Unable to update suggestion.';
            }
        } else {
            $error = 'Please complete all suggestion fields.';
        }
    } elseif (isset($_POST['delete_report'])) {
        $reportId = intval($_POST['report_id'] ?? 0);
        if ($reportId > 0) {
            $stmt = $mysqli->prepare('DELETE FROM crime_reports WHERE id = ? AND citizen_id = ?');
            $stmt->bind_param('ii', $reportId, $citizenId);
            if ($stmt->execute()) {
                $success = 'Crime report deleted successfully.';
            } else {
                $error = 'Unable to delete report.';
            }
        }
    } elseif (isset($_POST['update_report'])) {
        $reportId = intval($_POST['report_id'] ?? 0);
        $categoryId = intval($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $isAnonymous = isset($_POST['anonymous']) ? 1 : 0;
        if ($reportId > 0 && $categoryId > 0 && $description !== '') {
            $stmt = $mysqli->prepare('UPDATE crime_reports SET category_id = ?, description = ?, is_anonymous = ? WHERE id = ? AND citizen_id = ?');
            $stmt->bind_param('iisii', $categoryId, $description, $isAnonymous, $reportId, $citizenId);
            if ($stmt->execute()) {
                $success = 'Crime report updated successfully.';
            } else {
                $error = 'Unable to update report.';
            }
        } else {
            $error = 'Please complete all report fields.';
        }
    }
}

if (isset($_GET['edit_type'], $_GET['id'])) {
    $editType = $_GET['edit_type'];
    $editId = intval($_GET['id']);
    if ($editType === 'complaint') {
        $stmt = $mysqli->prepare('SELECT id, category_id, subject, message, status_id FROM complaints WHERE id = ? AND citizen_id = ?');
        $stmt->bind_param('ii', $editId, $citizenId);
        $stmt->execute();
        $editItem = $stmt->get_result()->fetch_assoc();
        $editMode = 'complaint';
    } elseif ($editType === 'suggestion') {
        $stmt = $mysqli->prepare('SELECT id, title, description FROM suggestions WHERE id = ? AND citizen_id = ?');
        $stmt->bind_param('ii', $editId, $citizenId);
        $stmt->execute();
        $editItem = $stmt->get_result()->fetch_assoc();
        $editMode = 'suggestion';
    } elseif ($editType === 'report') {
        $stmt = $mysqli->prepare('SELECT id, category_id, description, is_anonymous FROM crime_reports WHERE id = ? AND citizen_id = ?');
        $stmt->bind_param('ii', $editId, $citizenId);
        $stmt->execute();
        $editItem = $stmt->get_result()->fetch_assoc();
        $editMode = 'report';
    }
}

$complaints = $mysqli->query("SELECT complaints.id, complaint_categories.name AS category_name, complaints.subject, complaints.message, complaint_status.name AS status_name, complaints.created_at FROM complaints LEFT JOIN complaint_categories ON complaints.category_id = complaint_categories.id LEFT JOIN complaint_status ON complaints.status_id = complaint_status.id WHERE complaints.citizen_id = $citizenId ORDER BY complaints.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$suggestions = $mysqli->query("SELECT id, title, description, created_at FROM suggestions WHERE citizen_id = $citizenId ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$reports = $mysqli->query("SELECT crime_reports.id, crime_categories.name AS category_name, crime_reports.description, crime_reports.is_anonymous, crime_reports.media_path, crime_reports.media_type, crime_reports.is_reviewed, crime_reports.created_at FROM crime_reports LEFT JOIN crime_categories ON crime_reports.category_id = crime_categories.id WHERE crime_reports.citizen_id = $citizenId ORDER BY crime_reports.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>

<div class="back-btn-container">
    <a href="dashboard_citizen.php" class="button outline back-btn">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Back to Dashboard
    </a>
</div>

<div class="mb-4">
    <h1 class="section-title">My Submissions</h1>
    <?php if ($success): ?><div class="alert success mb-4" style="background:#dcfce7; color:#15803d; border-color:#bbf7d0;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert mb-4"><?= htmlspecialchars($error) ?></div><?php endif; ?>
</div>

<?php if ($editMode && $editItem): ?>
<div class="card mb-4" style="max-width: 700px; margin: 0 auto 60px;">
    <h2 style="margin-bottom: 30px;">Edit <?= ucfirst($editMode) ?></h2>
    <form method="post" action="manage_my_submissions.php">
        <?php if ($editMode === 'complaint'): ?>
            <input type="hidden" name="complaint_id" value="<?= $editItem['id'] ?>">
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($complaintCategories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $editItem['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required value="<?= htmlspecialchars($editItem['subject']) ?>">
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" required><?= htmlspecialchars($editItem['message']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="status_id">Status</label>
                <select id="status_id" name="status_id" required>
                    <?php foreach ($statuses as $stat): ?>
                        <option value="<?= $stat['id'] ?>" <?= $stat['id'] == $editItem['status_id'] ? 'selected' : '' ?>><?= htmlspecialchars($stat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="update_complaint">Update Complaint</button>
        <?php elseif ($editMode === 'suggestion'): ?>
            <input type="hidden" name="suggestion_id" value="<?= $editItem['id'] ?>">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required value="<?= htmlspecialchars($editItem['title']) ?>">
            </div>
            <div class="form-group">
                <label for="description">Details</label>
                <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($editItem['description']) ?></textarea>
            </div>
            <button type="submit" name="update_suggestion">Update Suggestion</button>
        <?php elseif ($editMode === 'report'): ?>
            <input type="hidden" name="report_id" value="<?= $editItem['id'] ?>">
            <div class="form-group">
                <label for="category_id">Crime Category</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($crimeCategories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $editItem['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($editItem['description']) ?></textarea>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="anonymous" value="1" <?= $editItem['is_anonymous'] ? 'checked' : '' ?> style="width: auto;">
                <label style="margin: 0;">Submit anonymously</label>
            </div>
            <button type="submit" name="update_report">Update Report</button>
        <?php endif; ?>
        <a href="manage_my_submissions.php" class="button outline mt-2" style="width: 100%;">Cancel Edit</a>
    </form>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 20px; flex-wrap: wrap;">
        <h2 style="margin: 0; font-size: 1.5rem;"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 12px;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 1 1-7.6-10.4 8.38 8.38 0 0 1 3.9.9"></path><polyline points="16 2 16 6 20 6"></polyline><polyline points="12 14 22 4"></polyline></svg>My Complaints</h2>
        <a href="submit_complaint.php" class="button" style="width: auto; padding: 10px 24px; font-size: 0.9rem;">+ New Complaint</a>
    </div>
    <?php if ($complaints): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Subject</th><th>Category</th><th>Status</th><th>Date</th><th style="text-align: right;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $c): ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--primary-dark);"><?= htmlspecialchars($c['subject']) ?></td>
                            <td><span class="badge" style="background:#f1f5f9; color:#64748b;"><?= htmlspecialchars($c['category_name']) ?></span></td>
                            <td><span class="badge <?= strtolower($c['status_name']) === 'resolved' ? 'success' : 'primary' ?>"><?= htmlspecialchars($c['status_name'] ?: 'New') ?></span></td>
                            <td class="text-muted"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            <td style="text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="manage_my_submissions.php?edit_type=complaint&id=<?= $c['id'] ?>" class="button outline" style="padding: 6px 12px; font-size: 0.8rem; border-color: var(--border);">Edit</a>
                                <form method="post" onsubmit="return confirm('Delete this complaint?');">
                                    <input type="hidden" name="complaint_id" value="<?= $c['id'] ?>">
                                    <button type="submit" name="delete_complaint" class="button" style="padding: 6px 12px; font-size: 0.8rem; background: var(--accent); box-shadow: none;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <h3>No complaints filed</h3>
            <p>You haven't submitted any complaints yet. Start by voicing your concerns.</p>
        </div>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 20px; flex-wrap: wrap;">
        <h2 style="margin: 0; font-size: 1.5rem;"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 12px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>My Suggestions</h2>
        <a href="submit_suggestion.php" class="button" style="width: auto; padding: 10px 24px; font-size: 0.9rem;">+ New Suggestion</a>
    </div>
    <?php if ($suggestions): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Title</th><th>Date</th><th style="text-align: right;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($suggestions as $s): ?>
                        <tr>
                            <td style="font-weight: 700;"><?= htmlspecialchars($s['title']) ?></td>
                            <td class="text-muted"><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                            <td style="text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="manage_my_submissions.php?edit_type=suggestion&id=<?= $s['id'] ?>" class="button outline" style="padding: 6px 12px; font-size: 0.8rem; border-color: var(--border);">Edit</a>
                                <form method="post" onsubmit="return confirm('Delete this suggestion?');">
                                    <input type="hidden" name="suggestion_id" value="<?= $s['id'] ?>">
                                    <button type="submit" name="delete_suggestion" class="button" style="padding: 6px 12px; font-size: 0.8rem; background: var(--accent); box-shadow: none;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <h3>No suggestions found</h3>
            <p>Your ideas can help improve our community. Submit your first suggestion today!</p>
        </div>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 20px; flex-wrap: wrap;">
        <h2 style="margin: 0; font-size: 1.5rem;"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 12px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>Crime Reports</h2>
        <a href="submit_crime.php" class="button" style="width: auto; padding: 10px 24px; font-size: 0.9rem; background: var(--accent);">+ Report Crime</a>
    </div>
    <?php if ($reports): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Category</th><th>Status</th><th>Date</th><th style="text-align: right;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $r): ?>
                        <tr>
                            <td style="font-weight: 700;"><?= htmlspecialchars($r['category_name']) ?> <small class="text-muted" style="display:block; font-weight:400;"><?= $r['is_anonymous'] ? '(Anonymous)' : '' ?></small></td>
                            <td><span class="badge <?= $r['is_reviewed'] ? 'success' : 'primary' ?>"><?= $r['is_reviewed'] ? 'Reviewed' : 'Pending' ?></span></td>
                            <td class="text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                            <td style="text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="manage_my_submissions.php?edit_type=report&id=<?= $r['id'] ?>" class="button outline" style="padding: 6px 12px; font-size: 0.8rem; border-color: var(--border);">Edit</a>
                                <form method="post" onsubmit="return confirm('Delete this report?');">
                                    <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="delete_report" class="button" style="padding: 6px 12px; font-size: 0.8rem; background: var(--accent); box-shadow: none;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            <h3>No crime reports</h3>
            <p>You haven't reported any incidents. Help keep our constituency safe by reporting crimes.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>