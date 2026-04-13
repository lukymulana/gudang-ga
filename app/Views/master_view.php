<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-shield-check me-2"></i><?= $title ?></h2>
            <p class="text-muted">Adaptive System Master List.</p>
        </div>
        <a href="<?= base_url('inventory') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Inventory
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0" style="height: 500px;"> <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-grid-3x3-gap me-2"></i>Kategori</span>
                    <span class="badge bg-secondary"><?= count($live_categories) ?></span>
                </div>
                <div class="card-body p-0" style="overflow-y: auto; height: 100%;">
                    <ul class="list-group list-group-flush">
                        <?php if(!empty($live_categories)): ?>
                            <?php foreach($live_categories as $cat): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tag-fill me-2 text-primary"></i> <?= esc($cat['kategori']) ?></span>
                                    <div class="text-end">
                                        <span class="badge bg-light text-dark border"><?= $cat['total_items'] ?> Types</span>
                                        <small class="d-block text-muted" style="font-size: 0.7rem;">Stock: <?= number_format($cat['total_stok'] ?? 0) ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center py-4 text-muted small">No categories detected.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0" style="height: 500px;"> <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person-badge me-2"></i>Status Seragam</span>
                    <span class="badge bg-white text-primary"><?= count($live_statuses) ?></span>
                </div>
                <div class="card-body p-0" style="overflow-y: auto; height: 100%;">
                    <ul class="list-group list-group-flush">
                        <?php if(!empty($live_statuses)): ?>
                            <?php foreach($live_statuses as $stat): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-check-circle-fill me-2 text-success"></i> <?= esc($stat['status_seragam']) ?></span>
                                    <span class="badge rounded-pill bg-light text-primary border"><?= $stat['total_items'] ?> Items</span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center py-4 text-muted small">No statuses recorded.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0" style="height: 500px;"> <div class="card-header bg-info text-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-rulers me-2"></i>Ukuran</span>
                    <span class="badge bg-white text-info"><?= count($live_sizes) ?></span>
                </div>
                <div class="card-body p-0" style="overflow-y: auto; height: 100%;">
                    <ul class="list-group list-group-flush">
                        <?php if(!empty($live_sizes)): ?>
                            <?php foreach($live_sizes as $sz): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-aspect-ratio me-2 text-info"></i> <?= esc($sz['ukuran']) ?></span>
                                    <span class="badge rounded-pill bg-light text-info border"><?= $sz['total_items'] ?> Types</span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center py-4 text-muted small">No sizes found.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>