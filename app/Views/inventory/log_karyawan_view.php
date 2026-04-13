<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<style>
    @media print {
        .no-print, #searchInput, .input-group, .btn, .reversal-form {
            display: none !important;
        }
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark">Log Distribusi Karyawan</h3>
            <p class="text-muted">Riwayat distribusi barang ke karyawan.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light rounded">
            <form action="<?= base_url('inventory/view_log_karyawan') ?>" method="get" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $start_date ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $end_date ?? '' ?>">
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-primary px-4 me-2">
                        <i class="bi bi-funnel-fill me-1"></i> Filter View
                    </button>
                    
                    <?php if (session()->get('role') === 'Super Admin'): ?>
                    <a href="<?= base_url('inventory/export_excel_log?' . http_build_query($_GET)) ?>" class="btn btn-success px-4">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('inventory/view_log_karyawan') ?>" class="btn btn-link text-muted ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3 no-print">
        <div class="col-md-4">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari NPK, Nama, atau Barang...">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="logTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Waktu</th>
                            <th>Karyawan (NPK)</th>
                            <th>Dept / Section</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Qty</th>
                            <th>Gudang</th>
                            <th class="no-print">Status / Tindakan</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $l): ?>
                            <tr class="<?= $l['status_peminjaman'] == 'Reversed' ? 'table-light text-muted' : '' ?>">
                                <td class="ps-3">
                                    <small class="d-block fw-bold"><?= date('d/m/Y', strtotime($l['tanggal_ambil'])) ?></small>
                                    <small class="text-muted"><?= date('H:i', strtotime($l['tanggal_ambil'])) ?></small>
                                </td>
                                <td>
                                    <span class="d-block fw-bold name-search"><?= $l['nama_karyawan'] ?></span>
                                    <span class="badge bg-secondary npk-search" style="font-size: 0.7rem;"><?= $l['npk'] ?></span>
                                </td>
                                <td>
                                    <small class="d-block"><?= $l['department'] ?></small>
                                    <small class="text-muted"><?= $l['section'] ?></small>
                                </td>
                                <td class="fw-bold item-search">
                                    <?= $l['nama_barang'] ?>
                                    <?php if($l['status_peminjaman'] == 'Reversed'): ?>
                                        <br><small class="text-danger italic"><i class="bi bi-exclamation-circle"></i> Transaction Voided</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $l['status_peminjaman'] == 'Reversed' ? 'bg-secondary' : 'bg-info text-dark' ?>"><?= $l['qty'] ?></span>
                                </td>
                                <td>
                                    <small class="badge <?= strpos($l['gudang_sumber'], 'Depan') !== false ? 'bg-primary' : 'bg-warning text-dark' ?>">
                                        <?= $l['gudang_sumber'] ?>
                                    </small>
                                </td>
                                <td class="no-print">
                                    <?php if ($l['status_peminjaman'] == 'Reversed'): ?>
                                        <small class="text-danger fw-bold">REVERSED</small><br>
                                        <small class="text-muted" style="font-size: 0.75rem;">Reason: <?= $l['alasan_koreksi'] ?></small>
                                    
                                    <?php elseif ($l['status_peminjaman'] == 'Kembali'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i> Returned</span>
                                    
                                    <?php elseif ($l['status_peminjaman'] == 'Dipinjam'): ?>
                                        <span class="badge bg-warning text-dark mb-1"><i class="bi bi-clock me-1"></i> Pinjaman</span>
                                        <form action="<?= base_url('inventory/reverse_transaction/' . $l['id']) ?>" method="post" class="reversal-form d-flex align-items-center">
                                            <input type="text" name="alasan" class="form-control form-control-sm me-1" placeholder="Alasan koreksi..." required style="width: 120px;">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Reverse Transaction" onclick="return confirm('Apakah Anda yakin ingin melakukan REVERSAL? Stok akan dikembalikan ke gudang.')">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>

                                    <?php else: // This is for 'Permanen' status ?>
                                        <form action="<?= base_url('inventory/reverse_transaction/' . $l['id']) ?>" method="post" class="reversal-form d-flex align-items-center">
                                            <input type="text" name="alasan" class="form-control form-control-sm me-1" placeholder="Alasan koreksi..." required style="width: 120px;">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Reverse Transaction" onclick="return confirm('Apakah Anda yakin ingin melakukan REVERSAL?')">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada data distribusi tercatat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#tableBody tr');

    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?= $this->endSection() ?>