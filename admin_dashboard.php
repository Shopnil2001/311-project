<?php
require_once 'includes/db.php';
requireRole('admin');
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_mp'])) {
        $mpId = intval($_POST['mp_id'] ?? 0);
        if ($mpId > 0) {
            $approveStmt = $mysqli->prepare('UPDATE mps SET is_approved = 1 WHERE id = ?');
            $approveStmt->bind_param('i', $mpId);
            if ($approveStmt->execute()) {
                $assignStmt = $mysqli->prepare('UPDATE sectors SET mp_id = ? WHERE constituency_id = (SELECT constituency_id FROM mps WHERE id = ?)');
                $assignStmt->bind_param('ii', $mpId, $mpId);
                $assignStmt->execute();
                $success = 'MP account approved and assigned to their constituency.';
            } else {
                $error = 'Unable to approve MP request.';
            }
        }
    } elseif (isset($_POST['add_constituency'])) {
        $constituencyName = trim($_POST['constituency_name'] ?? '');
        if ($constituencyName === '') {
            $error = 'Please enter a constituency name.';
        } else {
            $insertStmt = $mysqli->prepare('INSERT INTO constituencies (name) VALUES (?)');
            $insertStmt->bind_param('s', $constituencyName);
            if ($insertStmt->execute()) {
                $success = 'New constituency added successfully.';
            } else {
                $error = 'Unable to add constituency. It may already exist.';
            }
        }
    }
}

$pending = $mysqli->query('SELECT mps.id, mps.name, mps.email, mps.phone, constituencies.name AS constituency_name FROM mps LEFT JOIN constituencies ON mps.constituency_id = constituencies.id WHERE mps.is_approved = 0 ORDER BY mps.id DESC')->fetch_all(MYSQLI_ASSOC);
$constituencies = $mysqli->query('SELECT id, name FROM constituencies ORDER BY name')->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<section class="hero">
    <h1>Admin Approvals</h1>
    <p>Review pending MP requests and approve them so MPs can log in.</p>
</section>
<section class="card">
    <?php if ($success): ?><div class="alert" style="background:#d1fae5;color:#064e3b;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($pending): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Constituency</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $mp): ?>
                        <tr>
                            <td><?= htmlspecialchars($mp['name']) ?></td>
                            <td><?= htmlspecialchars($mp['email']) ?></td>
                            <td><?= htmlspecialchars($mp['phone']) ?></td>
                            <td><?= htmlspecialchars($mp['constituency_name']) ?></td>
                            <td>
                                <form method="post" action="admin_dashboard.php" style="margin:0;">
                                    <input type="hidden" name="mp_id" value="<?= $mp['id'] ?>">
                                    <button class="button" type="submit" name="approve_mp">Approve</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No pending MP registrations at this time.</p>
    <?php endif; ?>
</section>
<section class="card">
    <h2>Add Constituency</h2>
    <form method="post" action="admin_dashboard.php">
        <label for="constituency_name">Constituency name</label>
        <input type="text" id="constituency_name" name="constituency_name" required>
        <button type="submit" name="add_constituency">Add Constituency</button>
    </form>
</section>
<section class="card">
    <h2>Existing constituencies</h2>
    <?php if ($constituencies): ?>
        <ul>
            <?php foreach ($constituencies as $constituency): ?>
                <li><?= htmlspecialchars($constituency['name']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No constituencies defined yet.</p>
    <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>
