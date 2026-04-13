<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">GA Executive Dashboard</h3>
            <p class="text-muted">Real-time Inventory Intelligence & Operations</p>
        </div>
        <span class="badge bg-light text-dark border p-2">
            <i class="bi bi-calendar3 me-2"></i><?= date('D, d M Y') ?>
        </span>
    </div>

    <div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h6 class="small fw-bold opacity-75 text-uppercase">Total Stock (PCS)</h6>
                <h2 class="mb-1 fw-bold"><?= number_format($total_pcs, 0, ',', '.') ?></h2>
                <div class="mt-1">
                    <span class="badge bg-white bg-opacity-25" style="font-size: 0.7rem;">
                        <?php 
                            $role = session()->get('role'); 
                            // Condition: Office and Super Admin both get the Global View label
                            if(stripos($role, 'Super') !== false || stripos($role, 'Office') !== false) {
                                echo 'GLOBAL VIEW';
                            } elseif(stripos($role, 'Depan') !== false) {
                                echo 'GUDANG DEPAN';
                            } else {
                                echo 'GUDANG BELAKANG';
                            } 
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

        <?php if($role == 'Super Admin'): ?>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="small fw-bold opacity-75 text-uppercase mb-2">Total Harga Asset Gudang</h6>
                            <div class="d-flex justify-content-between small border-bottom border-white border-opacity-25 mb-1 pb-1">
                                <span>Depan:</span> <span>Rp <?= number_format($value_depan, 0, ',', '.') ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Belakang:</span> <span>Rp <?= number_format($value_belakang, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-dark text-white text-center h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h6 class="small fw-bold opacity-75 text-info text-uppercase">Total Harga Asset</h6>
                        <h2 class="mb-0 fw-bold">Rp <?= number_format($total_value, 0, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
        <?php elseif($role == 'Office'): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-white border-start border-info border-4 h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h6 class="small fw-bold text-muted text-uppercase">Pinjaman Aktif Karyawan</h6>
                        <h2 class="mb-0 fw-bold text-info"><?= $active_loans ?> <small class="fs-6 text-muted">Items Out</small></h2>
                        <p class="small text-muted mb-0">Currently assigned to employees</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-white border-start border-info border-4 h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h6 class="small fw-bold text-muted text-uppercase">Pinjaman Aktif Karyawan</h6>
                        <h2 class="mb-0 fw-bold text-info"><?= $active_loans ?> <small class="fs-6 text-muted">Items Out</small></h2>
                        <p class="small text-muted mb-0">Currently assigned to employees</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 <?= $low_stock_count > 0 ? 'bg-danger text-white' : 'bg-light text-muted' ?>">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h6 class="small fw-bold opacity-75">Warning Stok Rendah</h6>
                    <h2 class="mb-0 fw-bold"><?= $low_stock_count ?> <small class="fs-6">Items</small></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="height: 350px;">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Barang Stok Kritis
                </h6>
            </div>
            <div class="card-body p-0" style="height: 295px; overflow-y: auto; overflow-x: hidden;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light sticky-top" style="z-index: 10;">
                            <tr>
                                <th class="ps-3 border-0">Item Name</th>
                                <?php if($role == 'Super Admin' || $role == 'Office' || $role == 'Admin Depan'): ?>
                                    <th class="border-0 text-center">Gudang Depan</th>
                                <?php endif; ?>
                                
                                <?php if($role == 'Super Admin' || $role == 'Office' || $role == 'Admin Belakang'): ?>
                                    <th class="border-0 text-center">Gudang Belakang</th>
                                <?php endif; ?>
                                
                                <?php if($role == 'Super Admin'): ?>
                                    <th class="text-danger text-end pe-3 border-0">Min</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
    <?php if(!empty($low_stock_list)): ?>
        <?php foreach($low_stock_list as $ls): ?>
        <tr>
            <td class="ps-3 fw-bold text-dark"><?= $ls['nama_barang'] ?></td>
            
            <?php if(stripos($role, 'Super') !== false || stripos($role, 'Office') !== false || stripos($role, 'Depan') !== false): ?>
                <td class="text-center <?= ($ls['stok_depan'] <= $ls['min_stok']) ? 'text-danger fw-bold' : '' ?>">
                    <?= $ls['stok_depan'] ?? 0 ?> </td>
            <?php endif; ?>
            
            <?php if(stripos($role, 'Super') !== false || stripos($role, 'Office') !== false || stripos($role, 'Belakang') !== false): ?>
                <td class="text-center <?= ($ls['stok_belakang'] <= $ls['min_stok']) ? 'text-danger fw-bold' : '' ?>">
                    <?= $ls['stok_belakang'] ?? 0 ?> </td>
            <?php endif; ?>
            
            <?php if(stripos($role, 'Super') !== false): ?>
                <td class="text-danger fw-bold text-end pe-3"><?= $ls['min_stok'] ?></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <?php endif; ?>
</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3" style="height: 165px;">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Aktivitas Gudang
                </h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="row w-100 text-center g-0">
                    <div class="col-4 border-end">
                        <h4 class="fw-bold text-success mb-1"><?= count($in_today) ?></h4>
                        <div class="text-muted small fw-bold" style="font-size: 0.65rem;">Barang Masuk</div>
                    </div>
                    <div class="col-4 border-end">
                        <h4 class="fw-bold text-info mb-1"><?= count($out_today) ?></h4>
                        <div class="text-muted small fw-bold" style="font-size: 0.65rem;">Barang Keluar</div>
                    </div>
                    <div class="col-4">
                        <h4 class="fw-bold text-primary mb-1"><?= count($transfers_today) ?></h4>
                        <div class="text-muted small fw-bold" style="font-size: 0.65rem;">Transfer Antar Gudang</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="height: 170px;">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.85rem;">
                    <i class="bi bi-graph-up-arrow me-2 text-primary"></i>Top 5 Konsumabel
                </h6>
            </div>
            <div class="card-body p-0" style="height: 125px; overflow-y: auto; overflow-x: hidden;">
                <ul class="list-group list-group-flush">
                    <?php foreach($top_qty as $index => $tq): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" style="font-size: 0.75rem;">
                        <span><span class="text-muted me-2 small">#<?= $index + 1 ?></span> <?= $tq['nama_barang'] ?></span>
                        <span class="fw-bold text-primary"><?= number_format($tq['total_qty'], 0) ?> Pcs</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

    <div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="height: 350px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-graph-up-arrow me-2 text-success"></i>Trend Konsumsi & Distribusi (Data Historis)
                </h6>
                <span class="badge bg-light text-dark border small">Trend Viewport</span>
            </div>

            <div class="card-body p-0" style="height: 540px; overflow-y: auto; overflow-x: hidden;">
                <div class="p-4">
                    
                    <div style="position: relative; height: 150px; width: 100%;">
                        <canvas id="trendChart"></canvas>
                    </div>

                    <hr class="my-5">

                    <div class="table-responsive">
                        <table class="table table-sm table-hover mt-3">
                            <thead class="table-light sticky-top" style="z-index: 5; top: 0;">
                                <tr>
                                    <th class="ps-3 border-0">Periode (Bulan)</th>
                                    <th class="text-center border-0">Total Volume Keluar</th>
                                    <th class="text-end pe-3 border-0">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($monthly_trend)): ?>
                                    <?php foreach($monthly_trend as $row): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark"><?= $row['bulan'] ?></td>
                                        <td class="text-center"><?= number_format($row['total_qty'], 0) ?> Pcs</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle small">Verified</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="text-muted opacity-50">
                                                <i class="bi bi-folder-x d-block h1 mb-2"></i>
                                                Belum ada data historis yang tercatat.
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white py-2 text-center border-top">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> Data di atas menunjukkan tren 6 bulan terakhir.
                </small>
            </div>
        </div>
    </div>
</div>

    <div class="row g-4 mt-2 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="height: 350px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-water me-2 text-primary"></i>Status Aset Dispenser Sewa</h6>
                <span class="badge bg-primary"><?= $total_dispenser_aktif ?> Aktif</span>
            </div>
            
            <div class="card-body p-0" style="height: 290px; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light sticky-top" style="z-index: 10; top: 0;">
                            <tr class="text-uppercase small">
                                <th class="ps-3 border-0">Lokasi / Area</th>
                                <th class="border-0">ID Asset (SN)</th>
                                <th class="border-0">Tgl Berakhir Sewa</th>
                                <th class="text-end pe-3 border-0">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($dispenser_list)): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">Tidak ada aset dispenser yang terdaftar.</td></tr>
                            <?php else: ?>
                                <?php foreach($dispenser_list as $ds): ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?= $ds['lokasi'] ?></td>
                                    <td><code class="text-dark"><?= $ds['serial_number'] ?></code></td>
                                    <td><?= date('d M Y', strtotime($ds['tgl_berakhir'])) ?></td>
                                    <td class="text-end pe-3">
                                        <?php 
                                        $today = new DateTime();
                                        $end = new DateTime($ds['tgl_berakhir']);
                                        $diff = $today->diff($end)->format("%r%a");
                                        
                                        if($diff < 0): ?>
                                            <span class="badge bg-danger">Expired</span>
                                        <?php elseif($diff <= 30): ?>
                                            <span class="badge bg-warning text-dark">Akan Berakhir</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
    <div class="card border-0 shadow-sm" style="height: 350px;">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2 text-info"></i>Ukuran Distribusi (Aktif)</h6>
        </div>
        
        <div class="card-body" style="height: 290px; overflow-y: auto;">
            <div class="mb-4 p-3 bg-light rounded text-center">
                <small class="text-muted d-block text-uppercase fw-bold">Total Stok Tersedia</small>
                <h3 class="fw-bold text-primary mb-0"><?= number_format($total_pcs, 0, ',', '.') ?> <small class="fs-6">Pcs</small></h3>
            </div>
            
            <div class="list-group list-group-flush">
                <?php foreach($active_size_dist as $sd): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-bold">Size: <?= !empty($sd['ukuran']) ? $sd['ukuran'] : 'N/A' ?></span>
                        <span><?= number_format($sd['total'], 0, ',', '.') ?> Pcs</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: <?= ($total_pcs > 0) ? ($sd['total']/$total_pcs)*100 : 0 ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<div class="row g-4 mt-4">
    <?php if(session()->get('role') == 'Super Admin'): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-top border-success border-4" style="height: 450px;">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart-fill me-2 text-success"></i>Inventory Value by Category</h6>
                </div>
                
                <div class="card-body" style="height: 385px; overflow-y: auto;">
                    <div class="mb-4 p-3 rounded border-start border-success border-3" style="background-color: rgba(25, 135, 84, 0.1);">
                        <small class="text-muted d-block text-uppercase small fw-bold">Distributed Value (MTD)</small>
                        <h4 class="fw-bold text-success mb-0">Rp <?= number_format($monthly_out_value, 0, ',', '.') ?></h4>
                        <p class="small text-muted mb-0">Total cost issued this month</p>
                    </div>

                    <ul class="list-group list-group-flush">
                        <?php foreach($value_per_category as $vc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span class="small fw-bold text-dark"><?= $vc['kategori'] ?></span>
                            <span class="small text-muted">Rp <?= number_format($vc['total_value'], 0, ',', '.') ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="height: 450px;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-gem me-2 text-primary"></i>Top 10 Stock Asset Harga Tinggi</h6>
                    <span class="badge bg-primary rounded-pill">Asset Ranking</span>
                </div>

                <div class="card-body p-0" style="height: 345px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light text-uppercase small sticky-top" style="top: 0; z-index: 10;">
                                <tr>
                                    <th class="ps-3">Item Name</th>
                                    <th class="text-center">Stok</th>
                                    <th class="text-end pe-3">Total Asset Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_10_value_items as $item): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-dark"><?= $item['nama_barang'] ?></td>
                                    <td class="text-center"><?= number_format($item['stok_aktual'], 0) ?></td>
                                    <td class="text-end pe-3 text-primary fw-bold">
                                        Rp <?= number_format($item['asset_value'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer bg-white py-3 border-top">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Ranking based on (Current Stock × Unit Price)</small>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row g-4 mt-2 mb-4 d-flex align-items-stretch">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white text-dark h-100" style="min-height: 350px;">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2 text-info"></i>Tingkat Akurasi Stok</h6>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="mb-2">
                    <h1 class="display-4 fw-bold mb-0 <?= $accuracy_rate < 90 ? 'text-danger' : 'text-dark' ?>">
                        <?= number_format($accuracy_rate, 2) ?>%
                    </h1>
                    <p class="text-muted small text-uppercase tracking-wider">Skor Integritas Inventaris</p>
                </div>
                
                <div class="progress mb-4" style="height: 10px; background-color: #f0f0f0;">
                    <div class="progress-bar <?= $accuracy_rate < 90 ? 'bg-danger' : 'bg-info' ?>" 
                         role="progressbar" 
                         style="width: <?= $accuracy_rate ?>%"></div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-2 rounded bg-light border">
                            <small class="d-block text-muted small">Selisih Depan</small>
                            <span class="fw-bold <?= $selisih_depan != 0 ? 'text-danger' : 'text-success' ?>">
                                <?= ($selisih_depan > 0 ? '+' : '') . $selisih_depan ?> Pcs
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded bg-light border">
                            <small class="d-block text-muted small">Selisih Belakang</small>
                            <span class="fw-bold <?= $selisih_belakang != 0 ? 'text-danger' : 'text-primary' ?>">
                                <?= ($selisih_belakang > 0 ? '+' : '') . $selisih_belakang ?> Pcs
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="height: 350px; overflow: hidden;">
            <div class="card-header bg-white py-3 sticky-top" style="z-index: 10;">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-journal-check me-2 text-primary"></i>Riwayat Opname Stok Terakhir
                </h6>
            </div>
            
            <div class="card-body p-0" style="height: 295px; overflow-y: auto; overflow-x: hidden;">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light sticky-top" style="z-index: 5; top: 0;">
                            <tr class="small text-uppercase">
                                <th class="ps-3">Tanggal</th>
                                <th>Nama Barang</th>
                                <th class="text-center">Sistem</th>
                                <th class="text-center">Fisik</th>
                                <th class="text-end pe-3">Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($history)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Tidak ada data ditemukan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($history as $oh): ?>
                                <tr>
                                    <td class="ps-3 text-muted small"><?= date('d M Y', strtotime($oh['tanggal'])) ?></td>
                                    <td class="fw-bold"><?= $oh['nama_barang'] ?></td>
                                    <td class="text-center text-muted"><?= number_format($oh['sistem_stok'], 0) ?></td>
                                    <td class="text-center fw-bold text-primary"><?= number_format($oh['stok_fisik'], 0) ?></td>
                                    <td class="text-end pe-3">
                                        <?php if($oh['selisih'] == 0): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">Match</span>
                                        <?php else: ?>
                                            <span class="fw-bold <?= $oh['selisih'] < 0 ? 'text-danger' : 'text-warning' ?>">
                                                <?= ($oh['selisih'] > 0 ? '+' : '') . $oh['selisih'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-graph-up me-2 text-success"></i>Trend Akurasi Stok</h6>
            </div>
            <div class="card-body" style="height: 300px;">
                <canvas id="opnameTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-arrow-left-right me-2 text-primary"></i>Analisis Mutasi Stock
                </h6>
                <div class="d-flex gap-2">
                    <select id="filterCategory" class="form-select form-select-sm" style="width: 150px;" onchange="refreshMutationChart()">
                        <option value="All">All Categories</option>
                        <?php if(!empty($categories_list)): ?>
                            <?php foreach($categories_list as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['kategori']) ?>"><?= htmlspecialchars($cat['kategori']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                    <div class="input-group input-group-sm" style="width: 320px;">
                        <span class="input-group-text bg-white"><i class="bi bi-calendar3"></i></span>
                        <input type="date" id="startDate" class="form-control" 
                               onchange="refreshMutationChart()" 
                               value="<?= date('Y-m-d', strtotime('-7 days')) ?>">
                        <span class="input-group-text bg-light text-muted">to</span>
                        <input type="date" id="endDate" class="form-control" 
                               onchange="refreshMutationChart()" 
                               value="<?= date('Y-m-d') ?>">
                    </div>

                    <?php if (session()->get('role') !== 'Office'): ?>
                        <button onclick="exportFilteredData()" class="btn btn-sm btn-success fw-bold">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export
                        </button>
                    <?php endif; ?>
                </div> 
            </div>
            <div class="card-body" style="height: 350px;">
                <canvas id="mutationChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
function exportFilteredData() {
    // Grab exact IDs from your snippet
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    const cat = document.getElementById('filterCategory').value;

    const baseUrl = "<?= base_url('dashboard/export_graph_data') ?>";
    // Construct the URL with parameters
    window.location.href = `${baseUrl}?start=${start}&end=${end}&cat=${cat}`;
}
</script>

<style>
    /* Slim Scrollbar for Professional Look */
    .card-body::-webkit-scrollbar { width: 6px; }
    .card-body::-webkit-scrollbar-track { background: #f1f1f1; }
    .card-body::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
    .card-body::-webkit-scrollbar-thumb:hover { background: #999; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Global variable for the new Mutation chart
    let mutationChartInstance;

    document.addEventListener("DOMContentLoaded", function() {
        // --- EXISTING OPNAME CHART LOGIC ---
        const ctxOpname = document.getElementById('opnameTrendChart').getContext('2d');
        const historyData = <?= json_encode(array_reverse(array_slice($history, 0, 15))) ?>;
        
        new Chart(ctxOpname, {
            type: 'line',
            data: {
                labels: historyData.map(item => new Date(item.tanggal).toLocaleDateString('id-ID', {day: '2-digit', month: 'short'})),
                datasets: [
                    {
                        label: 'Sistem',
                        data: historyData.map(item => item.sistem_stok),
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Fisik',
                        data: historyData.map(item => item.stok_fisik),
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.05)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top', labels: { boxWidth: 12 } }
                }
            }
        });

        // --- NEW MUTATION CHART INITIAL LOAD ---
        refreshMutationChart(); 
    });

    // --- NEW MUTATION CHART FUNCTION ---
    async function refreshMutationChart() {
    // 1. Grab the values from our new Mouse-Friendly inputs
    const cat = document.getElementById('filterCategory').value;
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    
    // 2. We use URLSearchParams to build a clean URL for the fetch request
    const params = new URLSearchParams({
        category: cat,
        start: start,
        end: end
    });

    try {
        const response = await fetch(`<?= base_url('dashboard/getMutationData') ?>?${params.toString()}`);
        const rawData = await response.json();

        // 3. Map the results for the chart axes
        const labels = rawData.map(d => d.tgl);
        const dataIn = rawData.map(d => d.total_in);
        const dataOut = rawData.map(d => d.total_out);

        const ctx = document.getElementById('mutationChart').getContext('2d');
        
        // 4. Destroy previous instance to prevent the "flicker" bug
        if (mutationChartInstance) mutationChartInstance.destroy();

        // 5. Render the "Stock Opname Style" Graph
        mutationChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Distribusi (Out)',
                        data: dataOut,
                        borderColor: '#0d6efd', // BOLDER Blue
                        borderWidth: 4,         
                        pointBackgroundColor: '#0d6efd',
                        tension: 0.4,           
                        fill: false
                    },
                    {
                        label: 'Penerimaan (In)',
                        data: dataIn,
                        borderColor: '#adb5bd', // LIGHTER Gray
                        borderWidth: 2,         
                        borderDash: [5, 5],     // Dashed
                        pointBackgroundColor: '#adb5bd',
                        tension: 0.4,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom' }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true, 
                        ticks: { precision: 0 },
                        grid: { color: '#f8f9fa' }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error fetching mutation data:', error);
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <script>
(function() {
    // Wait for everything to be ready
    window.addEventListener('load', function() {
        var canvas = document.getElementById('trendChart');
        if (!canvas) {
            console.error("Canvas element 'trendChart' not found!");
            return;
        }

        var ctx = canvas.getContext('2d');
        
        // Using the exact variable $monthly_trend from your controller
        var chartData = <?= json_encode(array_reverse($monthly_trend)) ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(row => row.bulan),
                datasets: [{
                    label: 'Total Qty',
                    data: chartData.map(row => row.total_qty),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Forces it to respect our 180px height
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    });
})();
</script>
<?= $this->endSection() ?>