<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-5">
        <?php if(session()->get('role') == 'Super Admin'): ?>
        <div class="card shadow-sm border-0 mb-3" style="border-left: 5px solid #0d6efd !important;">
            <div class="card-body">
                <label class="form-label fw-bold text-primary"><i class="bi bi-geo-alt-fill me-1"></i> Lokasi Gudang Anda Sekarang</label>
                <select id="active_warehouse" class="form-select" onchange="resetBasketOnWarehouseChange()">
                    <option value="">-- Pilih Lokasi --</option>
                    <option value="stok_depan">Gudang Depan</option>
                    <option value="stok_belakang">Gudang Belakang</option>
                </select>
                <small class="text-muted">Pilihan ini menentukan sumber stok yang dipotong.</small>
            </div>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Penerima / Karyawan</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">NPK Karyawan</label>
                    <div class="input-group">
                        <input type="text" id="npkInput" class="form-control" placeholder="Input NPK & Press Tab">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>
                </div>

                <div id="employeeInfo" class="mt-2 p-3 border rounded bg-light" style="display:none; border-left: 5px solid #198754 !important;">
                    <p class="mb-0 small text-muted">Nama: <strong id="resNama" class="text-dark">-</strong></p>
                    <p class="mb-0 small text-muted">Dept: <strong id="resDept" class="text-dark">-</strong></p>
                </div>

                <hr>
                
                <form action="<?= base_url('inventory/process_bulk_distribution') ?>" method="post">
                    <input type="hidden" name="active_warehouse" id="form_warehouse">
                    <input type="hidden" name="npk" id="hiddenNpk">
                    <input type="hidden" name="nama_res" id="hiddenNama">
                    
                    <h6 class="fw-bold"><i class="bi bi-cart4 me-2"></i>Daftar Barang (Basket)</h6>
                    <div id="basketContainer" class="mb-3">
                        <div class="alert alert-secondary small py-2 text-center">Belum ada barang dipilih</div>
                    </div>

                    <div id="submitSection" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Keterangan</label>
                            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Contoh: Pembagian rutin bulanan..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm" onclick="return validateBeforeSubmit()">
                            <i class="bi bi-check2-circle me-1"></i> Konfirmasi Distribusi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pilih Barang</h5>
                <input type="text" id="itemSearch" class="form-control form-control-sm w-50" placeholder="Cari nama/ukuran...">
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button class="btn btn-sm btn-outline-primary filter-btn active" data-filter="all">All</button>
                    <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="Seragam">Seragam</button>
                    <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="Sepatu">Sepatu</button>
                    <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="Asset">Asset</button>
                </div>

                <div class="table-responsive" style="max-height: 500px;">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Tersedia</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemTableBody">
                            <?php foreach ($all_items as $item): ?>
                            <tr class="item-row" data-category="<?= $item['kategori'] ?>">
                                <td>
                                    <strong><?= $item['nama_barang'] ?></strong><br>
                                    <small class="text-muted"><?= $item['ukuran'] ?> | <?= $item['status_seragam'] ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if(session()->get('role') == 'Super Admin'): ?>
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Depan: <?= $item['stok_depan'] ?? 0 ?> | Belakang: <?= $item['stok_belakang'] ?? 0 ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-dark">
                                            <?= (session()->get('role') == 'Admin Gudang Depan') ? ($item['stok_depan'] ?? 0) : ($item['stok_belakang'] ?? 0) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary rounded-circle" 
                                            onclick="addToBasket(<?= $item['id'] ?>, '<?= addslashes($item['nama_barang']) ?>', '<?= $item['ukuran'] ?>', <?= $item['stok_depan'] ?? 0 ?>, <?= $item['stok_belakang'] ?? 0 ?>)">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let basket = [];
const userRole = '<?= session()->get('role') ?>';

function resetBasketOnWarehouseChange() {
    if (basket.length > 0) {
        if (confirm('Mengubah gudang akan mengosongkan keranjang. Lanjutkan?')) {
            basket = [];
            renderBasket();
        } else {
            // Revert selection if user cancels
            return; 
        }
    }
    const wh = document.getElementById('active_warehouse').value;
    document.getElementById('form_warehouse').value = wh;
}

function addToBasket(id, name, size, sd, sb) {
    let maxStock = 0;
    let whName = "";

    // STRICT CHECK FOR SUPER ADMIN
    if (userRole === 'Super Admin') {
        const warehouseSelect = document.getElementById('active_warehouse');
        const selectedWH = warehouseSelect.value;

        if (!selectedWH) {
            alert('PERHATIAN: Pilih "Lokasi Gudang Anda Sekarang" terlebih dahulu!');
            warehouseSelect.focus();
            return;
        }

        maxStock = (selectedWH === 'stok_depan') ? sd : sb;
        whName = (selectedWH === 'stok_depan') ? 'Gudang Depan' : 'Gudang Belakang';
        document.getElementById('form_warehouse').value = selectedWH;
    } else {
        // Fix stock based on role
        maxStock = (userRole === 'Admin Gudang Depan') ? sd : sb;
        whName = (userRole === 'Admin Gudang Depan') ? 'Gudang Depan' : 'Gudang Belakang';
    }

    if (maxStock <= 0) {
        alert(`Gagal! Stok "${name}" kosong (0) di ${whName}.`);
        return;
    }

    if (basket.some(i => i.id === id)) {
        alert('Barang ini sudah ada di dalam list!');
        return;
    }
    
    basket.push({ id, name, size, max: maxStock });
    renderBasket();
}

function removeFromBasket(index) {
    basket.splice(index, 1);
    renderBasket();
}

function renderBasket() {
    const container = document.getElementById('basketContainer');
    const submitBtn = document.getElementById('submitSection');
    
    if (basket.length === 0) {
        container.innerHTML = '<div class="alert alert-secondary small py-2 text-center">Belum ada barang dipilih</div>';
        submitBtn.style.display = 'none';
        return;
    }

    submitBtn.style.display = 'block';
    container.innerHTML = basket.map((item, index) => `
        <div class="bg-white border rounded p-2 mb-2 shadow-sm d-flex justify-content-between align-items-center">
            <div style="font-size: 0.85rem">
                <strong>${item.name}</strong><br>
                <small class="text-muted">${item.size} (Max: ${item.max})</small>
            </div>
            <div class="d-flex align-items-center">
                <input type="hidden" name="items[${index}][id]" value="${item.id}">
                <input type="number" name="items[${index}][qty]" class="form-control form-control-sm me-2" value="1" min="1" max="${item.max}" style="width: 65px;">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromBasket(${index})"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    `).join('');
}

function validateBeforeSubmit() {
    if (userRole === 'Super Admin' && !document.getElementById('active_warehouse').value) {
        alert('Pilih lokasi gudang terlebih dahulu!');
        return false;
    }
    if (!document.getElementById('hiddenNpk').value) {
        alert('Silakan input NPK karyawan terlebih dahulu!');
        return false;
    }
    return true;
}

// Search & Filter Logic
document.getElementById('itemSearch').addEventListener('keyup', function() {
    let search = this.value.toLowerCase();
    document.querySelectorAll('.item-row').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(search) ? '' : 'none';
    });
});

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        let cat = this.getAttribute('data-filter');
        document.querySelectorAll('.item-row').forEach(row => {
            row.style.display = (cat === 'all' || row.getAttribute('data-category') === cat) ? '' : 'none';
        });
    });
});

// Employee Search
document.getElementById('npkInput').addEventListener('change', function() {
    let npk = this.value;
    fetch('<?= base_url("inventory/get_employee_info") ?>?npk=' + npk)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                document.getElementById('employeeInfo').style.display = 'block';
                document.getElementById('resNama').innerText = res.data.nama_karyawan;
                document.getElementById('resDept').innerText = res.data.department;
                document.getElementById('hiddenNpk').value = npk;
                document.getElementById('hiddenNama').value = res.data.nama_karyawan;
            } else {
                alert('NPK Not Found');
                document.getElementById('employeeInfo').style.display = 'none';
                this.value = '';
            }
        });
});
</script>
<?= $this->endSection() ?>