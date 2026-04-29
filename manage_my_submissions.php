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
<section class="card">
    <h1>My Submissions</h1>
    <?php if ($success): ?><div class="alert" style="background:#d1fae5;color:#064e3b;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
</section>
<?php if ($editMode === 'complaint' && $editItem): ?>
<section class="card">
    <h2>Edit Complaint</h2>
    <form method="post" action="manage_my_submissions.php">
        <input type="hidden" name="complaint_id" value="<?= htmlspecialchars($editItem['id']) ?>">
        <label for="category_id">Complaint category</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select category</option>
            <?php foreach ($complaintCategories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= $category['id'] == $editItem['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" required value="<?= htmlspecialchars($editItem['subject']) ?>">
        <label for="message">Message</label>
        <textarea id="message" name="message" required><?= htmlspecialchars($editItem['message']) ?></textarea>
        <label for="status_id">Status</label>
        <select id="status_id" name="status_id" required>
            <option value="">Choose status</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= $status['id'] ?>" <?= $status['id'] == $editItem['status_id'] ? 'selected' : '' ?>><?= htmlspecialchars($status['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="update_complaint">Save Changes</button>
        <a class="button" href="manage_my_submissions.php" style="background:#6b7280;">Cancel</a>
    </form>
</section>
<?php elseif ($editMode === 'suggestion' && $editItem): ?>
<section class="card">
    <h2>Edit Suggestion</h2>
    <form method="post" action="manage_my_submissions.php">
        <input type="hidden" name="suggestion_id" value="<?= htmlspecialchars($editItem['id']) ?>">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required value="<?= htmlspecialchars($editItem['title']) ?>">
        <label for="description">Suggestion details</label>
        <textarea id="description" name="description" required><?= htmlspecialchars($editItem['description']) ?></textarea>
        <button type="submit" name="update_suggestion">Save Changes</button>
        <a class="button" href="manage_my_submissions.php" style="background:#6b7280;">Cancel</a>
    </form>
</section>
<?php elseif ($editMode === 'report' && $editItem): ?>
<section class="card">
    <h2>Edit Crime Report</h2>
    <form method="post" action="manage_my_submissions.php">
        <input type="hidden" name="report_id" value="<?= htmlspecialchars($editItem['id']) ?>">
        <label for="category_id">Crime category</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select category</option>
            <?php foreach ($crimeCategories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= $category['id'] == $editItem['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="description">Description</label>
        <textarea id="description" name="description" required><?= htmlspecialchars($editItem['description']) ?></textarea>
        <label><input type="checkbox" name="anonymous" value="1" <?= $editItem['is_anonymous'] ? 'checked' : '' ?>> Submit anonymously</label>
        <button type="submit" name="update_report">Save Changes</button>
        <a class="button" href="manage_my_submissions.php" style="background:#6b7280;">Cancel</a>
    </form>
</section>
<?php endif; ?>
<section class="card">
    <h2>My Complaints</h2>
    <?php if ($complaints): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Subject</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                        <tr>
                            <td><?= htmlspecialchars($complaint['subject']) ?></td>
                            <td><?= htmlspecialchars($complaint['category_name']) ?></td>
                            <td><?= htmlspecialchars($complaint['status_name'] ?: 'New') ?></td>
                            <td><?= htmlspecialchars($complaint['created_at']) ?></td>
                            <td>
                                <a class="button" href="manage_my_submissions.php?edit_type=complaint&id=<?= $complaint['id'] ?>">Edit</a>
                                <form method="post" action="manage_my_submissions.php" style="display:inline-block; margin-left:8px;">
                                    <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
                                    <button class="button" type="submit" name="delete_complaint" value="1" style="background:#dc2626;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No complaints submitted yet.</p>
    <?php endif; ?>
</section>
<section class="card">
    <h2>My Suggestions</h2>
    <?php if ($suggestions): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Title</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($suggestions as $suggestion): ?>
                        <tr>
                            <td><?= htmlspecialchars($suggestion['title']) ?></td>
                            <td><?= htmlspecialchars($suggestion['created_at']) ?></td>
                            <td>
                                <a class="button" href="manage_my_submissions.php?edit_type=suggestion&id=<?= $suggestion['id'] ?>">Edit</a>
                                <form method="post" action="manage_my_submissions.php" style="display:inline-block; margin-left:8px;">
                                    <input type="hidden" name="suggestion_id" value="<?= $suggestion['id'] ?>">
                                    <button class="button" type="submit" name="delete_suggestion" value="1" style="background:#dc2626;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No suggestions submitted yet.</p>
    <?php endif; ?>
</section>
<section class="card">
    <h2>My Crime Reports</h2>
    <?php if ($reports): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Category</th><th>Description</th><th>Media</th><th>Reviewed</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?= htmlspecialchars($report['category_name']) ?></td>
                            <td><?= nl2br(htmlspecialchars($report['description'])) ?></td>
                            <td>
                                <?php if ($report['media_path']): ?>
                                    <?php if ($report['media_type'] === 'video'): ?>
                                        <video width="160" controls><source src="<?= htmlspecialchars($report['media_path']) ?>" type="video/mp4"></video>
                                    <?php else: ?>
                                        <img src="<?= htmlspecialchars($report['media_path']) ?>" alt="Crime media" style="max-width:160px; max-height:120px;">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span>No media</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $report['is_reviewed'] ? 'Yes' : 'No' ?></td>
                            <td><?= htmlspecialchars($report['created_at']) ?></td>
                            <td>
                                <a class="button" href="manage_my_submissions.php?edit_type=report&id=<?= $report['id'] ?>">Edit</a>
                                <form method="post" action="manage_my_submissions.php" style="display:inline-block; margin-left:8px;">
                                    <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                    <button class="button" type="submit" name="delete_report" value="1" style="background:#dc2626;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No crime reports submitted yet.</p>
    <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>