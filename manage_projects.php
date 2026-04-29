<?php
require_once 'includes/db.php';
requireRole('mp');
$mpId = $_SESSION['user']['id'];
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
$projects = $mysqli->query('SELECT title, description, status, created_at FROM mp_projects WHERE mp_id = ' . intval($mpId) . ' ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Manage Projects</h1>
    <?php if ($success): ?><div class="alert" style="background:#d1fae5;color:#064e3b;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="manage_projects.php">
        <label for="title">Project title</label>
        <input type="text" id="title" name="title" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" required></textarea>

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="Planned">Planned</option>
            <option value="In progress">In progress</option>
            <option value="Completed">Completed</option>
        </select>

        <button type="submit">Publish Project</button>
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
            </div>
            <hr>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No projects published yet.</p>
    <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>