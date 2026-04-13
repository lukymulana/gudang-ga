<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark p-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Transfer Stok Internal</h4>
                <span class="badge bg-dark text-white fw-bold">MUTASI GUDANG</span>
            </div>
            <div class="card-body p-4">
                
                <form action="<?= base_url('inventory/process_transfer') ?>" method="post">
                    
                    <?php 
                        $role = session()->get('role'); 
                        $isSuper = (stripos($role, 'Super') !== false);
                        $isDepan = (stripos($role, 'Depan') !== false);
                        $isBelakang = (stripos($role, 'Belakang') !== false && !$isSuper);
                    ?>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Barang</label>
                        <select name="item_id" id="itemSelect" class="form-select select2 shadow-sm" required>
                            <option value="">-- Cari Nama Barang --</option>
                            <?php foreach($items as $i): ?>
                                <?php 
                                    if ($isSuper) {
                                        $stockInfo = "Depan: " . ($i['stok_depan'] ?? 0) . " | Belakang: " . ($i['stok_belakang'] ?? 0);
                                    } elseif ($isDepan) {
                                        $stockInfo = "Stok Tersedia: " . ($i['stok_depan'] ?? 0);
                                    } else {
                                        $stockInfo = "Stok Tersedia: " . ($i['stok_belakang'] ?? 0);
                                    }
                                ?>
                                <option value="<?= $i['id'] ?>" 
                                        data-status="<?= $i['status_seragam'] ?? '-' ?>"
                                        data-gender="<?= ($i['gender'] == 'L') ? 'Laki-Laki' : (($i['gender'] == 'P') ? 'Perempuan' : '-') ?>"
                                        data-ukuran="<?= $i['ukuran'] ?? '-' ?>"
                                        data-cat="<?= $i['kategori'] ?>"
                                        data-depan="<?= $i['stok_depan'] ?? 0 ?>"
                                        data-belakang="<?= $i['stok_belakang'] ?? 0 ?>"
                                        <?= (isset($selected_id) && $selected_id == $i['id']) ? 'selected' : '' ?>>
                                    
                                    <?= $i['nama_barang'] ?> | 
                                    <?= $i['status_seragam'] ?? $i['kategori'] ?> | 
                                    Size: <?= $i['ukuran'] ?? '-' ?> 
                                    (<?= $stockInfo ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="itemDetailArea" class="mb-4 p-3 bg-light rounded border d-none">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <small class="text-muted d-block fw-bold text-uppercase">Status</small>
                                <span id="detailStatus" class="badge bg-primary">-</span>
                            </div>
                            <div class="col-md-4 border-start border-end">
                                <small class="text-muted d-block fw-bold text-uppercase">Gender</small>
                                <span id="detailGender" class="text-dark fw-bold">-</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block fw-bold text-uppercase">Ukuran</small>
                                <span id="detailUkuran" class="text-dark fw-bold">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger"><i class="bi bi-box-arrow-up me-1"></i>Gudang Asal</label>
                            <?php if ($isDepan): ?>
                                <input type="text" class="form-control bg-light" value="Gudang Depan" readonly>
                                <input type="hidden" name="gudang_asal" value="Gudang Depan">
                            <?php elseif ($isBelakang): ?>
                                <input type="text" class="form-control bg-light" value="Gudang Belakang" readonly>
                                <input type="hidden" name="gudang_asal" value="Gudang Belakang">
                            <?php else: ?>
                                <select name="gudang_asal" id="sourceSelect" class="form-select border-danger shadow-sm" required>
                                    <option value="Gudang Belakang">Gudang Belakang</option>
                                    <option value="Gudang Depan">Gudang Depan</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success"><i class="bi bi-box-arrow-in-down me-1"></i>Gudang Tujuan</label>
                            <?php if ($isDepan): ?>
                                <input type="text" class="form-control bg-light fw-bold text-primary" value="Gudang Belakang" readonly>
                                <input type="hidden" name="gudang_tujuan" value="Gudang Belakang">
                            <?php elseif ($isBelakang): ?>
                                <input type="text" class="form-control bg-light fw-bold text-primary" value="Gudang Depan" readonly>
                                <input type="hidden" name="gudang_tujuan" value="Gudang Depan">
                            <?php else: ?>
                                <select name="gudang_tujuan" id="targetSelect" class="form-select border-success shadow-sm" required>
                                    <option value="Gudang Depan">Gudang Depan</option>
                                    <option value="Gudang Belakang">Gudang Belakang</option>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Jumlah yang Dipindahkan (Pcs)</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-calculator"></i></span>
                            <input type="number" name="qty" id="qtyInput" class="form-control" min="1" placeholder="Masukkan jumlah..." required>
                        </div>
                        <small id="maxStockWarning" class="text-danger fw-bold mt-1 d-block"></small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning btn-lg fw-bold shadow-sm" onclick="return confirm('Apakah Anda yakin data mutasi sudah benar?')">
                            <i class="bi bi-arrow-repeat me-2"></i> TRANSFER
                        </button>
                        <a href="<?= base_url('inventory') ?>" class="btn btn-link text-muted">Batal dan Kembali</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
const itemSelect = document.getElementById('itemSelect');
const sourceSelect = document.getElementById('sourceSelect');
const targetSelect = document.getElementById('targetSelect');
const qtyInput = document.getElementById('qtyInput');
const maxStockWarning = document.getElementById('maxStockWarning');

function updateLimits() {
    const selected = itemSelect.options[itemSelect.selectedIndex];
    if (!selected || !selected.value) return;

    let source = "";
    if (sourceSelect) {
        source = sourceSelect.value;
    } else {
        source = "<?= $isDepan ? 'Gudang Depan' : 'Gudang Belakang' ?>";
    }

    const maxVal = (source === 'Gudang Depan') ? selected.getAttribute('data-depan') : selected.getAttribute('data-belakang');
    
    qtyInput.max = maxVal;
    maxStockWarning.innerText = `Maksimal stok yang bisa dipindah dari ${source}: ${maxVal}`;
    
    if (parseInt(qtyInput.value) > parseInt(maxVal)) {
        qtyInput.value = maxVal;
    }
}

itemSelect.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const detailArea = document.getElementById('itemDetailArea');
    
    if (this.value) {
        document.getElementById('detailStatus').innerText = (selected.getAttribute('data-cat') === 'Seragam') ? selected.getAttribute('data-status') : selected.getAttribute('data-cat');
        document.getElementById('detailGender').innerText = selected.getAttribute('data-gender');
        document.getElementById('detailUkuran').innerText = selected.getAttribute('data-ukuran');

        detailArea.classList.remove('d-none');
        updateLimits();
    } else {
        detailArea.classList.add('d-none');
        maxStockWarning.innerText = "";
    }
});

if (sourceSelect && targetSelect) {
    sourceSelect.addEventListener('change', function() {
        targetSelect.value = (this.value === 'Gudang Depan') ? 'Gudang Belakang' : 'Gudang Depan';
        updateLimits();
    });
    targetSelect.addEventListener('change', function() {
        sourceSelect.value = (this.value === 'Gudang Depan') ? 'Gudang Belakang' : 'Gudang Depan';
        updateLimits();
    });
}

if(itemSelect.value !== "") {
    itemSelect.dispatchEvent(new Event('change'));
}
</script>

<?= $this->endSection() ?>