<section class="content-header">
    <h1>Unified Movement Hub <small>Distribusi & Mutasi</small></h1>
</section>

<section class="content">
    <form action="<?= base_url('inventory/process_unified_basket') ?>" method="post" id="movementForm">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-cog me-2"></i>Konfigurasi Utama</h3>
                    </div>
                    <div class="box-body">
                        <?php if(session()->get('role') == 'Super Admin'): ?>
                        <div class="form-group">
                            <label class="text-primary"><i class="fa fa-university"></i> Lokasi Gudang Anda Sekarang</label>
                            <select name="active_warehouse" id="active_warehouse" class="form-control" onchange="toggleInputsByWarehouse()" required>
                                <option value="">-- Pilih Lokasi --</option>
                                <option value="stok_depan">Gudang Depan</option>
                                <option value="stok_belakang">Gudang Belakang</option>
                            </select>
                            <small class="text-muted">Wajib dipilih untuk menentukan sumber stok.</small>
                        </div>
                        <hr>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Jenis Pergerakan</label>
                            <select name="move_type" id="move_type" class="form-control" onchange="toggleDestination()">
                                <option value="distribute">Distribusi ke Karyawan</option>
                                <option value="transfer">Mutasi Antar Gudang</option>
                            </select>
                        </div>

                        <div id="section_karyawan">
                            <div class="form-group">
                                <label>NPK Karyawan</label>
                                <div class="input-group">
                                    <input type="text" name="npk" id="npk" class="form-control" placeholder="Input NPK & Press Enter">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Info Penerima</label>
                                <input type="text" id="display_info" class="form-control" readonly placeholder="Nama | Dept | Sect">
                                <input type="hidden" name="nama_res" id="nama_res">
                                <input type="hidden" name="dept_res" id="dept_res">
                                <input type="hidden" name="sect_res" id="sect_res">
                            </div>
                        </div>

                        <div id="section_transfer" style="display:none;">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle me-2"></i>
                                <strong>Target Otomatis:</strong> 
                                <span id="target_label">
                                <?php 
                                    $role = session()->get('role');
                                    if ($role !== 'Super Admin') {
                                        echo ($role == 'Admin Gudang Depan') ? 'Gudang Belakang' : 'Gudang Depan';
                                    } else {
                                        echo "Pilih Lokasi Terlebih Dahulu";
                                    }
                                ?>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Alasan pergerakan..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="box box-success shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-shopping-basket me-2"></i>Keranjang Barang</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="basketTable">
                                <thead class="bg-gray">
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th width="120px">Ukuran</th>
                                        <th width="100px">Qty</th>
                                        <th width="50px">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="basketBody">
                                    <tr id="emptyRow">
                                        <td colspan="4" class="text-center text-muted">Keranjang masih kosong. Pilih barang untuk memulai.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-success btn-block btn-lg fw-bold shadow-sm">
                            <i class="fa fa-check-circle me-2"></i>KONFIRMASI & PROSES
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<script>
let itemIndex = 0; 

// Changes UI and Logic based on selected Warehouse (Super Admin only)
function toggleInputsByWarehouse() {
    const warehouse = document.getElementById('active_warehouse').value;
    const targetLabel = document.getElementById('target_label');
    
    if(warehouse === 'stok_depan') {
        targetLabel.innerText = 'Gudang Belakang';
    } else if (warehouse === 'stok_belakang') {
        targetLabel.innerText = 'Gudang Depan';
    } else {
        targetLabel.innerText = 'Pilih Lokasi Terlebih Dahulu';
    }
}

function toggleDestination() {
    const type = document.getElementById('move_type').value;
    document.getElementById('section_karyawan').style.display = (type === 'distribute') ? 'block' : 'none';
    document.getElementById('section_transfer').style.display = (type === 'transfer') ? 'block' : 'none';
}

/**
 * STRICT VALIDATION: Validates stock based on selected warehouse
 * @param {number} sd - Stok Depan
 * @param {number} sb - Stok Belakang
 */
function addItemToBasket(id, name, size, sd, sb) {
    const isSuperAdmin = <?= (session()->get('role') == 'Super Admin') ? 'true' : 'false' ?>;
    
    if (isSuperAdmin) {
        const warehouse = document.getElementById('active_warehouse').value;
        if (!warehouse) {
            alert('PERHATIAN: Anda harus memilih "Lokasi Gudang Anda Sekarang" sebelum menambah barang!');
            document.getElementById('active_warehouse').focus();
            return;
        }

        // Logic check for actual stock availability in chosen warehouse
        const stockToCheck = (warehouse === 'stok_depan') ? sd : sb;
        const whLabel = (warehouse === 'stok_depan') ? 'Gudang Depan' : 'Gudang Belakang';

        if (stockToCheck <= 0) {
            alert(`Gagal! Barang "${name}" memiliki 0 stok di ${whLabel}. Silakan pilih gudang lain atau barang lain.`);
            return;
        }
    }

    const emptyRow = document.getElementById('emptyRow');
    if(emptyRow) emptyRow.remove();

    const existing = document.querySelector(`input[value="${id}"][name*="[id]"]`);
    if (existing) {
        alert('Barang ini sudah ada di keranjang!');
        return;
    }

    const table = document.getElementById('basketBody');
    const row = `
        <tr>
            <td>
                <strong>${name}</strong>
                <input type="hidden" name="items[${itemIndex}][id]" value="${id}">
            </td>
            <td>${size}</td>
            <td>
                <input type="number" name="items[${itemIndex}][qty]" class="form-control input-sm" value="1" min="1">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    table.insertAdjacentHTML('beforeend', row);
    itemIndex++;
}

// Employee Search Logic
document.getElementById('npk').addEventListener('change', function() {
    const npk = this.value;
    if(!npk) return;

    fetch(`<?= base_url('inventory/get_employee_info') ?>?npk=${npk}`)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                document.getElementById('display_info').value = `${res.data.nama_karyawan} | ${res.data.department} | ${res.data.section}`;
                document.getElementById('nama_res').value = res.data.nama_karyawan;
                document.getElementById('dept_res').value = res.data.department;
                document.getElementById('sect_res').value = res.data.section; 
            } else {
                alert('Data Karyawan Tidak Ditemukan!');
                this.value = '';
                document.getElementById('display_info').value = '-';
            }
        });
});
</script>