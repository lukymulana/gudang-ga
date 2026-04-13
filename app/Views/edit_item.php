<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Item: <?= $item['nama_barang'] ?></h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('inventory/update/' . $item['id']) ?>" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="nama_barang" class="form-control" value="<?= $item['nama_barang'] ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category / Warehouse</label>
                                <input type="text" name="kategori" class="form-control" value="<?= $item['kategori'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Brand (Merk)</label>
                                <input type="text" name="merk" class="form-control" value="<?= $item['merk'] ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Current Stock</label>
                                <input type="number" name="stok_aktual" class="form-control" value="<?= $item['stok_aktual'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit Price (IDR)</label>
                                <input type="number" name="harga_satuan" class="form-control" value="<?= $item['harga_satuan'] ?>" required>
                            </div>
                        </div>

                        <div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Min. Stock Warning</label>
        <input type="number" name="min_stok" class="form-control" value="<?= $item['min_stok'] ?>" required>
        <small class="text-muted">Authorized: Super Admin Level</small>
    </div>
</div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= base_url('inventory') ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Update Item Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>