<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4"><i class="bi bi-clock-history me-2"></i>Laporan & Histori Stok</h2>
        <a href="<?= base_url('inventory') ?>" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light rounded">
            <form action="<?= base_url('inventory/history') ?>" method="get" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= @$_GET['start_date'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= @$_GET['end_date'] ?>">
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-primary px-4 me-2">
                        <i class="bi bi-funnel-fill me-1"></i> Filter View
                    </button>
                    
                    <?php if (session()->get('role') === 'Super Admin'): ?>
                    <a href="<?= base_url('inventory/export_excel_stock_logs?' . http_build_query($_GET)) ?>" class="btn btn-success px-4">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('inventory/history') ?>" class="btn btn-link text-muted ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu (Jakarta)</th>
                            <th>Nama Barang</th>
                            <th>Aksi</th>
                            <th>Jumlah</th>
                            <th>Petugas</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= date('d M Y', strtotime($log['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></small>
                                    </td>
                                    <td><?= esc($log['item_name']) ?></td>
                                    <td>
                                        <?php if ($log['action_type'] == 'Masuk'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Barang Masuk</span>
                                        <?php elseif ($log['action_type'] == 'Keluar'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Barang Keluar</span>
                                        <?php elseif ($log['action_type'] == 'Edit Data'): ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Edit Data</span>
                                        <?php elseif ($log['action_type'] == 'Hapus Barang'): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Hapus Barang</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border"><?= esc($log['action_type']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold">
                                        <?= ($log['action_type'] == 'Edit Data') ? '-' : $log['quantity'] ?>
                                    </td>
                                    <td><i class="bi bi-person-circle me-1"></i><?= esc($log['user_name']) ?></td>
                                    <td class="small text-muted"><?= esc($log['keterangan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Belum ada data histori yang tercatat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>