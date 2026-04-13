<div id="sidebar" class="d-flex flex-column p-3">
    <div class="sidebar-header mb-2">General Affairs</div>

    <div class="mb-3 px-3">
        <a href="<?= base_url('authentication/logout') ?>" class="btn btn-outline-danger btn-sm w-100 text-start">
            <i class="bi bi-box-arrow-left me-2"></i> Logout
        </a>
    </div>

    <ul class="nav nav-pills flex-column mb-auto">
        
        <li class="nav-item">
            <?php $isExecDashboard = (uri_string() == 'dashboard'); ?>
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= $isExecDashboard ? 'active' : '' ?> border-start border-4 border-info">
                <i class="bi bi-graph-up-arrow me-2"></i> GA Executive Dashboard
            </a>
        </li>

        <?php if (session()->get('role') !== 'Office'): ?>
            <hr class="my-2 opacity-25">

            <li class="nav-small-cap text-muted small text-uppercase mb-1" style="font-size: 0.65rem;">Inventory</li>
            
            <li>
                <?php $isInventory = (uri_string() == 'inventory' || uri_string() == '' || uri_string() == '/'); ?>
                <a href="<?= base_url('inventory') ?>" class="nav-link <?= $isInventory ? 'active' : '' ?>">
                    <i class="bi bi-box-seam me-2"></i> Manajemen Stock
                </a>
            </li>

            <li>
                <?php $isDistribusi = (strpos(uri_string(), 'inventory/distribusi') !== false); ?>
                <a href="<?= base_url('inventory/distribusi') ?>" class="nav-link <?= $isDistribusi ? 'active' : '' ?> text-warning fw-bold">
                    <i class="bi bi-lightning-charge-fill me-2"></i> Distribusi
                </a>
            </li>

            <li>
                <?php $isLogKaryawan = (strpos(uri_string(), 'inventory/view_log_karyawan') !== false); ?>
                <a href="<?= base_url('inventory/view_log_karyawan') ?>" class="nav-link <?= $isLogKaryawan ? 'active' : '' ?>">
                    <i class="bi bi-people-fill me-2"></i> Log Karyawan
                </a>
            </li>

            <li>
                <?php $isMonitoring = (strpos(uri_string(), 'inventory/monitoring_pinjam') !== false); ?>
                <a href="<?= base_url('inventory/monitoring_pinjam') ?>" class="nav-link <?= $isMonitoring ? 'active' : '' ?>">
                    <i class="bi bi-hourglass-split me-2"></i> Monitoring Pinjaman
                </a>
            </li>

            <hr class="my-2 opacity-25">
            <li class="nav-small-cap text-muted small text-uppercase mb-1" style="font-size: 0.65rem;">Fixed Assets</li>
            
            <li>
                <?php $isDispenser = (strpos(uri_string(), 'dispenser') !== false); ?>
                <a href="<?= base_url('dispenser') ?>" class="nav-link <?= $isDispenser ? 'active' : '' ?>">
                    <i class="bi bi-water me-2 text-info"></i> Modul Dispenser Sewa
                </a>
            </li>

            <hr class="my-2 opacity-25">
            
            <?php if (in_array(session()->get('role'), ['Super Admin', 'Admin Gudang Depan'])): ?>
            <li>
                <?php $isAdd = (strpos(uri_string(), 'inventory/add') !== false); ?>
                <a href="<?= base_url('inventory/add') ?>" class="nav-link <?= $isAdd ? 'active' : '' ?>">
                    <i class="bi bi-plus-circle me-2"></i> Input Barang Datang
                </a>
            </li>
            <?php endif; ?>

            <?php if (session()->get('role') == 'Super Admin'): ?>
            <li>
                <?php 
                    $isOpname = (strpos(uri_string(), 'opname') !== false); 
                    $currentGudang = $_GET['gudang'] ?? '';
                ?>
                <a class="nav-link <?= $isOpname ? 'active' : '' ?> border-start border-4 border-warning d-flex justify-content-between align-items-center" 
                   data-bs-toggle="collapse" 
                   href="#opnameSubmenu" 
                   role="button" 
                   aria-expanded="<?= $isOpname ? 'true' : 'false' ?>">
                    <span><i class="bi bi-clipboard-check-fill me-2"></i> Stok Opname</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                
                <div class="collapse <?= $isOpname ? 'show' : '' ?>" id="opnameSubmenu">
                    <ul class="list-unstyled ps-4 mt-1 pb-2 shadow-sm bg-light rounded">
                        <li class="py-1">
                            <a href="<?= base_url('opname?gudang=Gudang Depan') ?>" 
                               class="nav-link text-dark py-1 <?= ($currentGudang == 'Gudang Depan') ? 'fw-bold text-primary' : '' ?>">
                                <i class="bi bi-arrow-right-short"></i> Gudang Depan
                            </a>
                        </li>
                        <li class="py-1">
                            <a href="<?= base_url('opname?gudang=Gudang Belakang') ?>" 
                               class="nav-link text-dark py-1 <?= ($currentGudang == 'Gudang Belakang') ? 'fw-bold text-primary' : '' ?>">
                                <i class="bi bi-arrow-right-short"></i> Gudang Belakang
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li>
                <?php $isHistory = (strpos(uri_string(), 'inventory/history') !== false); ?>
                <a href="<?= base_url('inventory/history') ?>" class="nav-link <?= $isHistory ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text me-2"></i> Laporan & History
                </a>
            </li>
            
            <li>
                <?php $isMaster = (strpos(uri_string(), 'master') !== false); ?>
                <a href="<?= base_url('master') ?>" class="nav-link <?= $isMaster ? 'active' : '' ?>">
                    <i class="bi bi-tags me-2"></i> Master Categories
                </a>
            </li>
            <?php endif; ?>

        <?php endif; // End of check for Office role ?>
    </ul>
    
    <hr>

    <div class="small mt-auto">
        <span class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Logged in as:</span>
        <span class="text-white fw-bold"><?= session()->get('username') ?></span>
        <span class="badge bg-info d-block mt-1"><?= session()->get('role') ?></span>
    </div>
</div>