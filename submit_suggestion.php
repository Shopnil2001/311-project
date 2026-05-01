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
            $success = 'Thank you! Your suggestion has been submitted for community review.';
        } else {
            $error = 'Unable to submit suggestion. Please try again.';
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
        <h1 class="section-title">Share an Idea</h1>
        <p class="text-muted" style="margin-top: -20px; font-size: 1.1rem;">Have a suggestion for our constituency? We'd love to hear it.</p>
    </div>

    <?php if ($error): ?><div class="alert mb-4" style="background:#fee2e2; color:#b91c1c; padding: 15px; border-radius: 12px; border: 1px solid #fecaca;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert success mb-4" style="background:#dcfce7; color:#15803d; padding: 15px; border-radius: 12px; border: 1px solid #bbf7d0;"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="card" style="padding: 40px; border-top: 4px solid var(--secondary);">
        <form method="post" action="submit_suggestion.php">
            <div class="form-group">
                <label for="title">Suggestion Title</label>
                <input type="text" id="title" name="title" required placeholder="What is your idea called?">
            </div>

            <div class="form-group">
                <label for="description">Proposal Details</label>
                <textarea id="description" name="description" rows="8" required placeholder="Describe your suggestion in detail. How will it benefit the community?"></textarea>
            </div>

            <div style="background: #ecfdf5; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #d1fae5;">
                <p style="font-size: 0.9rem; color: #065f46; line-height: 1.5; font-weight: 500;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Constructive suggestions are a key part of our growth. Your proposal will be visible to officials for review.
                </p>
            </div>

            <button type="submit" style="width: 100%; font-size: 1.1rem; padding: 16px; background: var(--secondary); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);">Submit Proposal</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>