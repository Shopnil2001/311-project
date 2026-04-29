<?php
require_once 'includes/db.php';
requireRole('mp');
$mpId = $_SESSION['user']['id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_suggestion'])) {
        $suggestionId = intval($_POST['suggestion_id'] ?? 0);
        if ($suggestionId > 0) {
            $delete = $mysqli->prepare('DELETE FROM suggestions WHERE id = ?');
            $delete->bind_param('i', $suggestionId);
            if ($delete->execute()) {
                header('Location: manage_suggestions.php?deleted=1');
                exit;
            }
            $error = 'Unable to delete suggestion.';
        }
    } elseif (isset($_POST['toggle_review'])) {
        $suggestionId = intval($_POST['suggestion_id'] ?? 0);
        $reviewed = intval($_POST['reviewed'] ?? 0);
        if ($suggestionId > 0) {
            $stmt = $mysqli->prepare('UPDATE suggestions SET is_reviewed = ?, reviewed_at = ? WHERE id = ?');
            $reviewedAt = $reviewed ? date('Y-m-d H:i:s') : null;
            $stmt->bind_param('isi', $reviewed, $reviewedAt, $suggestionId);
            if ($stmt->execute()) {
                header('Location: manage_suggestions.php?updated=1');
                exit;
            }
            $error = 'Unable to update suggestion status.';
        }
    }
}

$suggestions = $mysqli->query("SELECT suggestions.id, suggestions.title, suggestions.description, suggestions.is_reviewed, suggestions.reviewed_at, citizens.name AS citizen_name, suggestions.created_at FROM suggestions JOIN citizens ON suggestions.citizen_id = citizens.id JOIN sectors ON citizens.sector_id = sectors.id WHERE sectors.mp_id = $mpId ORDER BY suggestions.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Manage Suggestions</h1>
    <?php if (!empty($_GET['updated'])): ?><div class="alert" style="background:#d1fae5;color:#064e3b;">Suggestion status updated.</div><?php endif; ?>
    <?php if (!empty($_GET['deleted'])): ?><div class="alert" style="background:#d1fae5;color:#064e3b;">Suggestion deleted.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Citizen</th><th>Title</th><th>Description</th><th>Reviewed</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($suggestions as $suggestion): ?>
                    <tr>
                        <td><?= htmlspecialchars($suggestion['citizen_name']) ?></td>
                        <td><?= htmlspecialchars($suggestion['title']) ?></td>
                        <td><?= nl2br(htmlspecialchars($suggestion['description'])) ?></td>
                        <td><?= $suggestion['is_reviewed'] ? 'Yes' : 'No' ?></td>
                        <td><?= htmlspecialchars($suggestion['created_at']) ?></td>
                        <td>
                            <form method="post" action="manage_suggestions.php" style="display:inline-block; margin-right:8px;">
                                <input type="hidden" name="suggestion_id" value="<?= $suggestion['id'] ?>">
                                <input type="hidden" name="reviewed" value="<?= $suggestion['is_reviewed'] ? 0 : 1 ?>">
                                <button class="button" type="submit" name="toggle_review"><?= $suggestion['is_reviewed'] ? 'Unmark' : 'Mark Reviewed' ?></button>
                            </form>
                            <form method="post" action="manage_suggestions.php" style="display:inline-block;">
                                <input type="hidden" name="suggestion_id" value="<?= $suggestion['id'] ?>">
                                <button class="button" type="submit" name="delete_suggestion" value="1" style="background:#dc2626;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include 'includes/footer.php'; ?>