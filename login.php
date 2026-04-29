<?php
require_once 'includes/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] === 'mp' ? 'mp' : 'citizen';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        if ($role === 'citizen') {
            $stmt = $mysqli->prepare('SELECT citizens.id, citizens.name, citizens.password, citizens.sector_id, sectors.mp_id FROM citizens LEFT JOIN sectors ON citizens.sector_id = sectors.id WHERE citizens.email = ?');
        } else {
            $stmt = $mysqli->prepare('SELECT id, name, password FROM mps WHERE email = ?');
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'role' => $role,
                'name' => $user['name'],
            ];
            if ($role === 'citizen') {
                $_SESSION['user']['sector_id'] = $user['sector_id'];
                $_SESSION['user']['mp_id'] = $user['mp_id'];
            }
            header('Location: ' . ($role === 'mp' ? 'dashboard_mp.php' : 'dashboard_citizen.php'));
            exit;
        }
        $error = 'Invalid login credentials.';
    }
}
?>
<?php include 'includes/header.php'; ?>
<section class="card">
    <h1>Login</h1>
    <?php if (isset($_GET['registered'])): ?>
        <div class="alert" style="background:#d1fae5;color:#064e3b;">Registration complete. You may now log in.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label for="role">Login as</label>
        <select id="role" name="role">
            <option value="citizen">Citizen</option>
            <option value="mp">Member of Parliament</option>
        </select>

        <label for="email">Email address</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>