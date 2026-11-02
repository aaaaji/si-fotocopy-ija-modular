<?php
$rows = fetch_all('SELECT t.*,p.total_biaya,c.nama_pelanggan FROM transaksi t JOIN pesanan p ON t.id_pesanan=p.id_pesanan LEFT JOIN pelanggan c ON p.id_pelanggan=c.id_pelanggan ORDER BY t.id_transaksi ASC LIMIT 300');
?>
<section class="panel">
    <div class="panel-title">
        <div>
            <h3>Riwayat Pembayaran</h3>
            <p>Semua pembayaran tercatat pada database.</p>
        </div>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Transaksi</th>
                    <th>Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Waktu Pembayaran</th>
                    <th>Tagihan</th>
                    <th>Diterima</th>
                    <th>Kembalian</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $r): ?>
                    <tr>
                        <td>#<?= $r['id_transaksi'] ?></td>
                        <td><a class="text-link"
                                href="index.php?page=detail&id=<?= $r['id_pesanan'] ?>">#<?= $r['id_pesanan'] ?></a></td>
                        <td><?= e($r['nama_pelanggan'] ?? 'Umum') ?></td>
                        <td><?= fmt_date($r['waktu_pembayaran']) ?></td>
                        <td><?= rupiah($r['total_biaya']) ?></td>
                        <td><?= rupiah($r['jumlah_bayar']) ?></td>
                        <td><?= rupiah($r['jumlah_bayar'] - $r['total_biaya']) ?></td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>