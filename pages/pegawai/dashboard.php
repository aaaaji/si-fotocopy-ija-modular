<?php
$orderCount = (int) (fetch_one('SELECT COUNT(*) AS n FROM pesanan')['n'] ?? 0);
$pending = (int) (fetch_one("SELECT COUNT(*) AS n FROM pesanan WHERE status_pesanan='menunggu'")['n'] ?? 0);
$processing = (int) (fetch_one("SELECT COUNT(*) AS n FROM pesanan WHERE status_pesanan='diproses'")['n'] ?? 0);
$completed = (int) (fetch_one("SELECT COUNT(*) AS n FROM pesanan WHERE status_pesanan='selesai'")['n'] ?? 0);
$latest = fetch_all('SELECT p.id_pesanan,p.tanggal_pesanan,p.status_pesanan,p.total_biaya,c.nama_pelanggan FROM pesanan p LEFT JOIN pelanggan c ON c.id_pelanggan=p.id_pelanggan ORDER BY p.id_pesanan ASC LIMIT 15');
?>
<div class="welcome">
    <div><span class="eyebrow">PANEL OPERASIONAL PEGAWAI</span>
        <h2>Selamat bekerja!</h2>
        <p>Pantau antrean, proses pesanan, dan catat pembayaran.</p>
    </div><a class="btn white" href="<?= e(url('buat')) ?>">+ Buat Pesanan</a>
</div>
<div class="stats">
    <div class="stat"><span>Semua Pesanan</span><strong><?= $orderCount ?></strong></div>
    <div class="stat"><span>Menunggu</span><strong><?= $pending ?></strong></div>
    <div class="stat"><span>Diproses</span><strong><?= $processing ?></strong></div>
    <div class="stat"><span>Selesai</span><strong><?= $completed ?></strong></div>
</div>
<section class="panel">
    <div class="panel-title">
        <div>
            <h3>Daftar Antrean Pesanan</h3>
            <p>Diurutkan dari ID terkecil.</p>
        </div><a class="btn secondary" href="<?= e(url('pesanan')) ?>">Lihat Semua</a>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Tagihan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody><?php foreach ($latest as $p): ?>
                    <tr>
                        <td>#<?= $p['id_pesanan'] ?></td>
                        <td><?= e($p['nama_pelanggan'] ?? 'Umum') ?></td>
                        <td><?= fmt_date($p['tanggal_pesanan']) ?></td>
                        <td><?= badge_status($p['status_pesanan']) ?></td>
                        <td><?= rupiah($p['total_biaya']) ?></td>
                        <td><a class="text-link" href="<?= e(url('detail')) ?>&id=<?= $p['id_pesanan'] ?>">Detail</a></td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>