<?php
// Pengamanan tambahan jika halaman ini di-include secara langsung.
if (!function_exists('is_owner') || !is_owner()) {
    http_response_code(403);
    exit('Akses ditolak. Halaman Laporan & Grafik hanya untuk admin.');
}
$from = (string) ($_GET['dari'] ?? date('Y-m-01'));
$to = (string) ($_GET['sampai'] ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    $from = date('Y-m-01');
    $to = date('Y-m-d');
}
$report = fetch_all('SELECT p.id_pesanan,p.tanggal_pesanan,p.total_biaya,c.nama_pelanggan,t.jumlah_bayar,t.waktu_pembayaran FROM transaksi t JOIN pesanan p ON t.id_pesanan=p.id_pesanan LEFT JOIN pelanggan c ON p.id_pelanggan=c.id_pelanggan WHERE DATE(t.waktu_pembayaran) BETWEEN ? AND ? ORDER BY t.waktu_pembayaran DESC', [$from, $to]);
$sum = array_sum(array_column($report, 'total_biaya'));
$byday = fetch_all('SELECT DATE(t.waktu_pembayaran) AS tanggal,COUNT(*) AS jumlah,SUM(p.total_biaya) AS pendapatan FROM transaksi t JOIN pesanan p ON t.id_pesanan=p.id_pesanan WHERE DATE(t.waktu_pembayaran) BETWEEN ? AND ? GROUP BY DATE(t.waktu_pembayaran) ORDER BY tanggal', [$from, $to]);
$services = fetch_all('SELECT l.nama_layanan,l.tarif,COALESCE(SUM(dp.jumlah),0) AS unit FROM layanan l LEFT JOIN detail_pesanan dp ON l.id_layanan=dp.id_layanan GROUP BY l.id_layanan ORDER BY unit DESC');
?>
<section class="panel">
    <div class="panel-title">
        <div>
            <h3>Laporan Transaksi</h3>
            <p>Filter berdasarkan tanggal pembayaran.</p>
        </div><button class="btn secondary" onclick="window.print()">Cetak Laporan</button>
    </div>
    <form method="get" class="filters"><input type="hidden" name="page" value="laporan"><label>Dari<input type="date"
                name="dari" value="<?= e($from) ?>"></label><label>Sampai<input type="date" name="sampai"
                value="<?= e($to) ?>"></label><button class="btn primary">Tampilkan</button></form>
    <div class="report-summary">
        <div><small>Total Pendapatan</small><strong><?= rupiah($sum) ?></strong></div>
        <div><small>Transaksi Lunas</small><strong><?= count($report) ?></strong></div>
        <div><small>Periode</small><strong><?= e($from) ?> — <?= e($to) ?></strong></div>
    </div>
    <div class="chart-wrap report-chart"><canvas id="report-chart"></canvas></div>
    <script type="application/json"
        id="report-data"><?= json_encode($byday, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Tanggal Bayar</th>
                    <th>Total Tagihan</th>
                    <th>Uang Diterima</th>
                </tr>
            </thead>
            <tbody><?php foreach ($report as $r): ?>
                    <tr>
                        <td>#<?= $r['id_pesanan'] ?></td>
                        <td><?= e($r['nama_pelanggan'] ?? 'Umum') ?></td>
                        <td><?= fmt_date($r['waktu_pembayaran']) ?></td>
                        <td><?= rupiah($r['total_biaya']) ?></td>
                        <td><?= rupiah($r['jumlah_bayar']) ?></td>
                    </tr><?php endforeach;
            if (!$report): ?>
                    <tr>
                        <td colspan="5" class="empty">Tidak ada transaksi pada periode ini.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<section class="panel">
    <div class="panel-title">
        <div>
            <h3>Laporan Layanan Tersedia</h3>
            <p>Katalog dan total unit pesanan dari seluruh periode.</p>
        </div>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Tarif Sekarang</th>
                    <th>Jumlah Unit Dipesan</th>
                </tr>
            </thead>
            <tbody><?php foreach ($services as $s): ?>
                    <tr>
                        <td><?= e($s['nama_layanan']) ?></td>
                        <td><?= rupiah($s['tarif']) ?></td>
                        <td><?= $s['unit'] ?></td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>