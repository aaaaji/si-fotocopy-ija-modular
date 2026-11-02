<?php
require __DIR__ . '/config.php';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    try {
        $server = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $server->exec('CREATE DATABASE IF NOT EXISTS `si_fotocopy_ija` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        // Split our own fixed schema at semicolons; no procedures/triggers in schema.
        $sql = file_get_contents(__DIR__ . '/database.sql');
        foreach (explode(';', $sql) as $statement) {
            if (trim($statement) !== '')
                $server->exec($statement);
        }
        $pdo = db();
        if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
            $success = 'Database sudah memiliki akun. Silakan masuk melalui halaman login.';
        } else {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO users(nama,username,password,role) VALUES(?,?,?,?)');
            $stmt->execute(['Pemilik IJA', 'admin', password_hash('admin123', PASSWORD_DEFAULT), 'pemilik']);
            $stmt->execute(['Pegawai IJA', 'pegawai', password_hash('pegawai123', PASSWORD_DEFAULT), 'pegawai']);
            $l = $pdo->prepare('INSERT INTO layanan(nama_layanan,tarif) VALUES(?,?)');
            foreach ([['Fotocopy Hitam Putih', 250], ['Print Warna', 1000], ['Jilid Lakban', 5000], ['Scan Dokumen', 2000], ['Laminasi A4', 4000], ['Print Hitam Putih', 500]] as $it)
                $l->execute($it);
            $p = $pdo->prepare('INSERT INTO pelanggan(nama_pelanggan,no_hp) VALUES(?,?)');
            foreach ([['Andi Saputra', '081234560001'], ['Siti Rahma', '081234560002'], ['Budi Santoso', '081234560003'], ['Dina Putri', '081234560004'], ['Rizky Maulana', null]] as $it)
                $p->execute($it);
            $order = $pdo->prepare('INSERT INTO pesanan(tanggal_pesanan,jumlah_dokumen,total_biaya,id_pelanggan,status_pesanan) VALUES(?,?,?,?,?)');
            $doc = $pdo->prepare('INSERT INTO dokumen(id_pesanan,jenis_dokumen,kondisi_dokumen) VALUES(?,?,?)');
            $detail = $pdo->prepare('INSERT INTO detail_pesanan(id_pesanan,id_layanan,jumlah,harga_satuan,subtotal) VALUES(?,?,?,?,?)');
            $trx = $pdo->prepare('INSERT INTO transaksi(id_pesanan,jumlah_bayar,waktu_pembayaran) VALUES(?,?,?)');
            $services = $pdo->query('SELECT * FROM layanan ORDER BY id_layanan')->fetchAll();
            for ($i = 30; $i >= 0; $i--) {
                $when = (new DateTimeImmutable('today'))->modify('-' . $i . ' days')->setTime(9 + ($i % 8), ($i * 7) % 60);
                $a = $services[$i % count($services)];
                $b = $services[($i + 2) % count($services)];
                $qty1 = 2 + ($i % 7);
                $qty2 = 1 + ($i % 3);
                $total = $qty1 * (int) $a['tarif'] + $qty2 * (int) $b['tarif'];
                $paid = $i > 2 || $i === 0;
                $order->execute([$when->format('Y-m-d H:i:s'), 2, $total, 1 + ($i % 5), $paid ? 'selesai' : ($i % 2 ? 'diproses' : 'menunggu')]);
                $id = (int) $pdo->lastInsertId();
                $doc->execute([$id, $i % 2 ? 'Makalah' : 'KTP', 'Lembaran']);
                $doc->execute([$id, 'Dokumen Tugas', 'Lembaran']);
                $detail->execute([$id, $a['id_layanan'], $qty1, $a['tarif'], $qty1 * $a['tarif']]);
                $detail->execute([$id, $b['id_layanan'], $qty2, $b['tarif'], $qty2 * $b['tarif']]);
                if ($paid)
                    $trx->execute([$id, $total, $when->modify('+15 minutes')->format('Y-m-d H:i:s')]);
            }
            $pdo->commit();
            $success = 'Instalasi berhasil! Login admin / admin123 atau pegawai / pegawai123. Segera ganti kata sandi demo setelah digunakan.';
        }
    } catch (Throwable $ex) {
        if (isset($pdo) && $pdo->inTransaction())
            $pdo->rollBack();
        $error = $ex->getMessage();
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Instalasi • Fotocopy IJA</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="auth-bg">
    <main class="auth-card">
        <div class="brand big">▣ <span>Fotocopy IJA</span></div>
        <h1>Instalasi Laragon</h1>
        <p class="muted">Satu klik untuk membuat 7 tabel, relasi, akun login, layanan dan transaksi contoh. Pastikan
            MySQL Laragon sudah berjalan.</p><?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div><?php endif; ?><?php if ($success): ?>
            <div class="notice success"><?= e($success) ?></div><a class="btn primary full" href="login.php">Buka halaman
                login →</a><?php else: ?>
            <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><button class="btn primary full"
                    type="submit">Pasang database + data dummy</button></form><?php endif; ?>
        <p class="small muted">Jika port/password MySQL berbeda, ubah config.php. Setelah instalasi, hapus file
            install.php.</p>
    </main>
</body>

</html>