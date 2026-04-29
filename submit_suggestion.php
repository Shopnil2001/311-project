<?php
require_once 'includes/db.php';
requireRole('citizen');
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($title === '' || $description === '') {
        $error = 'Please enter a title and description.';
    } else {
        $stmt = $mysqli->prepare('INSERT INTO suggestions (citizen_id, title, description, votes, created_at) VALUES (?, ?, ?, 0, NOW())');
        $stmt->bind_param('iss', $_SESSION['user']['id'], $title, $description);
        if ($stmt->execute()) {
            $success = 'Suggestion submitted successfully.';
        } else {
            $error = 'Unable to submit suggestion. Please try again.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Submit Suggestion</h1>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert" style="background:#d1fae5;color:#064e3b;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="post" action="submit_suggestion.php">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required>

        <label for="description">Suggestion details</label>
        <textarea id="description" name="description" required></textarea>

        <button type="submit">Submit Suggestion</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>