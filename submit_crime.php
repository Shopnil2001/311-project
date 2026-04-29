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
                $success = 'Crime report submitted successfully.';
            } else {
                $error = 'Unable to submit report. Please try again.';
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Report Crime</h1>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert" style="background:#d1fae5;color:#064e3b;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="post" action="submit_crime.php" enctype="multipart/form-data">
        <label for="category_id">Crime category</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select a category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="description">Description</label>
        <textarea id="description" name="description" required></textarea>

        <label for="media">Attach image or video (optional)</label>
        <input type="file" id="media" name="media" accept="image/*,video/mp4">

        <label><input type="checkbox" name="anonymous" value="1"> Submit anonymously</label>

        <button type="submit">Send Report</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>
