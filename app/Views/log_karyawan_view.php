<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col">
        <h2 class="fw-bold"><i class="bi bi-people-fill me-2"></i>Log Distribusi Karyawan</h2>
        <p class="text-muted">Riwayat lengkap pengambilan barang oleh karyawan dari seluruh gudang.</p>
    </div>
    <div class="col-auto">
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Tanggal & Waktu</th>
                        <th>NPK & Nama Karyawan</th>
                        <th>Department / Section</th>
                        <th>Barang</th>
                        <th class="text-center">QTY</th>
                        <th>Gudang Sumber</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $l): ?>
                        <tr>
                            <td class="ps-3">
                                <span class="d-block fw-bold"><?= date('d M Y', strtotime($l['tanggal_ambil'])) ?></span>
                                <small class="text-muted"><?= date('H:i', strtotime($l['tanggal_ambil'])) ?> WIB</small>
                            </td>
                            <td>
                                <div class="fw-bold text-primary"><?= $l['npk'] ?></div>
                                <div class="text-dark"><?= $l['nama_karyawan'] ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= $l['department'] ?></span>
                                <div class="small text-muted mt-1"><?= $l['section'] ?></div>
                            </td>
                            <td><?= $l['nama_barang'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-success fs-6"><?= $l['qty'] ?></span>
                            </td>
                            <td>
                                <?php if($l['gudang_sumber'] == 'Admin Gudang Depan'): ?>
                                    <span class="text-primary"><i class="bi bi-house-door"></i> Gudang Depan</span>
                                <?php else: ?>
                                    <span class="text-warning"><i class="bi bi-archive"></i> Gudang Belakang</span>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted italic"><?= $l['keterangan'] ?: '-' ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada riwayat distribusi karyawan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>