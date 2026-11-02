<? php ?>
<section class="panel narrow">
    <h3>Informasi Akun</h3>
    <div class="info-grid">
        <div><small>Nama</small><strong><?= e($_SESSION['user']['nama']) ?></strong></div>
        <div><small>Username</small><strong><?= e($_SESSION['user']['username']) ?></strong></div>
        <div><small>Role</small><strong><?= e(ucfirst($_SESSION['user']['role'])) ?></strong></div>
    </div>
    <hr>
    <h3>Ganti Password</h3>
    <form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action"
            value="change_password"><label>Password Lama<input type="password" name="old"
                required></label><label>Password Baru (minimal 8 karakter)<input type="password" name="new"
                minlength="8" required></label><button class="btn primary">Ganti Password</button></form>
</section>