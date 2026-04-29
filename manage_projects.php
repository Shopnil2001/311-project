<?php
require_once 'includes/db.php';
requireRole('mp');
$mpId = $_SESSION['user']['id'];
$error = '';
$success = '';
$editProject = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_project'])) {
        $projectId = intval($_POST['project_id'] ?? 0);
        if ($projectId > 0) {
            $delete = $mysqli->prepare('DELETE FROM mp_projects WHERE id = ? AND mp_id = ?');
            $delete->bind_param('ii', $projectId, $mpId);
            if ($delete->execute()) {
                header('Location: manage_projects.php?deleted=1');
                exit;
            }
            $error = 'Unable to delete project.';
        }
    } elseif (isset($_POST['update_project'])) {
        $projectId = intval($_POST['project_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? 'Planned');
        if ($projectId > 0 && $title !== '' && $description !== '') {
            $update = $mysqli->prepare('UPDATE mp_projects SET title = ?, description = ?, status = ? WHERE id = ? AND mp_id = ?');
            $update->bind_param('sssii', $title, $description, $status, $projectId, $mpId);
            if ($update->execute()) {
                header('Location: manage_projects.php?updated=1');
                exit;
            }
            $error = 'Unable to update project.';
        } else {
            $error = 'Please enter a title and description.';
        }
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? 'Planned');
        if ($title === '' || $description === '') {
            $error = 'Please enter a title and description.';
        } else {
            $stmt = $mysqli->prepare('INSERT INTO mp_projects (mp_id, title, description, status, created_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->bind_param('isss', $mpId, $title, $description, $status);
            if ($stmt->execute()) {
                $success = 'Project added successfully.';
            } else {
                $error = 'Unable to create project. Please try again.';
            }
        }
    }
}
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editStmt = $mysqli->prepare('SELECT id, title, description, status FROM mp_projects WHERE id = ? AND mp_id = ?');
    $editStmt->bind_param('ii', $editId, $mpId);
    $editStmt->execute();
    $editProject = $editStmt->get_result()->fetch_assoc();
}
$projects = $mysqli->query('SELECT id, title, description, status, created_at FROM mp_projects WHERE mp_id = ' . intval($mpId) . ' ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Manage Projects</h1>
    <?php if ($success): ?><div class="alert" style="background:#d1fae5;color:#064e3b;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="manage_projects.php">
        <?php if ($editProject): ?>
            <input type="hidden" name="project_id" value="<?= htmlspecialchars($editProject['id']) ?>">
            <h2>Edit Project</h2>
        <?php else: ?>
            <h2>Add New Project</h2>
        <?php endif; ?>
        <label for="title">Project title</label>
        <input type="text" id="title" name="title" required value="<?= htmlspecialchars($editProject['title'] ?? '') ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description" required><?= htmlspecialchars($editProject['description'] ?? '') ?></textarea>

        <label for="status">Status</label>
        <select id="status" name="status">
            <?php $statuses = ['Planned', 'In progress', 'Completed']; ?>
            <?php foreach ($statuses as $statusValue): ?>
                <option value="<?= $statusValue ?>" <?= isset($editProject['status']) && $editProject['status'] === $statusValue ? 'selected' : '' ?>><?= $statusValue ?></option>
            <?php endforeach; ?>
        </select>

        <?php if ($editProject): ?>
            <button type="submit" name="update_project">Update Project</button>
            <button type="submit" name="delete_project" value="1" style="margin-left:10px;background:#dc2626;">Delete Project</button>
            <a class="button" href="manage_projects.php" style="background:#6b7280;margin-left:10px;">Cancel</a>
        <?php else: ?>
            <button type="submit">Publish Project</button>
        <?php endif; ?>
    </form>
</section>
<section class="card">
    <h2>Your Projects</h2>
    <?php if ($projects): ?>
        <?php foreach ($projects as $project): ?>
            <div>
                <strong><?= htmlspecialchars($project['title']) ?></strong>
                <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                <span class="status-pill"><?= htmlspecialchars($project['status']) ?></span>
                <small><?= htmlspecialchars($project['created_at']) ?></small>
                <div style="margin-top:12px;">
                    <a class="button" href="manage_projects.php?edit=<?= $project['id'] ?>" style="background:#2563eb;">Edit</a>
                    <form method="post" action="manage_projects.php" style="display:inline-block;margin-left:10px;">
                        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                        <button class="button" type="submit" name="delete_project" value="1" style="background:#dc2626;">Delete</button>
                    </form>
                </div>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No projects published yet.</p>
    <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>