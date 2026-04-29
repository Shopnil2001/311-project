<?php
require_once 'includes/db.php';
if (!empty($_SESSION['user'])) {
    header('Location: ' . ($_SESSION['user']['role'] === 'mp' ? 'dashboard_mp.php' : 'dashboard_citizen.php'));
    exit;
}

$sectors = [];
$result = $mysqli->query('SELECT sectors.id, sectors.name AS sector_name, constituencies.name AS constituency_name, mps.name AS mp_name FROM sectors LEFT JOIN constituencies ON sectors.constituency_id = constituencies.id LEFT JOIN mps ON sectors.mp_id = mps.id ORDER BY sectors.name');
while ($row = $result->fetch_assoc()) {
    $sectors[] = $row;
}
?>
<?php include 'includes/header.php'; ?>
<section class="hero">
    <h1>Digital Constituency Management</h1>
    <p>Connect citizens with their MP, submit complaints, suggest improvements, and share anonymous reports.</p>
    <p>
        <a class="button" href="register.php">Register as Citizen</a>
        <a class="button" href="login.php">Login</a>
    </p>
</section>
<section class="grid">
    <div class="card">
        <h2>How it works</h2>
        <p>Citizens register with their sector and can view their elected MP, submit complaints, report issues, or suggest improvements. MPs manage constituency issues, publish projects, and respond to citizens.</p>
    </div>
    <div class="card">
        <h2>Available sectors</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Sector</th><th>Constituency</th><th>MP</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($sectors as $sector): ?>
                        <tr>
                            <td><?= htmlspecialchars($sector['sector_name']) ?></td>
                            <td><?= htmlspecialchars($sector['constituency_name']) ?></td>
                            <td><?= htmlspecialchars($sector['mp_name'] ?: 'Unassigned') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>