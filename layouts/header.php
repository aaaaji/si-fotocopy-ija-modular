<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($names[$page]) ?> • Fotocopy IJA</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" defer></script>
    <script src="assets/app.js" defer></script>
</head>

<body>
    <div class="app-shell">
        <aside class="sidebar"><a class="brand" href="index.php">▣ <span>Fotocopy <b>IJA</b></span></a>
            <div class="side-label">MENU UTAMA</div>
            <nav>
                <?php foreach (['dashboard' => '▦', 'pesanan' => '▤', 'buat' => '＋', 'pelanggan' => '♙', 'layanan' => '▧', 'pembayaran' => '◈'] + (is_owner() ? ['laporan' => '▥'] : []) as $key => $ico): ?><a
                        class="<?= ($page === $key ? 'active' : '') ?>" href="<?= e(url($key)) ?>"><span
                            class="navicon"><?= $ico ?></span><?= e($names[$key]) ?></a><?php endforeach; ?></nav>
            <div class="sidebar-bottom"><a href="<?= e(url('profil')) ?>">⚙ &nbsp; Pengaturan Akun</a><a
                    href="logout.php">↪ &nbsp; Keluar</a></div>
        </aside>
        <div class="main">
            <header class="topbar">
                <div>
                    <div class="eyebrow">SISTEM INFORMASI FOTOCOPY</div>
                    <h1><?= e($names[$page]) ?></h1>
                </div>
                <div class="top-actions"><span class="today"><?= date('d M Y') ?></span>
                    <div class="user-chip">
                        <div class="avatar"><?= e(strtoupper(substr($_SESSION['user']['nama'], 0, 1))) ?></div>
                        <div>
                            <strong><?= e($_SESSION['user']['nama']) ?></strong><small><?= e(ucfirst($_SESSION['user']['role'])) ?></small>
                        </div>
                    </div>
                </div>
            </header>
            <div class="content">
                <?php if (!empty($_SESSION['flash'])):
                    $f = $_SESSION['flash'];
                    unset($_SESSION['flash']); ?>
                    <div class="notice <?= e($f['kind']) ?>"><?= e($f['msg']) ?></div><?php endif; ?>