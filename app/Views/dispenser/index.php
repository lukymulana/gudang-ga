<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">Manajemen Dispenser Sewa</h4>
            <p class="text-muted small">Monitoring aset sewa dan masa berlaku kontrak</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addDispenserModal">
            <i class="bi bi-plus-lg me-2"></i>Registrasi Dispenser
        </button>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-info-circle me-2"></i> <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Detail Aset</th>
                            <th>Vendor & Biaya</th>
                            <th>Lokasi</th>
                            <th>Periode Sewa</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dispensers as $d): ?>
                        <tr class="<?= (new DateTime($d['tgl_berakhir']) < new DateTime()) ? 'bg-light-danger' : '' ?>">
                            <td>
                                <span class="fw-bold text-primary d-block"><?= $d['nama_asset'] ?? 'Dispenser Unit' ?></span>
                                <small class="text-muted">SN: <?= $d['serial_number'] ?></small>
                            </td>
                            <td>
                                <span class="d-block text-dark"><?= $d['vendor'] ?></span>
                                <small class="fw-bold text-success">Rp <?= number_format(($d['biaya_sewa_per_bulan'] ?? 0), 0, ',', '.') ?>/bln</small>
                            </td>
                            <td class="fw-bold text-secondary"><?= $d['lokasi'] ?></td>
                            <td>
                                <?php 
                                    $today = new DateTime();
                                    $end   = new DateTime($d['tgl_berakhir']);
                                    $diff  = $today->diff($end)->days;
                                    $isExpired = ($end < $today);
                                    $isWarning = (!$isExpired && $diff <= 30);
                                ?>
                                <small class="text-muted d-block">Mulai: <?= date('d/m/Y', strtotime($d['tgl_mulai'])) ?></small>
                                
                                <?php if($isExpired): ?>
                                    <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i> EXPIRED</span>
                                <?php elseif($isWarning): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> <?= $diff ?> Hari Lagi</span>
                                    <small class="d-block text-warning fw-bold"><?= date('d/m/Y', strtotime($d['tgl_berakhir'])) ?></small>
                                <?php else: ?>
                                    <small class="fw-bold text-dark">Akhir: <?= date('d/m/Y', strtotime($d['tgl_berakhir'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($d['status'] == 'Aktif'): ?>
                                    <span class="badge bg-success-soft text-success border border-success">Aktif</span>
                                <?php elseif($d['status'] == 'Sedang Diperbaiki'): ?>
                                    <span class="badge bg-warning-soft text-warning border border-warning">Sedang Diperbaiki</span>
                                <?php elseif($d['status'] == 'Service'): ?>
                                    <span class="badge bg-info-soft text-info border border-info">Service</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-soft text-secondary border border-secondary">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <?php if($d['status'] == 'Aktif'): ?>
                                        <button class="btn btn-sm btn-outline-warning" title="Kirim ke Perbaikan" onclick="sendToRepair(<?= $d['id'] ?>)">
                                            <i class="bi bi-wrench-adjustable"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Non-Aktifkan" onclick="updateDispenserStatus(<?= $d['id'] ?>, 'Non-Aktif')">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success" title="Aktifkan Kembali" onclick="updateDispenserStatus(<?= $d['id'] ?>, 'Aktif')">
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="<?= base_url('dispenser/delete/'.$d['id']) ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Hapus data aset ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addDispenserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-shield-check me-2"></i>Registrasi Dispenser Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('dispenser/save') ?>" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ID / Serial Number</label>
                            <input type="text" name="serial_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Aset</label>
                            <input type="text" name="nama_asset" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Vendor</label>
                            <input type="text" name="vendor" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-primary">Biaya Sewa per Bulan (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="biaya_sewa_per_bulan" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Mulai Sewa</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Akhir Sewa</label>
                            <input type="date" name="tgl_berakhir" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Lokasi Penempatan</label>
                            <input type="text" name="lokasi" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan ke Aset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateDispenserStatus(id, status) {
    if(confirm('Ubah status dispenser ini menjadi ' + status + '?')) {
        window.location.href = '<?= base_url("dispenser/update_status") ?>/' + id + '/' + status;
    }
}

function sendToRepair(id) {
    if(confirm('Kirim dispenser ini ke vendor untuk perbaikan? Status akan berubah menjadi "Sedang Diperbaiki".')) {
        window.location.href = '<?= base_url("dispenser/send_to_service") ?>/' + id;
    }
}
</script>

<style>
    .bg-light-danger { background-color: rgba(220, 53, 69, 0.05) !important; }
    .bg-success-soft { background-color: rgba(40, 167, 69, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-info-soft { background-color: rgba(23, 162, 184, 0.1); }
    .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
</style>

<?= $this->endSection() ?>