<?= $this->extend('layout/main'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid p-4 bg-light min-vh-100">
    <div class="row mb-4 g-3 align-items-center">
        <div class="col-md-4">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 py-2" placeholder="Cari Nama Barang...">
            </div>
        </div>
        <div class="col-md-3">
            <select id="categoryFilter" class="form-select shadow-sm py-2">
                <option value="">Semua Kategori</option>
                <?php 
                $categories = array_unique(array_column($master_stok, 'kategori'));
                foreach($categories as $cat): ?>
                    <option value="<?= $cat ?>"><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <form action="<?= base_url('opname/save_multi') ?>" method="POST" id="opnameForm">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Audit Manajemen Stok</h2>
                <p class="text-muted mb-0">Lokasi Audit: <span class="fw-bold text-primary"><?= $selected_gudang ?? 'Semua Lokasi' ?></span></p>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold">
                    <i class="bi bi-cloud-arrow-up me-2"></i>Simpan Hasil Audit
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive" style="max-height: 80vh;">
                <table class="table align-middle mb-0" id="opnameTable">
                    <thead class="table-dark sticky-top">
                        <tr class="small text-uppercase tracking-wider">
                            <th class="ps-4 py-3" style="width: 40%;">Detail Barang & Kategori</th>
                            <th class="text-center" style="width: 20%;">Lokasi</th>
                            <th class="text-center" style="width: 15%;">Stok Sistem</th>
                            <th class="text-center" style="width: 25%;">Input Stok Fisik</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($master_stok as $item): ?>
    
    <?php if ($selected_gudang == 'Gudang Depan'): ?>
        <tr class="border-bottom hover-row item-row" 
            data-category="<?= $item['kategori'] ?>" 
            data-name="<?= strtolower($item['nama_barang'] ?? '') ?>">
            <td class="ps-4">
                <div class="fw-bold text-dark fs-6"><?= $item['nama_barang'] ?></div>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3"><?= $item['kategori'] ?></span>
            </td>
            <td class="text-center">
                <span class="badge bg-primary text-white px-3 py-2 shadow-sm" style="font-size: 0.7rem; width: 120px;">GUDANG DEPAN</span>
            </td>
            <td class="text-center">
                <div class="p-2 rounded bg-light border border-dashed">
                    <span class="h6 fw-bold mb-0 text-muted"><?= $item['stok_depan'] ?></span>
                </div>
            </td>
            <td class="pe-4 py-3">
                <div class="input-group">
                    <input type="number" name="fisik_depan[]" 
                           class="form-control form-control-lg text-center border-primary fw-bold shadow-sm custom-input" 
                           placeholder="---" required>
                    <span class="input-group-text bg-primary text-white border-primary">Pcs</span>
                </div>
                <input type="hidden" name="id_item[]" value="<?= $item['id'] ?>">
                <input type="hidden" name="nama_barang[]" value="<?= $item['nama_barang'] ?>">
                <input type="hidden" name="sys_depan[]" value="<?= $item['stok_depan'] ?>">
            </td>
        </tr>
    <?php endif; ?>

    <?php if ($selected_gudang == 'Gudang Belakang'): ?>
        <tr class="border-bottom hover-row item-row" 
            style="background-color: #f8f9fa;"
            data-category="<?= $item['kategori'] ?>" 
            data-name="<?= strtolower($item['nama_barang'] ?? '') ?>">
            <td class="ps-4 opacity-75">
                <div class="fw-normal text-muted"><?= $item['nama_barang'] ?></div>
                <small class="text-muted italic">Audit Secondary Location</small>
            </td>
            <td class="text-center">
                <span class="badge bg-info text-white px-3 py-2 shadow-sm" style="font-size: 0.7rem; width: 120px;">GUDANG BELAKANG</span>
            </td>
            <td class="text-center">
                <div class="p-2 rounded bg-white border border-dashed">
                    <span class="h6 fw-bold mb-0 text-muted"><?= $item['stok_belakang'] ?></span>
                </div>
            </td>
            <td class="pe-4 py-3">
                <div class="input-group">
                    <input type="number" name="fisik_belakang[]" 
                           class="form-control form-control-lg text-center border-info fw-bold shadow-sm custom-input" 
                           placeholder="---" required>
                    <span class="input-group-text bg-info text-white border-info">Pcs</span>
                </div>
                <input type="hidden" name="id_item[]" value="<?= $item['id'] ?>">
                <input type="hidden" name="nama_barang[]" value="<?= $item['nama_barang'] ?>">
                <input type="hidden" name="sys_belakang[]" value="<?= $item['stok_belakang'] ?>">
            </td>
        </tr>
    <?php endif; ?>

<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<style>
    /* Professional Dashboard Styling */
    .table thead th { border: none; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05rem; }
    .border-dashed { border-style: dashed !important; border-width: 1px !important; }
    
    .hover-row:hover {
        background-color: #ffffff !important;
        transition: 0.2s;
        box-shadow: inset 5px 0 0 #0d6efd;
    }

    /* Input Refinement */
    .custom-input {
        border-radius: 8px 0 0 8px !important;
        font-size: 1.1rem !important;
        transition: all 0.2s ease;
    }
    
    .custom-input:focus {
        background-color: #fff !important;
        transform: scale(1.02);
        z-index: 10;
    }

    .input-group-text {
        border-radius: 0 8px 8px 0 !important;
    }

    /* Custom Scrollbar */
    .table-responsive::-webkit-scrollbar { width: 8px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 10px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const rows = document.querySelectorAll('.item-row');

    function performFilter() {
        const query = searchInput.value.toLowerCase();
        const category = categoryFilter.value;

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const cat = row.getAttribute('data-category');

            const matchesSearch = name.includes(query);
            const matchesCategory = category === "" || cat === category;

            if (matchesSearch && matchesCategory) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    searchInput.addEventListener('input', performFilter);
    categoryFilter.addEventListener('change', performFilter);
});
</script>

<?= $this->endSection(); ?>