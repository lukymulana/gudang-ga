<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-5">
        <form action="<?= base_url('inventory/process_unified_basket') ?>" method="post">
            <input type="hidden" name="active_warehouse" id="hiddenActiveWarehouse" value="stok_belakang">
            <?php 
                // Step 1: Define Role Logic
                $role = session()->get('role') ?? ''; 
                $isSuper = (stripos($role, 'Super') !== false);
                $isDepan = (stripos($role, 'Depan') !== false);
            ?>
            <div class="card shadow-sm border-0 border-top border-primary border-5 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Pusat Pergerakan Stok (Movement Hub)</h5>
                </div>
                <div class="card-body">
            <?php if ($isSuper): ?>
            <div class="mb-3 p-3 bg-primary bg-opacity-10 border-start border-primary border-4 rounded shadow-sm">
                <label class="form-label fw-bold text-primary small mb-1">
                    <i class="bi bi-geo-alt-fill me-1"></i> KONTROL LOKASI GUDANG
                </label>
                <select id="globalWarehouseSelector" class="form-select fw-bold border-primary" onchange="syncWarehouseContext()">
                    <option value="stok_belakang">Gudang Belakang </option>
                    <option value="stok_depan">Gudang Depan </option>
                </select>
                <small class="text-muted" style="font-size: 0.75rem;">
                    *Super Admin: Tentukan sumber stok sebelum memulai transaksi.
                </small>
            </div>
            <?php endif; ?>

            <div class="mb-3 p-2 bg-light rounded border">
                <label class="form-label fw-bold small text-muted">Tipe Pergerakan</label>
                <select name="move_type" id="moveTypeSelect" class="form-select form-select-sm" onchange="toggleMovementType()">
                    <option value="distribute">Distribusi (Ke Karyawan)</option>
                    <option value="transfer">Mutasi Internal (Antar Gudang)</option>
                </select>
            </div>

                    <div id="sectionKaryawan">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cari NPK / Nama Karyawan</label>
                            <div class="input-group">
                                <input list="employeeList" name="npk" id="npkInput" class="form-control" placeholder="Ketik NPK atau Nama..." required>
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            </div>
                            <datalist id="employeeList">
                                <?php foreach($employees as $emp): ?>
                                    <option value="<?= $emp['npk'] ?>"><?= $emp['nama_karyawan'] ?> - <?= $emp['department'] ?></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div id="employeeInfo" class="mt-2 p-3 border rounded bg-light" style="display:none; border-left: 5px solid #198754 !important;">
                            <div class="row">
                                <div class="col-12">
                                    <small class="text-muted d-block fw-bold small">NAMA LENGKAP</small>
                                    <p id="resNama" class="mb-1 fw-bold text-dark">-</p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block fw-bold small">DEPARTEMEN</small>
                                    <p id="resDept" class="mb-0 text-dark">-</p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block fw-bold small">SEKSI</small>
                                    <p id="resSection" class="mb-0 text-dark">-</p>
                                </div>
                            </div>
                            <input type="hidden" name="nama_res" id="hiddenNama">
                            <input type="hidden" name="dept_res" id="hiddenDept">
                            <input type="hidden" name="sect_res" id="hiddenSect">
                        </div>
                    </div>

                    <div id="sectionTransfer" style="display:none;">
                        <?php if ($isSuper): ?>
                            <div class="row g-2 mb-3 bg-white p-3 rounded border border-warning shadow-sm">
                                <div class="col-6">
                                    <label class="small fw-bold text-muted text-uppercase">Asal Barang</label>
                                    <select name="gudang_asal" id="hubAsal" class="form-select form-select-sm border-danger" onchange="syncHubDestination()">
                                        <option value="Gudang Depan">Gudang Depan</option>
                                        <option value="Gudang Belakang" selected>Gudang Belakang</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold text-muted text-uppercase">Tujuan Mutasi</label>
                                    <select name="gudang_tujuan" id="hubTujuan" class="form-select form-select-sm border-success">
                                        <option value="Gudang Depan">Gudang Depan</option>
                                        <option value="Gudang Belakang">Gudang Belakang</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-primary"><i class="bi bi-info-circle me-1"></i> Mode Super Admin: Bebas menentukan arah mutasi.</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info py-2 mb-3 border-start border-4 border-info">
                                <i class="bi bi-shield-lock-fill me-2"></i>
                                Rute Mutasi: <strong><?= $isDepan ? 'Depan' : 'Belakang' ?></strong> &rarr; 
                                <strong class="text-primary"><?= $isDepan ? 'Belakang' : 'Depan' ?></strong>
                                
                                <input type="hidden" name="gudang_asal" value="<?= $isDepan ? 'Gudang Depan' : 'Gudang Belakang' ?>">
                                <input type="hidden" name="gudang_tujuan" value="<?= $isDepan ? 'Gudang Belakang' : 'Gudang Depan' ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3 text-dark fw-bold"><i class="bi bi-cart4 me-2"></i>Keranjang Distribusi</h5>
                    <div id="basketContainer">
                        <p class="text-muted text-center py-4">Belum ada barang di keranjang. <br>Klik (+) pada daftar barang untuk menambah.</p>
                    </div>

                    <div id="submitSection" style="display: none;">
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-bold small text-muted uppercase">Keterangan Tambahan</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Pengambilan seragam tahunan / Mutasi stok buffer"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" onclick="return confirm('Apakah Anda yakin ingin memproses transaksi ini?')">
                            <i class="bi bi-send-check-fill me-2"></i> <span id="submitBtnText">Proses Distribusi</span>
                        </button>
                        <a href="<?= base_url('inventory') ?>" class="btn btn-link text-muted d-block text-center mt-2">Batal dan Kembali</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Inventaris</h5>
                <div class="input-group input-group-sm w-50">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="itemSearch" class="form-control border-0" placeholder="Cari barang, status, atau ukuran...">
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3 d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-outline-secondary filter-btn" data-filter="">Semua</button>
                    <?php foreach($categories as $cat): ?>
                        <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="<?= $cat['nama_kategori'] ?>">
                            <?= $cat['nama_kategori'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div style="max-height: 500px; overflow-y: auto;">
    <table class="table table-hover table-sm border">
        <thead class="table-light sticky-top">
            <tr>
                <th>Detail Barang</th>
                <th>
                    Stok Gudang 
                    <span id="stockLabel"><?= ($role == 'Admin Gudang Depan') ? '(Depan)' : '(Belakang)' ?></span>
                </th>
                <th>Kondisi</th> <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody id="itemTable">
    <?php foreach($all_items as $item): ?>
    <tr class="item-row" data-category="<?= $item['kategori'] ?>">
        <td>
            <strong><?= $item['nama_barang'] ?></strong><br>
            <small class="text-muted"><?= $item['status_seragam'] ?> | <?= $item['ukuran'] ?></small>
        </td>
        <td>
            <?php if ($isSuper): ?>
                <span class="badge bg-primary stock-display stock-belakang" style="min-width: 45px;">
                    <?= $item['stok_belakang'] ?>
                </span>
                <span class="badge bg-info stock-display stock-depan d-none" style="min-width: 45px;">
                    <?= $item['stok_depan'] ?>
                </span>
            <?php else: ?>
                <?php 
                    $stock = ($isDepan) ? $item['stok_depan'] : $item['stok_belakang'];
                    if ($stock <= 0) {
                        $color = 'bg-danger';
                    } elseif (isset($item['min_stok']) && $stock <= $item['min_stok']) {
                        $color = 'bg-warning text-dark';
                    } else {
                        $color = 'bg-primary';
                    }
                ?>
                <span class="badge <?= $color ?>" style="min-width: 45px;">
                    <?= $stock ?>
                </span>
            <?php endif; ?>
        </td>

        <td>
            <?php 
                $cond = strtolower(trim($item['kondisi'] ?? ''));
                if ($cond == 'pinjaman' || $cond == 'pinjam'): 
            ?>
                <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">
                    <i class="bi bi-hourglass-split"></i> Pinjaman
                </span>
            <?php else: ?>
                <span class="badge bg-light text-success border" style="font-size: 0.7rem;">Baru</span>
            <?php endif; ?>
        </td>

        <td class="text-center">
    <button type="button" 
        class="btn btn-sm btn-success rounded-circle shadow-sm btn-add-basket" 
        onclick="addToBasket(
            <?= $item['id'] ?>, 
            '<?= addslashes($item['nama_barang']) ?>', 
            '<?= $item['ukuran'] ?>', 
            '<?= $item['status_seragam'] ?>', 
            <?= $item['stok_depan'] ?? 0 ?>, 
            <?= $item['stok_belakang'] ?? 0 ?>
        )">
        <i class="bi bi-plus"></i>
    </button>
</td>
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

// 1. Logika Toggle Tipe Pergerakan
function toggleMovementType() {
    const type = document.getElementById('moveTypeSelect').value;
    const sectionKaryawan = document.getElementById('sectionKaryawan');
    const sectionTransfer = document.getElementById('sectionTransfer');
    const npkInput = document.getElementById('npkInput');
    const submitBtnText = document.getElementById('submitBtnText');

    if (type === 'transfer') {
        sectionKaryawan.style.display = 'none';
        sectionTransfer.style.display = 'block';
        npkInput.removeAttribute('required');
        submitBtnText.innerText = 'Proses Transfer';
    } else {
        sectionKaryawan.style.display = 'block';
        sectionTransfer.style.display = 'none';
        npkInput.setAttribute('required', 'required');
        submitBtnText.innerText = 'Proses Distribusi';
    }
}

// 2. Logika Keranjang (Basket)
// 2. Logika Keranjang (Basket) dengan Validasi Ketat
function addToBasket(id, name, size, status, stockDepan, stockBelakang) {
    const isSuper = <?= $isSuper ? 'true' : 'false' ?>;
    const isDepan = <?= $isDepan ? 'true' : 'false' ?>;
    let availableStock = 0;

    // 1. Determine which stock to use
    if (isSuper) {
        const selectedWH = document.getElementById('globalWarehouseSelector').value;
        availableStock = (selectedWH === 'stok_depan') ? stockDepan : stockBelakang;
    } else {
        availableStock = isDepan ? stockDepan : stockBelakang;
    }

    // 2. Strict Validation
    if (availableStock <= 0) {
        alert(`Maaf, stok ${name} (${size}) kosong di gudang yang dipilih.`);
        return;
    }

    // 3. Prevent Duplicates
    if (basket.find(i => i.id === id)) {
        alert("Barang ini sudah ada di keranjang!");
        return;
    }

    // 4. Add to Basket
    basket.push({ id, name, size, status, maxStock: availableStock, currentQty: 1 });
    renderBasket();
}

function updateQty(index, val) {
    let requestedQty = parseInt(val);
    let maxAvailable = basket[index].maxStock;

    // Proteksi: Jika user mengetik manual melebihi stok
    if (requestedQty > maxAvailable) {
        alert(`Peringatan: Stok maksimal hanya ${maxAvailable}.`);
        basket[index].currentQty = maxAvailable;
        renderBasket(); // Reset tampilan agar angka kembali ke max
    } else if (requestedQty < 1 || isNaN(requestedQty)) {
        basket[index].currentQty = 1;
        renderBasket();
    } else {
        basket[index].currentQty = requestedQty;
    }
}

function removeFromBasket(id) {
    basket = basket.filter(i => i.id !== id);
    renderBasket();
}

function renderBasket() {
    const container = document.getElementById('basketContainer');
    const submitSection = document.getElementById('submitSection');
    
    // Update label total barang jika ada (Opsional untuk UI)
    const totalLabel = document.getElementById('totalItemsLabel');

    if (basket.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-4">Belum ada barang di keranjang. <br>Klik (+) pada daftar barang untuk menambah.</p>';
        submitSection.style.display = 'none';
        if(totalLabel) totalLabel.innerText = "0";
        return;
    }

    if(totalLabel) totalLabel.innerText = basket.length;
    submitSection.style.display = 'block';
    
    container.innerHTML = basket.map((item, index) => `
        <div class="card mb-2 border-0 bg-light shadow-sm">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="flex: 1;">
                        <small class="text-primary fw-bold">${item.name} [${item.status || '-'}]</small><br>
                        <small class="text-muted small">Ukuran: ${item.size} | <span class="badge bg-secondary">Maks: ${item.maxStock}</span></small>
                    </div>
                    <div style="width: 80px;" class="me-2">
                        <input type="hidden" name="items[${index}][id]" value="${item.id}">
                        <input type="number" name="items[${index}][qty]" 
                               class="form-control form-control-sm fw-bold border-primary" 
                               value="${item.currentQty}" 
                               min="1" 
                               max="${item.maxStock}"
                               onchange="updateQty(${index}, this.value)">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeFromBasket(${item.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// 3. Logika Cari & Filter
document.getElementById('itemSearch').addEventListener('keyup', () => filterTable());
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.replace('btn-primary', 'btn-outline-primary'));
        this.classList.replace('btn-outline-primary', 'btn-primary');
        filterTable(this.getAttribute('data-filter'));
    });
});

function filterTable(catFilter = '') {
    const searchText = document.getElementById('itemSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.item-row');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const cat = row.getAttribute('data-category');
        row.style.display = (text.includes(searchText) && (catFilter === '' || cat === catFilter)) ? '' : 'none';
    });
}

// 4. AJAX Lookup Karyawan Otomatis
document.getElementById('npkInput').addEventListener('change', function() {
    let npk = this.value;
    let infoBox = document.getElementById('employeeInfo');
    
    if (npk.length > 0) {
        fetch('<?= base_url("inventory/get_employee_info") ?>?npk=' + npk)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    document.getElementById('resNama').innerText = result.data.nama_karyawan;
                    document.getElementById('resDept').innerText = result.data.department;
                    document.getElementById('resSection').innerText = result.data.section;
                    
                    document.getElementById('hiddenNama').value = result.data.nama_karyawan;
                    document.getElementById('hiddenDept').value = result.data.department;
                    document.getElementById('hiddenSect').value = result.data.section;
                    
                    infoBox.style.display = 'block';
                } else {
                    alert('NPK Tidak Ditemukan!');
                    infoBox.style.display = 'none';
                }
            });
    } else {
        infoBox.style.display = 'none';
    }
});

// Enhanced Helper for Super Admin to sync the UI with the selected Warehouse
function syncHubDestination() {
    const asal = document.getElementById('hubAsal').value;
    const tujuan = document.getElementById('hubTujuan');
    const label = document.getElementById('stockLabel');
    
    // 1. Flip the destination dropdown automatically
    if (tujuan) {
        tujuan.value = (asal === 'Gudang Depan') ? 'Gudang Belakang' : 'Gudang Depan';
    }

    // 2. Update the Table Header Label so you know which stock you are looking at
    if (label) {
        label.innerText = (asal === 'Gudang Depan') ? '(Depan)' : '(Belakang)';
    }

    // 3. Toggle Stock Badges and Update Button Logic for every row in the table
    const isDepan = (asal === 'Gudang Depan');
    
    document.querySelectorAll('.item-row').forEach(row => {
        const badgeDepan = row.querySelector('.stock-depan');
        const badgeBelakang = row.querySelector('.stock-belakang');
        const addBtn = row.querySelector('.btn-add-basket');

        if (isDepan) {
            // Show Depan Badges, Hide Belakang Badges
            if(badgeDepan) badgeDepan.classList.remove('d-none');
            if(badgeBelakang) badgeBelakang.classList.add('d-none');
            
            // Update the "Add to Basket" button to use Depan stock for its max validation
            if(addBtn) {
                const stock = addBtn.getAttribute('data-depan');
                // Regex replaces the last number in the onclick function with the Depan stock
                addBtn.setAttribute('onclick', addBtn.getAttribute('onclick').replace(/,[^,]*\)$/, `, ${stock})`));
            }
        } else {
            // Show Belakang Badges, Hide Depan Badges
            if(badgeDepan) badgeDepan.classList.add('d-none');
            if(badgeBelakang) badgeBelakang.classList.remove('d-none');
            
            // Update the "Add to Basket" button to use Belakang stock for its max validation
            if(addBtn) {
                const stock = addBtn.getAttribute('data-belakang');
                addBtn.setAttribute('onclick', addBtn.getAttribute('onclick').replace(/,[^,]*\)$/, `, ${stock})`));
            }
        }
    });
}
function syncWarehouseContext() {
    const selector = document.getElementById('globalWarehouseSelector');
    if (!selector) return;

    const selectedWH = selector.value;
    const label = document.getElementById('stockLabel');

    const hiddenInput = document.getElementById('hiddenActiveWarehouse');
    if (hiddenInput) {
        hiddenInput.value = selectedWH;
    }
    
    // 1. Alert user if basket isn't empty (Prevents stock source mismatch)
    if (basket.length > 0) {
        if (!confirm("Mengubah gudang akan mengosongkan keranjang untuk memastikan validasi stok yang benar. Lanjutkan?")) {
            // Revert selector if user cancels
            selector.value = (selectedWH === 'stok_depan') ? 'stok_belakang' : 'stok_depan';
            return;
        }
        basket = [];
        renderBasket();
    }

    // 2. Update the Table Header Label
    if (label) {
        label.innerText = (selectedWH === 'stok_depan') ? '(Depan)' : '(Belakang)';
    }

    // 3. Toggle the Badge Visibility in the Table
    document.querySelectorAll('.stock-depan').forEach(el => {
        el.classList.toggle('d-none', selectedWH !== 'stok_depan');
    });
    document.querySelectorAll('.stock-belakang').forEach(el => {
        el.classList.toggle('d-none', selectedWH !== 'stok_belakang');
    });
}
// Run once on page load to set initial state
if (document.getElementById('globalWarehouseSelector')) {
    syncWarehouseContext();
}
</script>
<?= $this->endSection() ?>