<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm rounded">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= base_url('inventory') ?>">
            <strong>Gudang GA</strong>
        </a>
        <div class="d-flex align-items-center">
            <span class="badge bg-secondary me-3 p-2">
                <i class="bi bi-person-fill"></i> 
                User: <?= session()->get('username') ?> (<?= $role ?>)
            </span>
            <a href="<?= base_url('logout') ?>" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<?php if (session()->getFlashdata('msg')): ?>
    <?php 
        $isError = preg_match('/(Gagal|kesalahan|tidak boleh)/i', session()->getFlashdata('msg'));
        $alertClass = $isError ? 'danger' : 'success';
        $iconClass = $isError ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill';
    ?>
    <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi <?= $iconClass ?> fs-4 me-3"></i>
            <div>
                <?= session()->getFlashdata('msg') ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-alert="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<h5 class="mb-3 text-muted fw-bold"><i class="bi bi-person-badge me-2"></i>Status Stok Seragam </h5>
<div class="row g-3 mb-4">
    <?php 
    $status_config = [
        'Kartap'  => ['color' => 'primary', 'icon' => 'bi-person-check-fill'],
        'Kontrak' => ['color' => 'success', 'icon' => 'bi-file-earmark-text-fill'],
        'Vokasi'  => ['color' => 'warning', 'icon' => 'bi-mortarboard-fill'],
        'Magang'  => ['color' => 'info',    'icon' => 'bi-person-badge-fill']
    ];
    ?>

    <?php if (!empty($summary)): ?>
        <?php foreach($summary as $status => $total): 
            $config = $status_config[$status] ?? ['color' => 'secondary', 'icon' => 'bi-plus-circle-dotted'];
        ?>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-<?= $config['color'] ?> h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="text-muted small text-uppercase fw-bold mb-0">Stok <?= $status ?></h6>
                        <i class="bi <?= $config['icon'] ?> text-<?= $config['color'] ?> opacity-50"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark"><?= number_format($total) ?> <small class="text-muted h6">pcs</small></h3>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><p class="text-muted italic">Tidak ada data seragam tersedia.</p></div>
    <?php endif; ?>
</div>

<h5 class="mb-3 text-muted fw-bold"><i class="bi bi-box-seam me-2"></i>Kategori Lainnya</h5>
<div class="row g-3 mb-4">
    <?php foreach($category_summary as $catName => $catTotal): ?>
    <div class="col-md-2">
        <div class="card shadow-sm border-0 bg-light h-100">
            <div class="card-body p-2 text-center">
                <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.65rem;"><?= $catName ?></h6>
                <h4 class="fw-bold mb-0 text-dark"><?= number_format($catTotal) ?></h4>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($role == 'Super Admin'): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-white shadow-sm border-0 overflow-hidden">
            <div class="card-body p-0 d-flex align-items-center">
                <div class="bg-primary p-4 text-white"><i class="bi bi-wallet2 h2"></i></div>
                <div class="ps-4">
                    <p class="text-uppercase small text-muted fw-bold mb-0">Total Estimasi Nilai Inventory (Global)</p>
                    <h2 class="fw-bold mb-0 text-primary">Rp <?= number_format($total_value, 0, ',', '.') ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<hr class="my-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Portal Distribusi GA</h2>
        <?php if (in_array($role, ['Super Admin', 'Admin Gudang Depan'])): ?>
            <a href="<?= base_url('inventory/add') ?>" class="btn btn-primary shadow-sm mt-2">
                <i class="bi bi-plus-lg"></i> Input Barang Datang
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Kondisi</th> <?php if ($role == 'Super Admin' || $role == 'Admin Gudang Depan'): ?>
                        <th>Stock Depan</th>
                    <?php endif; ?>

                    <?php if ($role == 'Super Admin' || $role == 'Admin Gudang Belakang'): ?>
                        <th>Stock Belakang</th>
                    <?php endif; ?>

                    <?php if ($role == 'Super Admin'): ?>
                        <th>Total Stock</th>
                        <th>Harga</th>
                    <?php endif; ?>

                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <strong><?= $item['nama_barang'] ?></strong>
                        <br>
                        <small class="text-muted">
                            <?= $item['gender'] ?? '-' ?> | 
                            <?= $item['ukuran'] ?? '-' ?> | 
                            <?= $item['merk'] ?? '-' ?> | 
                            <span class="text-primary"><?= $item['warna'] ?? '-' ?></span>
                            <?php if(!empty($item['status_seragam'])): ?>
                                | <span class="badge bg-light text-dark border"><?= $item['status_seragam'] ?></span>
                            <?php endif; ?>
                        </small>
                    </td>
                    <td><?= $item['kategori'] ?></td>
                    <td>
    <?php 
        $cond = strtolower(trim($item['kondisi'] ?? ''));
        // Updated to match 'pinjaman' to align with your controller and DB
        if($cond == 'pinjaman' || $cond == 'pinjam'): 
    ?>
        <span class="badge bg-warning text-dark">
            <i class="bi bi-hourglass-split"></i> Pinjaman
        </span>
    <?php elseif($cond == 'bekas'): ?>
        <span class="badge bg-secondary">Bekas</span>
    <?php elseif($cond == 'servis'): ?>
        <span class="badge bg-info">Servis</span>
    <?php else: ?>
        <span class="badge bg-success">Baru</span>
    <?php endif; ?>
</td>
                    

                    <?php if ($role == 'Super Admin' || $role == 'Admin Gudang Depan'): ?>
                        <td><?= $item['stok_depan'] ?></td>
                    <?php endif; ?>

                    <?php if ($role == 'Super Admin' || $role == 'Admin Gudang Belakang'): ?>
                        <td><?= $item['stok_belakang'] ?></td>
                    <?php endif; ?>

                    <?php if ($role == 'Super Admin'): ?>
                        <td>
                            <?php if ($item['stok_aktual'] <= ($item['min_stok'] ?? 0)): ?>
                                <span class="badge bg-danger"><?= $item['stok_aktual'] ?> (Low)</span>
                            <?php else: ?>
                                <strong><?= $item['stok_aktual'] ?></strong>
                            <?php endif; ?>
                        </td>
                        <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                    <?php endif; ?>

                   <td class="text-center">
                   <div class="btn-group shadow-sm">
                        <a href="<?= base_url('inventory/distribusi') ?>" class="btn btn-sm btn-success">
                           <i class="bi bi-lightning-charge-fill me-1"></i> Distribute
                        </a>

                            <?php if ($role == 'Super Admin'): ?>
                                <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('inventory/edit/'.$item['id']) ?>">
                                            <i class="bi bi-pencil me-2"></i> Edit Data
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="<?= base_url('inventory/delete/'.$item['id']) ?>" onclick="return confirm('Hapus barang ini?')">
                                            <i class="bi bi-trash me-2"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>