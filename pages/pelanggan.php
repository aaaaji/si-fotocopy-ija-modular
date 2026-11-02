<?php
$customers = fetch_all('SELECT c.*,COUNT(p.id_pesanan) n FROM pelanggan c LEFT JOIN pesanan p ON p.id_pelanggan=c.id_pelanggan GROUP BY c.id_pelanggan ORDER BY c.id_pelanggan DESC');
?>
<div class="two-col">
    <section class="panel">
        <h3>Daftar Pelanggan</h3>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Nomor HP</th>
                        <th>Jumlah Pesanan</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($customers as $c): ?>
                        <tr>
                            <td><b><?= e($c['nama_pelanggan']) ?></b></td>
                            <td><?= e($c['no_hp'] ?? '-') ?></td>
                            <td><?= $c['n'] ?></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <aside class="panel">
        <h3>Tambah Pelanggan</h3>
        <form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action"
                value="add_customer"><label>Nama Pelanggan<input name="nama_pelanggan" required
                    maxlength="100"></label><label>Nomor HP (opsional)<input name="no_hp" maxlength="25"></label><button
                class="btn primary full">Tambah Pelanggan</button></form>
    </aside>
</div>