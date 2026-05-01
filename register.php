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
<div class="auth-wrapper">
    <section class="card">
        <h1>Create Account</h1>
        <!-- <p class="text-center text-muted" style="margin-top:24px;margin-bottom:32px;">Join the Constituency Hub</p> -->

        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="register.php" id="register-form">
            <div class="form-group">
                <label for="role">Register as</label>
                <select id="role" name="role">
                    <option value="citizen">Citizen</option>
                    <option value="mp">Member of Parliament</option>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone / Contact</label>
                <input type="text" id="phone" name="phone" placeholder="+880 1311-1100-00">
            </div>

            <div id="citizen-fields" class="form-group">
                <label for="sector_id">Your Sector</label>
                <select id="sector_id" name="sector_id">
                    <option value="">Select a sector</option>
                    <?php foreach ($sectors as $sector): ?>
                        <option value="<?= $sector['id'] ?>"><?= htmlspecialchars($sector['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="mp-fields" class="form-group" style="display:none;">
                <label for="constituency_id">Your Constituency</label>
                <select id="constituency_id" name="constituency_id">
                    <option value="">Select a constituency</option>
                    <?php foreach ($constituencies as $constituency): ?>
                        <option value="<?= $constituency['id'] ?>"><?= htmlspecialchars($constituency['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-sm text-muted mt-2">MP registration will be reviewed by the super admin before the
                    account is activated.</p>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" style="margin-top: 8px;">Register Account</button>

            <p class="text-center mt-4 text-sm text-muted">
                Already have an account? <a href="login.php" class="link">Login here</a>
            </p>
        </form>
    </section>
</div>
<script>
    const roleSelect = document.getElementById('role');
    const citizenFields = document.getElementById('citizen-fields');
    const mpFields = document.getElementById('mp-fields');

    function updateRoleFields() {
        if (roleSelect.value === 'mp') {
            citizenFields.style.display = 'none';
            mpFields.style.display = 'flex';
            document.getElementById('sector_id').required = false;
            document.getElementById('constituency_id').required = true;
        } else {
            citizenFields.style.display = 'flex';
            mpFields.style.display = 'none';
            document.getElementById('sector_id').required = true;
            document.getElementById('constituency_id').required = false;
        }
    }
    roleSelect.addEventListener('change', updateRoleFields);
    updateRoleFields();
</script>
<?php include 'includes/footer.php'; ?>