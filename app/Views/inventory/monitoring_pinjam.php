<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-uppercase">
                        <i class="bi bi-clock-history me-2"></i> Monitoring Barang Belum Kembali
                    </h5>
                    <span class="badge bg-dark px-3"><?= count($loans) ?> Items Outstanding</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Peminjam (NPK)</th>
                                    <th>Departement / Section</th>
                                    <th>Barang & Kondisi</th>
                                    <th>Qty</th>
                                    <th>Tanggal Pinjam</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($loans)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="bi bi-check-circle text-success display-4"></i>
                                            <p class="mt-2 text-muted">Semua barang pinjaman sudah dikembalikan.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($loans as $l): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= $l['nama_karyawan'] ?></div>
                                            <div class="small text-muted"><?= $l['npk'] ?></div>
                                        </td>
                                        <td>
                                            <div class="small"><?= $l['department'] ?></div>
                                            <div class="small text-muted"><?= $l['section'] ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary"><?= $l['nama_barang'] ?></div>
                                            <span class="badge bg-info text-dark small"><?= $l['kondisi'] ?></span>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= $l['qty'] ?></span></td>
                                        <td><?= date('d/m/Y H:i', strtotime($l['tanggal_ambil'])) ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('inventory/return_item/' . $l['id']) ?>" 
                                               class="btn btn-sm btn-success rounded-pill px-3"
                                               onclick="return confirm('Konfirmasi: Barang sudah diterima kembali?')">
                                                <i class="bi bi-arrow-return-left me-1"></i> Set Kembali
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>