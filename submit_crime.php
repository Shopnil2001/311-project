<?php
require_once 'includes/db.php';
requireRole('citizen');
$error = '';
$success = '';
$categories = [];
$result = $mysqli->query('SELECT id, name FROM crime_categories ORDER BY name');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $is_anonymous = isset($_POST['anonymous']) ? 1 : 0;
    $mediaPath = null;
    $mediaType = null;
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'video/mp4' => 'mp4'
    ];

    if ($category_id <= 0 || $description === '') {
        $error = 'Please choose a category and enter the incident description.';
    } else {
        if (!empty($_FILES['media']['name'])) {
            $file = $_FILES['media'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                if (!isset($allowedTypes[$file['type']])) {
                    $error = 'Only JPG, PNG, GIF images and MP4 video are allowed.';
                } elseif ($file['size'] > 20 * 1024 * 1024) {
                    $error = 'Media file must be 20MB or smaller.';
                } else {
                    if (!is_dir('uploads')) {
                        mkdir('uploads', 0755, true);
                    }
                    $ext = $allowedTypes[$file['type']];
                    $filename = uniqid('crime_', true) . '.' . $ext;
                    $mediaPath = 'uploads/' . $filename;
                    if (move_uploaded_file($file['tmp_name'], $mediaPath)) {
                        $mediaType = strpos($file['type'], 'video/') === 0 ? 'video' : 'image';
                    } else {
                        $error = 'Unable to save uploaded media.';
                    }
                }
            } else {
                $error = 'Error uploading media file.';
            }
        }

        if ($error === '') {
            $stmt = $mysqli->prepare('INSERT INTO crime_reports (citizen_id, category_id, description, is_anonymous, media_path, media_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmt->bind_param('iiisss', $_SESSION['user']['id'], $category_id, $description, $is_anonymous, $mediaPath, $mediaType);
            if ($stmt->execute()) {
                $success = 'Report submitted securely. Thank you for your cooperation.';
            } else {
                $error = 'Unable to submit report. Please try again.';
            }
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
        <h1 class="section-title" style="background: linear-gradient(to right, #f43f5e, #e11d48); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Report an Incident</h1>
        <p class="text-muted" style="margin-top: -20px; font-size: 1.1rem;">Help maintain safety in our constituency. Reports can be submitted anonymously.</p>
    </div>

    <?php if ($error): ?><div class="alert mb-4" style="background:#fee2e2; color:#b91c1c; padding: 15px; border-radius: 12px; border: 1px solid #fecaca;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert success mb-4" style="background:#dcfce7; color:#15803d; padding: 15px; border-radius: 12px; border: 1px solid #bbf7d0;"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="card" style="padding: 40px; border-top: 4px solid var(--accent);">
        <div style="background: #fff1f2; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #fecaca; display: flex; gap: 15px;">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#e11d48" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            <p style="font-size: 0.95rem; color: #9f1239; line-height: 1.5; font-weight: 600;">
                Confidentiality Notice: Your identity will be protected. If you choose to submit anonymously, your name and profile will not be linked to this report.
            </p>
        </div>

        <form method="post" action="submit_crime.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category_id">Crime Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select the type of incident</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Incident Details</label>
                <textarea id="description" name="description" rows="8" required placeholder="Provide a clear description of what happened, location, and time..."></textarea>
            </div>

            <div class="form-group">
                <label for="media">Evidence (Images or Videos)</label>
                <div style="border: 2px dashed var(--border); padding: 30px; border-radius: 12px; text-align: center; background: #f8fafc; transition: all 0.3s ease;" id="drop-zone">
                    <input type="file" id="media" name="media" accept="image/*,video/mp4" style="display: none;">
                    <label for="media" style="cursor: pointer; margin: 0;">
                        <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="var(--text-muted)" stroke-width="1.5" style="margin-bottom: 15px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        <p style="font-weight: 700; color: var(--text-main);">Click to upload or drag & drop</p>
                        <p class="text-sm text-muted">JPG, PNG, GIF or MP4 (Max 20MB)</p>
                    </label>
                </div>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 15px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid var(--border); margin-top: 20px;">
                <input type="checkbox" name="anonymous" id="anonymous" value="1" style="width: 20px; height: 20px; cursor: pointer;">
                <label for="anonymous" style="margin: 0; cursor: pointer; font-size: 1.1rem;">Submit this report anonymously</label>
            </div>

            <button type="submit" style="width: 100%; font-size: 1.1rem; padding: 16px; background: var(--accent); margin-top: 30px; box-shadow: 0 4px 15px rgba(244, 63, 94, 0.3);">Submit Secure Report</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
