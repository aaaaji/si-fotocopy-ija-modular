<?php
$services = fetch_all('SELECT l.*,COUNT(dp.id_pesanan) AS digunakan FROM layanan l LEFT JOIN detail_pesanan dp ON l.id_layanan=dp.id_layanan GROUP BY l.id_layanan ORDER BY l.id_layanan');
?>
<div class="two-col">
    <section class="panel">
        <div class="panel-title">
            <div>
                <h3>Daftar Layanan</h3>
                <p>Seluruh jenis layanan yang tersedia beserta tarifnya.</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Nama Layanan</th>
                        <th>Tarif per Unit</th>
                        <th>Dipakai dalam Pesanan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody><?php foreach ($services as $s): ?>
                        <tr>
                            <td><b><?= e($s['nama_layanan']) ?></b></td>
                            <td><?= rupiah($s['tarif']) ?></td>
                            <td><?= $s['digunakan'] ?> pesanan</td>
                            <td><?php if (is_owner()): ?><button class="text-link edit-service" type="button"
                                        data-id="<?= $s['id_layanan'] ?>" data-name="<?= e($s['nama_layanan']) ?>"
                                        data-price="<?= $s['tarif'] ?>">Edit</button><?php if (!$s['digunakan']): ?>
                                        <form method="post" class="inline" onsubmit="return confirm('Hapus layanan ini?')"><input
                                                type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action"
                                                value="delete_service"><input type="hidden" name="id"
                                                value="<?= $s['id_layanan'] ?>"><button class="text-link danger">Hapus</button></form>
                                    <?php endif; endif; ?>
                            </td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <aside class="panel"><?php if (is_owner()): ?>
            <h3 id="service-form-title">Tambah Layanan</h3>
            <form method="post" id="service-form"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden"
                    name="action" value="save_service"><input type="hidden" name="id" value=""><label>Nama Layanan<input
                        name="nama_layanan" maxlength="50" required placeholder="Nama layanan"></label><label>Tarif
                    (Rp)<input name="tarif" type="number" min="0" required placeholder="1000"></label><button
                    class="btn primary full">Simpan Layanan</button><button class="btn secondary full" type="reset"
                    id="reset-service">Reset Form</button></form><?php else: ?>
            <h3>Informasi</h3>
            <p class="muted">Perubahan tarif hanya dapat dilakukan oleh pemilik.</p><?php endif; ?>
    </aside>
</div>