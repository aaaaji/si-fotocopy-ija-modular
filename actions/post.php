<?php
// Semua perubahan data diproses di sini; setiap aksi dilindungi CSRF.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'create_order') {
            $customer = (int) ($_POST['pelanggan'] ?? 0);
            $ids = $_POST['layanan'] ?? [];
            $quantities = $_POST['qty'] ?? [];
            if ($customer < 1 || !is_array($ids) || !is_array($quantities))
                throw new RuntimeException('Pilih pelanggan dan layanan.');
            $items = [];
            $total = 0;
            foreach ($ids as $idx => $id) {
                $id = (int) $id;
                $qty = (int) ($quantities[$idx] ?? 0);
                if (!$id || !$qty)
                    continue;
                if ($qty < 1 || $qty > 100000)
                    throw new RuntimeException('Jumlah tidak valid.');
                if (isset($items[$id]))
                    throw new RuntimeException('Layanan yang sama tidak boleh dipilih dua kali.');
                $l = fetch_one('SELECT * FROM layanan WHERE id_layanan=?', [$id]);
                if (!$l)
                    throw new RuntimeException('Layanan tidak tersedia.');
                $subtotal = $qty * (int) $l['tarif'];
                if ($subtotal > 2000000000 || $total + $subtotal > 2000000000)
                    throw new RuntimeException('Nilai pesanan terlalu besar.');
                $total += $subtotal;
                $items[$id] = ['qty' => $qty, 'tarif' => (int) $l['tarif'], 'subtotal' => $subtotal];
            }
            if (!$items)
                throw new RuntimeException('Tambahkan setidaknya satu layanan.');
            $customerRow = fetch_one('SELECT id_pelanggan FROM pelanggan WHERE id_pelanggan=?', [$customer]);
            if (!$customerRow)
                throw new RuntimeException('Pelanggan tidak ditemukan.');
            $jenis = trim((string) ($_POST['jenis_dokumen'] ?? ''));
            $kondisi = trim((string) ($_POST['kondisi_dokumen'] ?? ''));
            if (!$jenis || !$kondisi)
                throw new RuntimeException('Jenis dan kondisi dokumen wajib diisi.');
            db()->beginTransaction();
            $s = db()->prepare('INSERT INTO pesanan(tanggal_pesanan,jumlah_dokumen,total_biaya,id_pelanggan,status_pesanan,catatan) VALUES(NOW(),?,?,?,?,?)');
            $s->execute([array_sum(array_column($items, 'qty')), $total, $customer, 'menunggu', trim((string) ($_POST['catatan'] ?? ''))]);
            $orderId = (int) db()->lastInsertId();
            $s = db()->prepare('INSERT INTO dokumen(id_pesanan,jenis_dokumen,kondisi_dokumen) VALUES(?,?,?)');
            $s->execute([$orderId, mb_substr($jenis, 0, 50), mb_substr($kondisi, 0, 50)]);
            $s = db()->prepare('INSERT INTO detail_pesanan(id_pesanan,id_layanan,jumlah,harga_satuan,subtotal) VALUES(?,?,?,?,?)');
            foreach ($items as $id => $it)
                $s->execute([$orderId, $id, $it['qty'], $it['tarif'], $it['subtotal']]);
            db()->commit();
            alert('Pesanan #' . $orderId . ' berhasil dibuat.');
            go('index.php?page=detail&id=' . $orderId);
        }
        if ($action === 'update_status') {
            $id = (int) ($_POST['id'] ?? 0);
            $status = (string) ($_POST['status'] ?? '');
            if (!in_array($status, ['menunggu', 'diproses', 'selesai'], true))
                throw new RuntimeException('Status tidak valid.');
            $s = db()->prepare('UPDATE pesanan SET status_pesanan=? WHERE id_pesanan=?');
            $s->execute([$status, $id]);
            alert('Status pesanan berhasil diperbarui.');
            go('index.php?page=detail&id=' . $id);
        }
        if ($action === 'pay') {
            $id = (int) ($_POST['id'] ?? 0);
            $order = fetch_one('SELECT * FROM pesanan WHERE id_pesanan=?', [$id]);
            if (!$order)
                throw new RuntimeException('Pesanan tidak ditemukan.');
            $amount = (int) ($_POST['jumlah_bayar'] ?? 0);
            if ($amount < (int) $order['total_biaya'])
                throw new RuntimeException('Uang diterima kurang dari total biaya.');
            if (fetch_one('SELECT id_transaksi FROM transaksi WHERE id_pesanan=?', [$id]))
                throw new RuntimeException('Pesanan ini sudah dibayar.');
            db()->beginTransaction();
            $s = db()->prepare('INSERT INTO transaksi(id_pesanan,jumlah_bayar,waktu_pembayaran) VALUES(?,?,NOW())');
            $s->execute([$id, $amount]);
            db()->prepare("UPDATE pesanan SET status_pesanan='selesai' WHERE id_pesanan=?")->execute([$id]);
            db()->commit();
            alert('Pembayaran berhasil dicatat. Kembalian: ' . rupiah($amount - (int) $order['total_biaya']));
            go('index.php?page=detail&id=' . $id);
        }
        if ($action === 'save_service') {
            if (!is_owner())
                throw new RuntimeException('Hanya pemilik yang dapat mengubah tarif.');
            $name = trim((string) ($_POST['nama_layanan'] ?? ''));
            $price = (int) ($_POST['tarif'] ?? -1);
            $id = (int) ($_POST['id'] ?? 0);
            if (!$name || mb_strlen($name) > 50 || $price < 0 || $price > 2000000000)
                throw new RuntimeException('Nama atau tarif tidak valid.');
            if ($id) {
                db()->prepare('UPDATE layanan SET nama_layanan=?,tarif=? WHERE id_layanan=?')->execute([$name, $price, $id]);
            } else {
                db()->prepare('INSERT INTO layanan(nama_layanan,tarif) VALUES(?,?)')->execute([$name, $price]);
            }
            alert('Katalog layanan diperbarui.');
            go(url('layanan'));
        }
        if ($action === 'delete_service') {
            if (!is_owner())
                throw new RuntimeException('Hanya pemilik yang dapat menghapus layanan.');
            db()->prepare('DELETE FROM layanan WHERE id_layanan=?')->execute([(int) ($_POST['id'] ?? 0)]);
            alert('Layanan berhasil dihapus.');
            go(url('layanan'));
        }
        if ($action === 'add_customer') {
            $name = trim((string) ($_POST['nama_pelanggan'] ?? ''));
            $phone = trim((string) ($_POST['no_hp'] ?? ''));
            if (!$name || mb_strlen($name) > 100)
                throw new RuntimeException('Nama pelanggan wajib diisi (maks. 100 karakter).');
            db()->prepare('INSERT INTO pelanggan(nama_pelanggan,no_hp) VALUES(?,?)')->execute([$name, $phone ?: null]);
            alert('Pelanggan baru ditambahkan.');
            go(url('pelanggan'));
        }
        if ($action === 'change_password') {
            $id = (int) $_SESSION['user']['id'];
            $user = fetch_one('SELECT password FROM users WHERE id_user=?', [$id]);
            if (!password_verify((string) ($_POST['old'] ?? ''), $user['password']))
                throw new RuntimeException('Password lama salah.');
            $new = (string) ($_POST['new'] ?? '');
            if (strlen($new) < 8)
                throw new RuntimeException('Password baru minimal 8 karakter.');
            db()->prepare('UPDATE users SET password=? WHERE id_user=?')->execute([password_hash($new, PASSWORD_DEFAULT), $id]);
            alert('Password berhasil diganti.');
            go(url('profil'));
        }
        throw new RuntimeException('Aksi tidak tersedia.');
    } catch (Throwable $ex) {
        if (db()->inTransaction())
            db()->rollBack();
        alert('Gagal: ' . $ex->getMessage(), 'error');
        go($_SERVER['HTTP_REFERER'] ?? url());
    }
}
