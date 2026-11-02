<?php
require __DIR__ . '/config.php';
if (logged_in())
    go('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    try {
        $row = fetch_one('SELECT * FROM users WHERE username=?', [trim((string) ($_POST['username'] ?? ''))]);
        if ($row && password_verify((string) ($_POST['password'] ?? ''), $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int) $row['id_user'], 'nama' => $row['nama'], 'username' => $row['username'], 'role' => $row['role']];
            go('index.php');
        }
        $error = 'Username atau password salah.';
    } catch (Throwable $ex) {
        $error = 'Database belum siap. Jalankan install.php terlebih dahulu.';
    }
}
?><!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk • Fotocopy IJA</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="auth-bg">
    <main class="auth-card">
        <div class="brand big">▣ <span>Fotocopy IJA</span></div>
        <h1>Selamat datang</h1>
        <p class="muted">Masuk untuk mengelola pesanan dan laporan usaha.</p><?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><label>Username<input
                    name="username" autocomplete="username" required placeholder="Masukkan Username"></label><label>Password<input
                    name="password" type="password" autocomplete="current-password" required
                    placeholder="Masukkan Password"></label><button class="btn primary full">Masuk ke Dashboard →</button></form>
        </div><a class="small" href="install.php">Belum instal database?</a>
    </main>
</body>

</html>