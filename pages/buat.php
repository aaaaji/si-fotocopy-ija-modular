<?php
$customers = fetch_all('SELECT * FROM pelanggan ORDER BY nama_pelanggan');
$services = fetch_all('SELECT * FROM layanan ORDER BY nama_layanan');
?>
<div class="two-col">
    <section class="panel">
        <div class="panel-title">
            <div>
                <h3>Form Pesanan Baru</h3>
                <p>Tarif otomatis diambil dari database.</p>
            </div>
        </div>
        <form method="post" id="order-form"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden"
                name="action" value="create_order"><label>Pelanggan<select name="pelanggan" required>
                    <option value="">Pilih pelanggan</option><?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id_pelanggan'] ?>"><?= e($c['nama_pelanggan']) ?></option><?php endforeach; ?>
                </select></label>
            <p class="small">Belum ada? <a href="<?= e(url('pelanggan')) ?>">Tambah pelanggan di sini.</a></p>
            <div class="form-grid"><label>Jenis Dokumen<input name="jenis_dokumen" maxlength="50" required
                        placeholder="Contoh: Makalah"></label><label>Kondisi Dokumen<select name="kondisi_dokumen"
                        required>
                        <option>Lembaran</option>
                        <option>Buku</option>
                        <option>Rusak</option>
                        <option>Digital</option>
                    </select></label></div>
            <h4>Rincian Layanan</h4>
            <div id="service-rows">
                <div class="service-row"><select name="layanan[]" required class="service-picker">
                        <option value="">Pilih layanan</option><?php foreach ($services as $s): ?>
                            <option value="<?= $s['id_layanan'] ?>" data-price="<?= $s['tarif'] ?>"><?= e($s['nama_layanan']) ?> —
                                <?= rupiah($s['tarif']) ?></option><?php endforeach; ?>
                    </select><input name="qty[]" type="number" min="1" max="100000" value="1" required
                        aria-label="Jumlah unit"><button type="button" class="btn secondary remove-row">×</button></div>
            </div><button class="btn secondary" id="add-service" type="button">+ Tambah Layanan</button><label>Catatan
                (opsional)<textarea name="catatan" rows="3" maxlength="255"
                    placeholder="Catatan pengerjaan pesanan..."></textarea></label><button
                class="btn primary full">Simpan Pesanan</button>
        </form>
    </section>
    <aside class="panel sidebar-summary">
        <h3>Ringkasan Tagihan</h3>
        <p class="muted">Perhitungan otomatis berdasarkan tarif tiap layanan × jumlah.</p>
        <div class="summary-line"><span>Total</span><strong id="order-total">Rp 0</strong></div>
        <p class="small muted">Total final dihitung ulang pada server untuk mencegah manipulasi nominal.</p>
    </aside>
</div>