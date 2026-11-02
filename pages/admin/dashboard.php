<?php
$income = (int) (fetch_one('SELECT COALESCE(SUM(p.total_biaya),0) AS n FROM transaksi t JOIN pesanan p ON p.id_pesanan=t.id_pesanan')['n'] ?? 0);
$orderCount = (int) (fetch_one('SELECT COUNT(*) n FROM pesanan')['n'] ?? 0);
$paidCount = (int) (fetch_one('SELECT COUNT(*) n FROM transaksi')['n'] ?? 0);
$pending = (int) (fetch_one("SELECT COUNT(*) n FROM pesanan WHERE status_pesanan!='selesai'")['n'] ?? 0);
$daily = fetch_all("SELECT DATE(t.waktu_pembayaran) AS tanggal, SUM(p.total_biaya) AS pendapatan FROM transaksi t JOIN pesanan p ON p.id_pesanan=t.id_pesanan WHERE t.waktu_pembayaran>=DATE_SUB(CURDATE(),INTERVAL 30 DAY) GROUP BY DATE(t.waktu_pembayaran) ORDER BY tanggal ASC");
$popular = fetch_all('SELECT l.nama_layanan,COALESCE(SUM(dp.jumlah),0) AS unit FROM layanan l LEFT JOIN detail_pesanan dp ON l.id_layanan=dp.id_layanan GROUP BY l.id_layanan,l.nama_layanan ORDER BY unit DESC LIMIT 6');
$latest = fetch_all('SELECT p.*,c.nama_pelanggan,t.id_transaksi FROM pesanan p LEFT JOIN pelanggan c ON p.id_pelanggan=c.id_pelanggan LEFT JOIN transaksi t ON p.id_pesanan=t.id_pesanan ORDER BY p.tanggal_pesanan DESC,p.id_pesanan DESC LIMIT 7');
?>
<div class="welcome">
    <div><span class="eyebrow">RINGKASAN BISNIS</span>
        <h2>Kelola usaha, lebih praktis.</h2>
        <p>Semua transaksi dan progres pesanan dalam satu tempat.</p>
    </div><a class="btn white" href="<?= e(url('buat')) ?>">+ Buat Pesanan</a>
</div>
<div class="stats">
    <div class="stat"><span>Total Pendapatan</span><strong><?= rupiah($income) ?></strong><small>Pendapatan dari pesanan
            yang dibayar</small></div>
    <div class="stat"><span>Total Pesanan</span><strong><?= $orderCount ?></strong><small>Seluruh pesanan tercatat</small>
    </div>
    <div class="stat"><span>Transaksi Dibayar</span><strong><?= $paidCount ?></strong><small>Pembayaran yang
            tercatat</small></div>
    <div class="stat"><span>Pesanan Belum Selesai</span><strong><?= $pending ?></strong><small>Menunggu / diproses</small>
    </div>
</div>
<div class="chart-grid">
    <section class="panel">
        <div class="panel-title">
            <div>
                <h3>Tren Pendapatan</h3>
                <p>30 hari terakhir • pembayaran masuk</p>
            </div>
        </div>
        <div class="chart-wrap"><canvas id="income-chart"></canvas></div>
    </section>
    <section class="panel">
        <div class="panel-title">
            <div>
                <h3>Layanan Terpopuler</h3>
                <p>Jumlah unit layanan yang dipesan</p>
            </div>
        </div>
        <div class="chart-wrap"><canvas id="service-chart"></canvas></div>
    </section>
</div>
<script type="application/json"
    id="chart-data"><?= json_encode(['daily' => $daily, 'popular' => $popular], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<section class="panel">
    <div class="panel-title">
        <h3>Pesanan Terbaru</h3><a class="text-link" href="<?= e(url('pesanan')) ?>">Lihat semua →</a>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pelanggan</th>
                    <th>Waktu</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th></th>
                </tr>
            </thead>
            <tbody><?php foreach ($latest as $r): ?>
                    <tr>
                        <td><b>#<?= $r['id_pesanan'] ?></b></td>
                        <td><?= e($r['nama_pelanggan'] ?? 'Umum') ?></td>
                        <td><?= fmt_date($r['tanggal_pesanan']) ?></td>
                        <td><?= rupiah($r['total_biaya']) ?></td>
                        <td><?= badge_status($r['status_pesanan']) ?></td>
                        <td><?= $r['id_transaksi'] ? '<span class="green">Lunas</span>' : '<span class="orange">Belum bayar</span>' ?>
                        </td>
                        <td><a class="text-link" href="index.php?page=detail&id=<?= $r['id_pesanan'] ?>">Detail →</a></td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>