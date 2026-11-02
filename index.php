<?php
// Router: setiap halaman memiliki file terpisah. Login dan database tetap menggunakan config.php.
require __DIR__ . '/config.php';
require_login();
$page = (string) ($_GET['page'] ?? 'dashboard');
$allowed = ['dashboard', 'pesanan', 'buat', 'detail', 'layanan', 'pelanggan', 'pembayaran', 'laporan', 'profil'];
if (!in_array($page, $allowed, true))
    $page = 'dashboard';
// Laporan dan grafik hanya boleh diakses oleh akun admin (role: pemilik).
if ($page === 'laporan' && !is_owner()) {
    http_response_code(403);
    exit('Akses ditolak. Halaman Laporan & Grafik hanya untuk admin.');
}
require __DIR__ . '/actions/post.php';
$names = ['dashboard' => 'Dashboard', 'pesanan' => 'Daftar Pesanan', 'buat' => 'Pesanan Baru', 'detail' => 'Detail Pesanan', 'layanan' => 'Katalog Layanan', 'pelanggan' => 'Pelanggan', 'pembayaran' => 'Pembayaran', 'laporan' => 'Laporan & Grafik', 'profil' => 'Profil Akun'];
$csrf = csrf();
function badge_status(string $s): string
{
    return '<span class="pill ' . e($s) . '">' . e(ucfirst($s)) . '</span>';
}
function fmt_date(string $t): string
{
    return date('d M Y, H:i', strtotime($t));
}
require __DIR__ . '/layouts/header.php';
if ($page === 'dashboard') {
    require __DIR__ . '/pages/' . (is_owner() ? 'admin' : 'pegawai') . '/dashboard.php';
} else {
    require __DIR__ . '/pages/' . $page . '.php';
}
require __DIR__ . '/layouts/footer.php';
