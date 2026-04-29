<?php
require_once 'includes/db.php';
requireRole('citizen');
$error = '';
$success = '';
$categories = [];
$result = $mysqli->query('SELECT id, name FROM crime_categories ORDER BY name');
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $is_anonymous = isset($_POST['anonymous']) ? 1 : 0;
    if ($category_id <= 0 || $description === '') {
        $error = 'Please choose a category and enter the incident description.';
    } else {
        $stmt = $mysqli->prepare('INSERT INTO crime_reports (citizen_id, category_id, description, is_anonymous, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('iisi', $_SESSION['user']['id'], $category_id, $description, $is_anonymous);
        if ($stmt->execute()) {
            $success = 'Crime report submitted.';
        } else {
            $error = 'Unable to submit report. Please try again.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Report Crime</h1>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert" style="background:#d1fae5;color:#064e3b;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="post" action="submit_crime.php">
        <label for="category_id">Crime category</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select a category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="description">Description</label>
        <textarea id="description" name="description" required></textarea>

        <label><input type="checkbox" name="anonymous" value="1"> Submit anonymously</label>

        <button type="submit">Send Report</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>