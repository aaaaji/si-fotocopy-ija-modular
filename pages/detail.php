<?php
$id = (int) ($_GET['id'] ?? 0);
$order = fetch_one('SELECT p.*,c.nama_pelanggan,c.no_hp,t.id_transaksi,t.jumlah_bayar,t.waktu_pembayaran FROM pesanan p LEFT JOIN pelanggan c ON c.id_pelanggan=p.id_pelanggan LEFT JOIN transaksi t ON t.id_pesanan=p.id_pesanan WHERE p.id_pesanan=?', [$id]);
if (!$order): ?>
    <div class="panel empty">Pesanan tidak ditemukan. <a href="<?= e(url('pesanan')) ?>">Kembali</a></div>
<?php else:
    $details = fetch_all('SELECT dp.*,l.nama_layanan FROM detail_pesanan dp JOIN layanan l ON dp.id_layanan=l.id_layanan WHERE dp.id_pesanan=?', [$id]);
    $docs = fetch_all('SELECT * FROM dokumen WHERE id_pesanan=?', [$id]); ?>
    <div class="toolbar"><a class="btn secondary" href="<?= e(url('pesanan')) ?>">← Semua Pesanan</a><button
            onclick="window.print()" class="btn secondary">Cetak Nota</button></div>
    <div class="two-col">
        <section class="panel">
            <div class="panel-title">
                <div><span class="eyebrow">NOTA PESANAN</span>
                    <h2>#<?= $id ?></h2>
                    <p><?= fmt_date($order['tanggal_pesanan']) ?></p>
                </div><?= badge_status($order['status_pesanan']) ?>
            </div>
            <div class="info-grid">
                <div><small>Pelanggan</small><strong><?= e($order['nama_pelanggan'] ?? 'Umum') ?></strong></div>
                <div><small>Nomor HP</small><strong><?= e($order['no_hp'] ?? '-') ?></strong></div>
                <div><small>Jenis Dokumen</small><strong><?= e(implode(', ', array_column($docs, 'jenis_dokumen'))) ?></strong>
                </div>
                <div><small>Kondisi
                        Dokumen</small><strong><?= e(implode(', ', array_column($docs, 'kondisi_dokumen'))) ?></strong></div>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Qty</th>
                            <th>Harga/Unit</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($details as $d): ?>
                            <tr>
                                <td><?= e($d['nama_layanan']) ?></td>
                                <td><?= $d['jumlah'] ?></td>
                                <td><?= rupiah($d['harga_satuan']) ?></td>
                                <td><?= rupiah($d['subtotal']) ?></td>
                            </tr><?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="bill-total"><span>Total Tagihan</span><strong><?= rupiah($order['total_biaya']) ?></strong></div>
            <?php if ($order['catatan']): ?>
                <p class="small muted">Catatan: <?= e($order['catatan']) ?></p><?php endif; ?>
        </section>
        <aside class="stack">
            <section class="panel">
                <h3>Progres Pengerjaan</h3>
                <form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action"
                        value="update_status"><input type="hidden" name="id" value="<?= $id ?>"><label>Status Pesanan<select
                            name="status"><?php foreach (['menunggu', 'diproses', 'selesai'] as $st): ?>
                                <option value="<?= $st ?>" <?= $st === $order['status_pesanan'] ? 'selected' : '' ?>><?= ucfirst($st) ?>
                                </option><?php endforeach; ?>
                        </select></label><button class="btn secondary full">Simpan Status</button></form>
            </section>
            <section class="panel">
                <h3>Pembayaran</h3><?php if ($order['id_transaksi']): ?>
                    <div class="notice success">Sudah dibayar <?= fmt_date($order['waktu_pembayaran']) ?></div>
                    <div class="summary-line"><span>Uang diterima</span><strong><?= rupiah($order['jumlah_bayar']) ?></strong>
                    </div>
                    <div class="summary-line">
                        <span>Kembalian</span><strong><?= rupiah($order['jumlah_bayar'] - $order['total_biaya']) ?></strong></div>
                <?php else: ?>
                    <form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action"
                            value="pay"><input type="hidden" name="id" value="<?= $id ?>"><label>Jumlah Uang Diterima (Rp)<input
                                type="number" name="jumlah_bayar" min="<?= $order['total_biaya'] ?>"
                                value="<?= $order['total_biaya'] ?>" required></label><button class="btn primary full">Catat
                            Pembayaran</button></form><?php endif; ?>
            </section>
        </aside>
    </div><?php endif; ?>