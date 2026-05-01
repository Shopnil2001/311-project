<?php
require_once 'includes/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'citizen';
    if (!in_array($role, ['citizen', 'mp', 'admin'], true)) {
        $role = 'citizen';
    }

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        if ($role === 'citizen') {
            $stmt = $mysqli->prepare('SELECT citizens.id, citizens.name, citizens.password, citizens.sector_id, sectors.mp_id FROM citizens LEFT JOIN sectors ON citizens.sector_id = sectors.id WHERE citizens.email = ?');
        } elseif ($role === 'mp') {
            $stmt = $mysqli->prepare('SELECT id, name, password, is_approved FROM mps WHERE email = ?');
        } else {
            $stmt = $mysqli->prepare('SELECT id, name, password FROM admins WHERE email = ?');
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            if ($role === 'mp' && empty($user['is_approved'])) {
                $error = 'Your MP account is pending admin approval. Please wait for confirmation.';
            } else {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'role' => $role,
                    'name' => $user['name'],
                ];
                if ($role === 'citizen') {
                    $_SESSION['user']['sector_id'] = $user['sector_id'];
                    $_SESSION['user']['mp_id'] = $user['mp_id'];
                }
                $redirect = $role === 'admin' ? 'admin_dashboard.php' : ($role === 'mp' ? 'dashboard_mp.php' : 'dashboard_citizen.php');
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $error = 'Invalid login credentials.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="auth-wrapper">
    <section class="card">
        <h1>Welcome Back</h1>
        <!-- <p class="text-center text-muted" style="margin-top:-24px;margin-bottom:32px;">Please sign in to your account</p> -->

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert success">Registration complete. You may now log in.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label for="role">Login as</label>
                <select id="role" name="role">
                    <option value="citizen">Citizen</option>
                    <option value="mp">Member of Parliament</option>
                    <option value="admin">Super Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" style="margin-top: 8px;">Login to Account</button>

            <p class="text-center mt-4 text-sm text-muted">
                Don't have an account? <a href="register.php" class="link">Register here</a>
            </p>
        </form>
    </section>
</div>
<?php include 'includes/footer.php'; ?>