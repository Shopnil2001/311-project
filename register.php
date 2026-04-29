<?php
require_once 'includes/db.php';
$error = '';
$success = '';
$sectors = [];
$constituencies = [];
$sectorResult = $mysqli->query('SELECT id, name FROM sectors ORDER BY name');
while ($row = $sectorResult->fetch_assoc()) {
    $sectors[] = $row;
}
$conResult = $mysqli->query('SELECT id, name FROM constituencies ORDER BY name');
while ($row = $conResult->fetch_assoc()) {
    $constituencies[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] === 'mp' ? 'mp' : 'citizen';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $sector_id = intval($_POST['sector_id'] ?? 0);
    $constituency_id = intval($_POST['constituency_id'] ?? 0);

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in your name, email, and password.';
    } elseif ($role === 'citizen' && $sector_id <= 0) {
        $error = 'Please choose your sector.';
    } elseif ($role === 'mp' && $constituency_id <= 0) {
        $error = 'Please choose the constituency you represent.';
    } else {
        $check = $mysqli->prepare('SELECT id FROM citizens WHERE email = ? UNION SELECT id FROM mps WHERE email = ?');
        $check->bind_param('ss', $email, $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = 'An account with that email already exists.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            if ($role === 'citizen') {
                $insert = $mysqli->prepare('INSERT INTO citizens (name, email, password, phone, sector_id, registered_at) VALUES (?, ?, ?, ?, ?, NOW())');
                $insert->bind_param('ssssi', $name, $email, $passwordHash, $phone, $sector_id);
                if ($insert->execute()) {
                    header('Location: login.php?registered=1');
                    exit;
                }
                $error = 'Unable to register as citizen. Please try again.';
            } else {
                $insert = $mysqli->prepare('INSERT INTO mps (name, email, password, constituency_id, phone, is_approved) VALUES (?, ?, ?, ?, ?, 0)');
                $insert->bind_param('sssii', $name, $email, $passwordHash, $constituency_id, $phone);
                if ($insert->execute()) {
                    $success = 'MP request submitted. Super admin will approve your account before login.';
                } else {
                    $error = 'Unable to submit MP registration. Please try again.';
                }
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Register</h1>
    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert" style="background:#d1fae5;color:#064e3b;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" action="register.php" id="register-form">
        <label for="role">Register as</label>
        <select id="role" name="role">
            <option value="citizen">Citizen</option>
            <option value="mp">Member of Parliament</option>
        </select>

        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email address</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone / Contact</label>
        <input type="text" id="phone" name="phone">

        <div id="citizen-fields">
            <label for="sector_id">Your Sector</label>
            <select id="sector_id" name="sector_id">
                <option value="">Select a sector</option>
                <?php foreach ($sectors as $sector): ?>
                    <option value="<?= $sector['id'] ?>"><?= htmlspecialchars($sector['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="mp-fields" style="display:none;">
            <label for="constituency_id">Your Constituency</label>
            <select id="constituency_id" name="constituency_id">
                <option value="">Select a constituency</option>
                <?php foreach ($constituencies as $constituency): ?>
                    <option value="<?= $constituency['id'] ?>"><?= htmlspecialchars($constituency['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <p style="font-size:0.95rem;color:#555;margin-top:-10px;">MP registration will be reviewed by the super admin before the account is activated.</p>
        </div>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Register</button>
    </form>
</section>
<script>
    const roleSelect = document.getElementById('role');
    const citizenFields = document.getElementById('citizen-fields');
    const mpFields = document.getElementById('mp-fields');

    function updateRoleFields() {
        if (roleSelect.value === 'mp') {
            citizenFields.style.display = 'none';
            mpFields.style.display = 'block';
            document.getElementById('sector_id').required = false;
            document.getElementById('constituency_id').required = true;
        } else {
            citizenFields.style.display = 'block';
            mpFields.style.display = 'none';
            document.getElementById('sector_id').required = true;
            document.getElementById('constituency_id').required = false;
        }
    }
    roleSelect.addEventListener('change', updateRoleFields);
    updateRoleFields();
</script>