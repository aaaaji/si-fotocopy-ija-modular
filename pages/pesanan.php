<?php
$q = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$params = [];
$where = [];
if ($q !== '') {
    $where[] = '(c.nama_pelanggan LIKE ? OR CAST(p.id_pesanan AS CHAR) LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if (in_array($status, ['menunggu', 'diproses', 'selesai'], true)) {
    $where[] = 'p.status_pesanan=?';
    $params[] = $status;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$rows = fetch_all('SELECT p.*,c.nama_pelanggan,t.id_transaksi FROM pesanan p LEFT JOIN pelanggan c ON c.id_pelanggan=p.id_pelanggan LEFT JOIN transaksi t ON t.id_pesanan=p.id_pesanan' . $whereSql . ' ORDER BY p.id_pesanan ASC LIMIT 300', $params);
?>
<section class="panel">
    <div class="panel-title">
        <div>
            <h3>Riwayat Pesanan</h3>
            <p>Telusuri semua pesanan dan status pengerjaannya.</p>
        </div><a class="btn primary" href="<?= e(url('buat')) ?>">+ Pesanan Baru</a>
    </div>
    <form method="get" class="filters"><input type="hidden" name="page" value="pesanan"><input name="q"
            value="<?= e($q) ?>" placeholder="Cari nama / ID pesanan..."><select name="status">
            <option value="">Semua Status</option><?php foreach (['menunggu', 'diproses', 'selesai'] as $st): ?>
                <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option><?php endforeach; ?>
        </select><button class="btn secondary">Cari</button></form>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Waktu</th>
                    <th>Dokumen / Unit</th>
                    <th>Tagihan</th>
                    <th>Status</th>
                    <th>Bayar</th>
                    <th></th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $r): ?>
                    <tr>
                        <td><b>#<?= $r['id_pesanan'] ?></b></td>
                        <td><?= e($r['nama_pelanggan'] ?? 'Umum') ?></td>
                        <td><?= fmt_date($r['tanggal_pesanan']) ?></td>
                        <td><?= (int) $r['jumlah_dokumen'] ?></td>
                        <td><?= rupiah($r['total_biaya']) ?></td>
                        <td><?= badge_status($r['status_pesanan']) ?></td>
                        <td><?= $r['id_transaksi'] ? 'Lunas' : 'Belum' ?></td>
                        <td><a class="text-link" href="index.php?page=detail&id=<?= $r['id_pesanan'] ?>">Buka →</a></td>
                    </tr><?php endforeach;
            if (!$rows): ?>
                    <tr>
                        <td colspan="8" class="empty">Tidak ada pesanan ditemukan.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>