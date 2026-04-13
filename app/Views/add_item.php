<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white p-3">
        <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Input Barang</h4>
    </div>
    <div class="card-body p-4">
        <form action="<?= base_url('inventory/save') ?>" method="post">
            
            <div class="row mb-4">
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold text-primary">1. Kategori</label>
        <div class="input-group">
            <select name="kategori" id="main_cat" class="form-select border-primary" required onchange="updateSub(); fetchMinStock()">
                <option value="">-- Select Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['nama_kategori'] ?>"><?= $cat['nama_kategori'] ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="button" onclick="toggleField('main')">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
        <input type="text" id="main_custom"class="form-control mt-2 d-none" placeholder="New Category Name..." onchange="fetchMinStock()">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold text-primary">2. Sub Kategori</label>
        <div class="input-group">
            <select name="sub_kategori" id="sub_cat" class="form-select border-primary" disabled onchange="fetchMinStock()">
                <option value="">-- Select Sub --</option>
            </select>
            <button class="btn btn-primary" type="button" onclick="toggleField('sub')">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
        <input type="text" id="sub_custom" class="form-control mt-2 d-none" placeholder="New Sub-Category..." onchange="fetchMinStock()">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold text-primary">3. Nama Barang</label>
        <div class="input-group">
            <select id="item_name_sel" name="nama_barang" class="form-select border-primary" onchange="fetchMinStock()">
                <option value="">-- Select Item --</option>
                <?php foreach($existing_names as $n): ?>
                    <option value="<?= $n['nama_barang'] ?>"><?= $n['nama_barang'] ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="button" onclick="toggleField('item_name')">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
        <input type="text" id="item_name_custom" class="form-control mt-2 d-none" placeholder="New Item Name..." onchange="fetchMinStock()">
    </div>
</div>

            <div id="extra_attributes" class="row p-3 mb-4 d-none" style="background-color: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
                <div class="col-md-3 mb-2">
                    <label class="small fw-bold text-primary">Status</label>
                    <div class="input-group input-group-sm">
                        <select id="status_sel" name="status_seragam" class="form-select" onchange="fetchMinStock()">
                            <option value="-">-</option>
                            <option value="Kartap">Kartap</option>
                            <option value="Kontrak">Kontrak</option>
                            <option value="Konig">Konig</option>
                        </select>
                        <button class="btn btn-outline-primary" type="button" onclick="toggleField('status')"><i class="bi bi-plus"></i></button>
                    </div>
                    <input type="text" id="status_custom" class="form-control form-control-sm mt-1 d-none" placeholder="New Status..." onchange="fetchMinStock()">
                </div>

                <div class="col-md-3 mb-2">
    <label class="small fw-bold text-primary">Gender</label>
    <div class="input-group input-group-sm">
        <select id="gender_sel" name="gender" class="form-select" onchange="fetchMinStock()">
            <option value="-">-</option>
            <option value="Pria">Pria</option>
            <option value="Wanita">Wanita</option>
        </select>
        </div>
</div>

                <div class="col-md-3 mb-2">
                    <label class="small fw-bold text-primary">Size</label>
                    <div class="input-group input-group-sm">
                        <select id="size_sel" name="ukuran" class="form-select" onchange="fetchMinStock()">
                            <option value="-">-</option>
                            <option value="S">S</option><option value="M">M</option><option value="L">L</option><option value="XL">XL</option>
                        </select>
                        <button class="btn btn-outline-primary" type="button" onclick="toggleField('size')"><i class="bi bi-plus"></i></button>
                    </div>
                    <input type="text" id="size_custom" class="form-control form-control-sm mt-1 d-none" placeholder="New Size..." onchange="fetchMinStock()">
                </div>

                <div class="col-md-3 mb-2">
                    <label class="small fw-bold text-primary">Color</label>
                    <div class="input-group input-group-sm">
                        <select id="color_sel" name="warna" class="form-select" onchange="fetchMinStock()">
                            <option value="-">-</option>
                            <option value="Hitam">Hitam</option><option value="Putih">Putih</option>
                        </select>
                        <button class="btn btn-outline-primary" type="button" onclick="toggleField('color')"><i class="bi bi-plus"></i></button>
                    </div>
                    <input type="text" id="color_custom" class="form-control form-control-sm mt-1 d-none" placeholder="New Color..." onchange="fetchMinStock()">
                </div>
            </div>

            <div class="row mb-4">
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold text-primary">3. Merk (Brand)</label>
        <div class="input-group">
            <select name="merk" id="merk_sel" class="form-select border-primary">
                <option value="">-- Select Merk --</option>
                <?php if(isset($merks)): foreach ($merks as $m): ?>
                    <option value="<?= $m['nama_merk'] ?>"><?= $m['nama_merk'] ?></option>
                <?php endforeach; endif; ?>
            </select>
            <button class="btn btn-primary" type="button" onclick="toggleField('merk')">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
        <input type="text" id="merk_custom" class="form-control mt-2 d-none" placeholder="New Merk Name...">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold text-primary">4. Kondisi</label>
        <div class="input-group">
            <select name="kondisi" id="kondisi_sel" class="form-select border-primary" required>
                <option value="">-- Select Kondisi --</option>
                <?php if(isset($conditions)): foreach ($conditions as $con): ?>
                    <option value="<?= $con['nama_kondisi'] ?>"><?= $con['nama_kondisi'] ?></option>
                <?php endforeach; else: ?>
                    <option value="Baru">Baru</option>
                    <option value="Pinjaman">Pinjaman</option>
                    <option value="Bekas">Bekas</option>
                    <option value="Servis">Servis</option>
                <?php endif; ?>
            </select>
            <button class="btn btn-primary" type="button" onclick="toggleField('kondisi')">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
        <input type="text" id="kondisi_custom" class="form-control mt-2 d-none" placeholder="Enter New Condition Status...">
    </div>

    <?php if (session()->get('role') === 'Super Admin') : ?>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold text-primary">6. Warehouse</label>
        <div class="input-group">
            <select name="gudang" id="gudang_sel" class="form-select border-primary" required>
                <option value="Gudang Depan" selected>Gudang Depan</option>
                <option value="Gudang Belakang">Gudang Belakang</option> 
            </select>
            <button class="btn btn-outline-primary border-primary disabled" type="button">
                <i class="bi bi-house"></i>
            </button>
        </div>
    </div>
<?php else : ?>
    <input type="hidden" name="gudang" value="Gudang Depan">
<?php endif; ?>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold text-success">Stock Datang</label>
        <input type="number" name="stok_aktual" id="qty_input" class="form-control border-success" value="0" min="1" required oninput="calculateLiveStock()">
        
        <div id="live_stock_preview" class="mt-2 p-2 border-start border-4 border-info bg-light d-none">
            <div class="d-flex align-items-center mb-1">
                <div style="width: 15px; border-bottom: 2px solid #dee2e6; margin-right: 5px;"></div>
                <small class="text-muted">Current: <span id="current_stock_val" class="fw-bold">0</span></small>
            </div>
            <div class="d-flex align-items-center" style="margin-left: 15px;">
                <div style="width: 15px; border-bottom: 2px solid #0dcaf0; margin-right: 5px;"></div>
                <small class="text-info fw-bold">New Total: <span id="new_total_val">0</span> Pcs</small>
            </div>
        </div>
    </div>
</div>

<?php 
    $role = session()->get('role');
    $isSuperAdmin = ($role === 'Super Admin'); 
?>

<div class="row">
    <?php if (session()->get('role') === 'Super Admin'): ?>
        <div class="col-md-6 mb-3" id="price_wrapper">
            <label class="form-label fw-bold text-primary">Price per Unit (Rp)</label>
            <div class="input-group">
                <span class="input-group-text bg-light">Rp</span>
                <input type="number" name="harga_satuan" id="price_input" class="form-control border-primary" value="0">
            </div>
            <small class="text-muted" style="font-size: 0.7rem;">Enter price for new item registration.</small>
        </div>

        <div class="col-md-6 mb-3" id="min_stock_wrapper">
            <label class="form-label fw-bold text-danger">Min. Stock Alert</label>
            <input type="number" name="min_stok" id="min_stok_input" class="form-control border-danger" value="5">
            <small class="text-muted" style="font-size: 0.7rem;">Threshold for dashboard warnings.</small>
        </div>
    <?php else: ?>
        <input type="hidden" name="harga_satuan" value="0">
        <input type="hidden" name="min_stok" value="5">
    <?php endif; ?>
</div> 

<hr class="my-4"> 

<div class="row" id="form_actions">
    <div class="col-12 text-center text-md-start">
        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
            <i class="bi bi-save me-2"></i> Save to Inventory
        </button>
        <a href="<?= base_url('inventory') ?>" class="btn btn-outline-secondary px-4 py-2 ms-2">
            Cancel
        </a>
    </div>
</div>
<script>
const hierarchy = {
    'Seragam': ['Baju Kerja', 'Celana Kerja', 'Sepatu Safety', 'Topi'],
    'F&B': ['Susu', 'Kopi', 'Air Mineral'],
};

function updateSub() {
    const mainVal = document.getElementById('main_cat').value;
    const subSelect = document.getElementById('sub_cat');
    const extraAttr = document.getElementById('extra_attributes');
    
    // Toggle visibility
    if (mainVal === 'Seragam') {
        extraAttr.classList.remove('d-none');
        // Professional Touch: Ensure dropdowns have names when shown
        document.getElementById('status_sel').setAttribute('name', 'status_seragam');
        document.getElementById('size_sel').setAttribute('name', 'ukuran');
        document.getElementById('color_sel').setAttribute('name', 'warna');
    } else {
        extraAttr.classList.add('d-none');
        // Remove names so they don't interfere with non-seragam items
        document.getElementById('status_sel').removeAttribute('name');
        document.getElementById('size_sel').removeAttribute('name');
        document.getElementById('color_sel').removeAttribute('name');
    }

    subSelect.innerHTML = '<option value="">-- Select Sub --</option>';
    if (hierarchy[mainVal]) {
        subSelect.disabled = false;
        hierarchy[mainVal].forEach(item => {
            let opt = document.createElement('option');
            opt.value = item; opt.innerHTML = item;
            subSelect.appendChild(opt);
        });
    } else {
        subSelect.disabled = true;
    }
}

function toggleField(prefix) {
    const select = document.getElementById(prefix + '_sel') || 
                   document.getElementById(prefix + '_cat') || 
                   document.getElementById(prefix + '_select');
                   
    const custom = document.getElementById(prefix + '_custom');
    
    // Exact mapping to match Inventory.php Controller
    let dbName = '';
    if (prefix === 'item_name') dbName = 'nama_barang';
    else if (prefix === 'main') dbName = 'kategori';
    else if (prefix === 'sub') dbName = 'sub_kategori';
    else if (prefix === 'merk') dbName = 'merk';
    else if (prefix === 'kondisi') dbName = 'kondisi';
    else if (prefix === 'status') dbName = 'status_seragam'; 
    else if (prefix === 'size') dbName = 'ukuran';           
    else if (prefix === 'color') dbName = 'warna';          
    else dbName = prefix;

    if (custom.classList.contains('d-none')) {
        // --- MODE: SWITCHING TO MANUAL INPUT ---
        // 1. Show and Enable the Custom Input
        custom.classList.remove('d-none');
        custom.setAttribute('name', dbName); // Give name to Input
        custom.disabled = false;

        // 2. Hide and Completely Strip the Dropdown
        if (select) {
            select.classList.add('d-none');
            select.removeAttribute('name');   // REMOVE name from Select
            select.disabled = true;          // Disable to be safe
        }

        if (prefix === 'item_name') {
            $('#price_wrapper, #min_stock_wrapper').fadeIn();
        }
        custom.focus();
    } else {
        // --- MODE: SWITCHING BACK TO DROPDOWN ---
        // 1. Hide and Strip the Custom Input
        custom.classList.add('d-none');
        custom.removeAttribute('name');      // REMOVE name from Input
        custom.disabled = true;

        // 2. Show and Restore the Dropdown
        if (select) {
            select.classList.remove('d-none');
            select.setAttribute('name', dbName); // Restore name to Select
            select.disabled = false;
        }
        
        custom.value = '';
        if (prefix === 'item_name') {
            fetchMinStock(); 
        }
    }
    // Always trigger a sync for your live preview
    if (typeof fetchMinStock === "function") fetchMinStock();
}

function getVal(prefix) {
    const custom = document.getElementById(prefix + '_custom');
    const select = document.getElementById(prefix + '_sel') || document.getElementById(prefix + '_cat') || document.getElementById(prefix + '_cat_sel');
    
    const subSelect = document.getElementById('sub_cat');
    if (prefix === 'sub' && !custom.classList.contains('d-none')) return custom.value;
    if (prefix === 'sub') return subSelect.value;

    let val = (custom && !custom.classList.contains('d-none')) ? custom.value : (select ? select.value : '');
    return val;
}

let currentStockFromDB = 0; 

function fetchMinStock() {
    // 1. Get the Raw Values
    const itemName = getVal('item_name');
    const mainCat  = getVal('main');
    
    // 2. THE EMERGENCY CHECK
    // If these two are missing, we stop. Otherwise, we proceed!
    if (!itemName || !mainCat) {
        console.log("Missing Name or Category - hiding box.");
        document.getElementById('live_stock_preview').classList.add('d-none');
        return;
    }

    // 3. PACK PARAMS (Matches your PHP precisely)
    const params = new URLSearchParams({
        name: itemName,
        kategori: mainCat,
        sub_kategori: getVal('sub') || '',
        status: getVal('status') || '',
        gender: document.getElementById('gender_sel')?.value || '', // Safety check on ID
        size: getVal('size') || '',
        kondisi: document.getElementById('kondisi')?.value || ''
    });

    console.log("Fetching with:", params.toString());

    fetch('<?= base_url("inventory/get_min_stock") ?>?' + params.toString())
        .then(response => response.json())
        .then(data => {
            console.log("Data Received:", data);

            // UPDATE AND FORCE SHOW
            currentStockFromDB = parseInt(data.stok_aktual) || 0; 
            document.getElementById('current_stock_val').innerText = currentStockFromDB;
            
            // This is the line that makes it appear
            const previewBox = document.getElementById('live_stock_preview');
            if (previewBox) {
                previewBox.classList.remove('d-none');
                previewBox.style.display = 'block'; // Force override any CSS
            }
            
            if (typeof calculateLiveStock === "function") calculateLiveStock(); 
        })
        .catch(error => {
            console.error('Fetch Error:', error);
        });
}

function calculateLiveStock() {
    const qtyInput = document.getElementById('qty_input').value;
    const arrivalQty = parseInt(qtyInput) || 0;
    const newTotal = currentStockFromDB + arrivalQty;
    document.getElementById('new_total_val').innerText = newTotal;
}

// --- THE ADAPTIVE OBSERVERS ---
// This block links your HTML elements to the fetchMinStock function

const adaptiveFields = [
    'item_name_sel', 
    'main_cat', 
    'sub_cat', 
    'gender_sel', 
    'status_sel', 
    'size_sel', 
    'kondisi'
];

adaptiveFields.forEach(id => {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('change', fetchMinStock);
    }
});

// Also watch the "Custom" text inputs if the user types a new entry
['item_name_custom', 'status_custom', 'size_custom'].forEach(id => {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('blur', fetchMinStock);
    }
});
</script>

<?= $this->endSection() ?>