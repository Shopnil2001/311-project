<?php
require_once 'includes/db.php';
requireRole('citizen');
$user = $_SESSION['user'];
$error = '';
$success = '';
$categories = [];
$result = $mysqli->query('SELECT id, name FROM complaint_categories ORDER BY name');
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($category_id <= 0 || $subject === '' || $message === '') {
        $error = 'Please complete all fields.';
    } else {
        $stmt = $mysqli->prepare('INSERT INTO complaints (citizen_id, category_id, subject, message, status_id, created_at) VALUES (?, ?, ?, ?, 1, NOW())');
        $stmt->bind_param('iiss', $user['id'], $category_id, $subject, $message);
        if ($stmt->execute()) {
            $success = 'Complaint submitted successfully.';
        } else {
            $error = 'Unable to submit complaint. Please try again.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Submit Complaint</h1>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert" style="background:#d1fae5;color:#064e3b;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="post" action="submit_complaint.php">
        <label for="category_id">Complaint category</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" required>

        <label for="message">Message</label>
        <textarea id="message" name="message" required></textarea>

        <button type="submit">Submit Complaint</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>