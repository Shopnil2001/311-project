<?php
require_once 'includes/db.php';
$error = '';
$sectors = [];
$sectorResult = $mysqli->query('SELECT sectors.id, sectors.name FROM sectors ORDER BY sectors.name');
while ($row = $sectorResult->fetch_assoc()) {
    $sectors[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $sector_id = intval($_POST['sector_id'] ?? 0);

    if ($name === '' || $email === '' || $password === '' || $sector_id <= 0) {
        $error = 'Please fill in all fields and choose your sector.';
    } else {
        $stmt = $mysqli->prepare('SELECT id FROM citizens WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'A citizen with that email already exists.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $mysqli->prepare('INSERT INTO citizens (name, email, password, phone, sector_id, registered_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $insert->bind_param('ssssi', $name, $email, $passwordHash, $phone, $sector_id);
            if ($insert->execute()) {
                header('Location: login.php?registered=1');
                exit;
            }
            $error = 'Unable to register. Please try again.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Citizen Registration</h1>
    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="register.php">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email address</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone / Contact</label>
        <input type="text" id="phone" name="phone">

        <label for="sector_id">Your Sector</label>
        <select id="sector_id" name="sector_id" required>
            <option value="">Select a sector</option>
            <?php foreach ($sectors as $sector): ?>
                <option value="<?= $sector['id'] ?>"><?= htmlspecialchars($sector['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Register</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>