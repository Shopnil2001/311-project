<?php
require_once 'includes/db.php';
requireRole('citizen');
$user = $_SESSION['user'];
$error = '';
$success = '';
$categories = [];
$result = $mysqli->query('SELECT id, name FROM complaint_categories ORDER BY name');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
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
            $success = 'Your complaint has been logged. We will review it shortly.';
        } else {
            $error = 'Unable to submit complaint. Please try again.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="back-btn-container">
    <a href="dashboard_citizen.php" class="button outline back-btn">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Back to Dashboard
    </a>
</div>

<div style="max-width: 800px; margin: 0 auto 100px;">
    <div class="mb-4">
        <h1 class="section-title">Submit a Complaint</h1>
        <p class="text-muted" style="margin-top: -20px; font-size: 1.1rem;">Help us identify and resolve issues in our constituency.</p>
    </div>

    <?php if ($error): ?><div class="alert mb-4" style="background:#fee2e2; color:#b91c1c; padding: 15px; border-radius: 12px; border: 1px solid #fecaca;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert success mb-4" style="background:#dcfce7; color:#15803d; padding: 15px; border-radius: 12px; border: 1px solid #bbf7d0;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    
    <div class="card" style="padding: 40px;">
        <form method="post" action="submit_complaint.php">
            <div class="form-group">
                <label for="category_id">Complaint Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select the relevant category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required placeholder="What is the main issue?">
            </div>

            <div class="form-group">
                <label for="message">Detailed Description</label>
                <textarea id="message" name="message" rows="8" required placeholder="Please provide as much detail as possible to help us understand the situation..."></textarea>
            </div>

            <div style="background: #f8fafc; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid var(--border);">
                <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.5;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    By submitting this form, you agree that the information provided is accurate. Our team will review your complaint and may contact you for further details.
                </p>
            </div>

            <button type="submit" style="width: 100%; font-size: 1.1rem; padding: 16px;">Log Complaint</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>