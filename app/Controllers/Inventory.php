<?php

namespace App\Controllers;

require_once APPPATH . 'Models/ItemModel.php';

use App\Models\ItemModel;
use CodeIgniter\Controller;
use App\Models\LogKaryawanModel;

class Inventory extends BaseController
{
    // --- 1. VIEWING: Adaptive dashboard based on Role ---
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('authentication');
        }

        $model = new ItemModel();
        $role = session()->get('role');
        $items = $model->findAll();

        // --- IMPROVED: Initialize empty arrays for dynamic summaries ---
        $data = [
            'items' => $items,
            'role'  => $role,
            'total_value' => 0,
            'summary' => [],           // Dynamic: Will hold Kartap, Kontrak, etc.
            'category_summary' => []   // Dynamic: Will hold Sepatu, ATK, etc.
        ];

        $total_asset_sum = 0;

        foreach ($items as $item) {
            // --- 1. DETERMINE WHICH STOCK TO SHOW IN SUMMARY CARDS ---
            if ($role == 'Super Admin') {
                $display_stock = $item['stok_depan'] + $item['stok_belakang'];
            } elseif ($role == 'Admin Gudang Depan') {
                $display_stock = $item['stok_depan'];
            } else {
                $display_stock = $item['stok_belakang'];
            }

            // --- 2. CALCULATE GLOBAL ASSET VALUE (Always Depan + Belakang) ---
            $global_stock_qty = $item['stok_depan'] + $item['stok_belakang'];
            $total_asset_sum += ($global_stock_qty * $item['harga_satuan']);

            // --- 3. DYNAMIC SUMMARY LOGIC ---
            if ($item['kategori'] === 'Seragam' && !empty($item['status_seragam'])) {
                // If this status doesn't exist in summary yet, create it starting at 0
                if (!isset($data['summary'][$item['status_seragam']])) {
                    $data['summary'][$item['status_seragam']] = 0;
                }
                $data['summary'][$item['status_seragam']] += $display_stock;
            } else {
                // Summarize non-uniform categories (Sepatu, ATK, etc.)
                if (!isset($data['category_summary'][$item['kategori']])) {
                    $data['category_summary'][$item['kategori']] = 0;
                }
                $data['category_summary'][$item['kategori']] += $display_stock;
            }
        }

        // Apply global asset sum to total_value if role is Super Admin
        if ($role == 'Super Admin') {
            $data['total_value'] = $total_asset_sum;
        }

        return view('inventory_view', $data);
    }

   // --- 2. INPUT: Restricted to Super Admin & Admin Gudang Depan Only ---
   public function add()
{
    $role = session()->get('role');
    $model = new ItemModel();
    
    // Ensure Admin Belakang is blocked 
    if (!in_array($role, ['Super Admin', 'Admin Gudang Depan'])) {
        return redirect()->to('inventory')->with('msg', 'Access Denied: Admin Belakang cannot input items.');
    }

    // --- ADAPTIVE DATA FETCHING ---
    // Fetching unique values to feed datalists/dropdowns for the "Many-to-Little" logic
    $data['existing_names']    = $model->distinct()->select('nama_barang')->findAll();
    $data['existing_status']   = $model->distinct()->select('status_seragam')->where('status_seragam !=', null)->findAll();
    $data['existing_sizes']    = $model->distinct()->select('ukuran')->where('ukuran !=', '')->findAll();
    
    // Added: Fetch existing sub-categories for the adaptive filtering
    $data['existing_sub_cats'] = $model->distinct()->select('sub_kategori')->where('sub_kategori !=', '')->findAll();

    // Categories remain locked as per requirement
    $data['categories'] = [
        ['nama_kategori' => 'Consumable'],
        ['nama_kategori' => 'Seragam'],
        ['nama_kategori' => 'Sepatu'],
        ['nama_kategori' => 'Topi'],
        ['nama_kategori' => 'Tas'],     
        ['nama_kategori' => 'Asset']    
    ];
    
    $data['role'] = $role;

    return view('add_item', $data); 
}

public function get_adaptive_stock()
{
    $model = new \App\Models\ItemModel();
    $role = session()->get('role');

    // 1. Collect inputs from AJAX POST
    $cat     = trim($this->request->getPost('category') ?? '');
    $sub     = trim($this->request->getPost('sub_category') ?? '');
    $status  = trim($this->request->getPost('status') ?? ''); 
    $size    = trim($this->request->getPost('size') ?? '');   
    $kondisi = trim($this->request->getPost('kondisi') ?? '');
    $name    = trim($this->request->getPost('name') ?? '');

    $builder = $model->builder();

    // 2. Many-to-Little Filtering (Ignore empty or hyphens)
    if ($cat !== '' && $cat !== '-')     { $builder->where('category', $cat); }
    if ($sub !== '' && $sub !== '-')     { $builder->where('sub_kategori', $sub); }
    if ($status !== '' && $status !== '-') { $builder->where('status_seragam', $status); }
    if ($size !== '' && $size !== '-')   { $builder->where('ukuran', $size); }
    if ($kondisi !== '' && $kondisi !== '-') { $builder->where('kondisi', $kondisi); }
    if ($name !== '' && $name !== '-')   { $builder->where('nama_barang', $name); }

    // 3. Role-Based Summation
    // If Admin Gudang Depan, sum 'stok_depan', else sum 'stok_aktual'
    $column = ($role === 'Admin Gudang Depan') ? 'stok_depan' : 'stok_aktual';
    $result = $builder->selectSum($column)->get()->getRow();
    $totalFound = (int)($result->$column ?? 0);

    return $this->response->setJSON([
        'status' => 'success',
        'current_sum' => $totalFound
    ]);
}
   public function save()
{
    $role = session()->get('role');
    
    if (!in_array($role, ['Super Admin', 'Admin Gudang Depan'])) {
        return redirect()->to('inventory')->with('msg', 'Unauthorized action.');
    }

    $model = new ItemModel();
    
    // 1. Collect and "Auto-Clean" ALL Text Data
    // We combine the raw capture and cleaning into one step per variable to avoid overwrites
    $nama_barang    = ucwords(strtolower(trim($this->request->getPost('nama_barang'))));
    $kategori       = ucwords(strtolower(trim($this->request->getPost('kategori'))));
    $sub_kategori   = ucwords(strtolower(trim($this->request->getPost('sub_kategori'))));
    $warna          = ucwords(strtolower(trim($this->request->getPost('warna'))));
    $status_seragam = ucwords(strtolower(trim($this->request->getPost('status_seragam'))));
    $gender         = $this->request->getPost('gender');
    
    // FIX: Ensure 'ukuran' is cleaned but only assigned once to prevent data loss
    $raw_ukuran     = $this->request->getPost('ukuran');
    $ukuran         = !empty($raw_ukuran) ? strtoupper(trim($raw_ukuran)) : 'N/A';

    // --- UPDATED MERK & KONDISI WITH AUTO-CLEAN ---
    $merk_raw       = $this->request->getPost('merk') ?: $this->request->getPost('merk_custom');
    $merk_input     = !empty($merk_raw) ? ucwords(strtolower(trim($merk_raw))) : 'No Brand';

    $kondisi_raw    = $this->request->getPost('kondisi') ?: $this->request->getPost('kondisi_custom');
    $kondisi_input  = !empty($kondisi_raw) ? ucwords(strtolower(trim($kondisi_raw))) : 'Baru';

    $gudang_tujuan  = $this->request->getPost('gudang') ?: 'Gudang Depan';

    if (empty($nama_barang)) {
        return redirect()->back()->withInput()->with('error', 'Item Name cannot be empty.');
    }
    
    $qty_baru = (int)$this->request->getPost('stok_aktual');
    
    $harga_input = ($role === 'Super Admin') ? ($this->request->getPost('harga_satuan') ?? 0) : null;
    $min_stok_input = ($role === 'Super Admin') ? $this->request->getPost('min_stok') : null;

    // 2. CHECK: Does this exact item exist? (Uses the cleaned $ukuran)
    $existingItem = $model->where([
        'nama_barang'    => $nama_barang,
        'status_seragam' => $status_seragam,
        'ukuran'         => $ukuran,
        'warna'          => $warna,
        'kondisi'        => $kondisi_input,
        'merk'           => $merk_input 
    ])->first();

    if ($existingItem) {
        $current_depan    = (int)($existingItem['stok_depan'] ?? 0);
        $current_belakang = (int)($existingItem['stok_belakang'] ?? 0);

        if ($gudang_tujuan == 'Gudang Depan') {
            $current_depan += $qty_baru;
        } else {
            $current_belakang += $qty_baru;
        }

        $updateData = [
            'stok_depan'    => $current_depan,
            'stok_belakang' => $current_belakang,
            'stok_aktual'   => $current_depan + $current_belakang
        ];

        if ($role === 'Super Admin') {
            if ($harga_input !== null) $updateData['harga_satuan'] = $harga_input;
            if ($min_stok_input !== null) $updateData['min_stok'] = $min_stok_input;
        }

        $model->update($existingItem['id'], $updateData);
        $targetID = $existingItem['id'];
        $log_desc = "Penambahan stok di $gudang_tujuan (Update Existing)";

    } else {
        $val_depan    = ($gudang_tujuan == 'Gudang Depan') ? $qty_baru : 0;
        $val_belakang = ($gudang_tujuan == 'Gudang Belakang') ? $qty_baru : 0;

        $data = [
            'nama_barang'    => $nama_barang,
            'kategori'       => $kategori,
            'sub_kategori'   => $sub_kategori,
            'stok_depan'     => $val_depan,
            'stok_belakang'  => $val_belakang,
            'stok_aktual'    => $val_depan + $val_belakang,
            'min_stok'       => $min_stok_input ?? 5, 
            'harga_satuan'   => $harga_input ?? 0,
            'merk'           => $merk_input,    
            'gudang'         => $gudang_tujuan, 
            'status_seragam' => $status_seragam,
            'gender'         => $gender,
            'ukuran'         => $ukuran,
            'warna'          => $warna,
            'kondisi'        => $kondisi_input, 
        ];

        $model->insert($data);
        $targetID = $model->getInsertID();
        $log_desc = "Penerimaan Barang BARU di $gudang_tujuan";
    }

    $this->log_movement($targetID, $nama_barang, 'Masuk', $qty_baru, $log_desc);
    
    return redirect()->to('inventory')->with('msg', 'Stok berhasil diperbarui!');
}
    
// --- AJAX hook here ---
   public function get_min_stock()
{
    $role = session()->get('role');
    $model = new \App\Models\ItemModel();

    // 1. Collect all potential refining criteria from the Fetch request
    $name     = trim($this->request->getGet('name') ?? '');
    $kategori = trim($this->request->getGet('kategori') ?? '');
    $sub      = trim($this->request->getGet('sub_kategori') ?? '');
    $kondisi  = $this->request->getGet('kondisi');
    
    // Adaptive Criteria
    $status   = $this->request->getGet('status');
    $gender   = $this->request->getGet('gender');
    $size     = $this->request->getGet('size');

    // 2. Start building a Dynamic Query using the builder
    $builder = $model->builder();

    // Base Filters (Name and Category are usually required to start)
    if (!empty($name)) {
        $builder->where('LOWER(nama_barang)', strtolower($name));
    }
    if (!empty($kategori)) {
        $builder->where('LOWER(kategori)', strtolower($kategori));
    }

    // --- ADAPTIVE DRILL-DOWN ---
    // These only apply if the user has actually selected them. 
    // If they are empty or "-", the query ignores them and gives a broader SUM.
    
    if (!empty($sub) && $sub !== '-') {
        $builder->where('LOWER(sub_kategori)', strtolower($sub));
    }
    if ($kondisi && $kondisi !== '-') {
        $builder->where('kondisi', $kondisi);
    }
    if ($status && $status !== '-') {
        $builder->where('status_seragam', $status);
    }
    if ($gender && $gender !== '-') {
        $builder->where('gender', $gender);
    }
    if ($size && $size !== '-') {
        $builder->where('ukuran', $size);
    }

    // 3. THE MAGIC: Sum the stock based on current filters
    $column = ($role === 'Admin Gudang Depan') ? 'stok_depan' : 'stok_aktual';
    
    // SelectSum so if multiple rows match (e.g. all sizes of one shirt), 
    // it gives the total "Adaptive" number.
    $sumResult = $builder->selectSum($column, 'total')->get()->getRow();
    $current_qty = $sumResult->total ?? 0;

    // 4. Check for Existence (For Admin Price/Min Stock fields)
    //  check for the exact item identity to decide if  hide Price/Min Stock inputs
    $itemExists = $model->where('LOWER(nama_barang)', strtolower($name))
                        ->where('LOWER(kategori)', strtolower($kategori))
                        ->first();

    // 5. Return Data
    return $this->response->setJSON([
        'exists'       => !empty($itemExists),
        'min_stok'     => $itemExists ? $itemExists['min_stok'] : 5,
        'harga_satuan' => $itemExists ? $itemExists['harga_satuan'] : 0,
        'stok_aktual'  => $current_qty 
    ]);
}

public function distribusi()
{
    // FIX: Manual load in case Autoloader is glitched by VS Code update
    require_once APPPATH . 'Models/ItemModel.php';
    $model = new \App\Models\ItemModel();
    
    $role = session()->get('role');
    $db = \Config\Database::connect(); 
    
    // Fetch data for the UI
    $data['categories'] = $model->distinct()->select('kategori as nama_kategori')->findAll();
    
    // Fetch Employees
    $data['employees'] = $db->table('employees')
                            ->select('npk, nama_karyawan, department')
                            ->get()
                            ->getResultArray();

    // Restricted items view based on role
    if ($role == 'Admin Gudang Belakang') {
        $items = $model->where('stok_belakang >', 0)->findAll();
    } elseif ($role == 'Admin Gudang Depan') {
        $items = $model->where('stok_depan >', 0)->findAll();
    } else {
        // Super Admin
        $items = $model->where('stok_depan > 0 OR stok_belakang > 0')->findAll();
    }

    $data['all_items'] = $items;
    $data['role'] = $role;
    
    return view('inventory/quick_distribute', $data); 
}

// --- NEW: UNIFIED PROCESSOR (The Brain) ---
public function process_unified_basket()
{
    require_once APPPATH . 'Models/ItemModel.php';
    $model = new \App\Models\ItemModel();
    $db = \Config\Database::connect();

    $items_input = $this->request->getPost('items'); 
    $move_type = $this->request->getPost('move_type'); 
    $role = session()->get('role');
    
    // NEW: Get the global warehouse context for Super Admin
    $active_warehouse = $this->request->getPost('active_warehouse');

    if (empty($items_input)) {
        return redirect()->back()->with('msg', 'Gagal! Keranjang kosong.');
    }

    $db->transStart();

    foreach ($items_input as $entry) {
        $id = $entry['id'];
        $qty = (int)$entry['qty'];
        $item = $model->find($id);

        if (!$item) continue;

        // 1. Determine which column to pull from
        if ($role === 'Super Admin') {
            // Priority 1: Use the new global 'active_warehouse' selector
            // Priority 2: Fallback to 'stok_depan' if somehow empty
            $source_col = !empty($active_warehouse) ? $active_warehouse : 'stok_depan'; 
        } else {
            $source_col = ($role == 'Admin Gudang Belakang') ? 'stok_belakang' : 'stok_depan';
        }
        
        $source_label = ($source_col == 'stok_belakang') ? 'Gudang Belakang' : 'Gudang Depan';

        // 2. Safety Check
        if ($item[$source_col] < $qty) {
            $db->transRollback();
            return redirect()->back()->with('msg', "Stok {$item['nama_barang']} tidak cukup di $source_label.");
        }

        if ($move_type === 'distribute') {
            // --- DISTRIBUTION LOGIC ---
            $model->update($id, [
                $source_col   => $item[$source_col] - $qty,
                'stok_aktual' => ($item['stok_aktual'] ?? 0) - $qty
            ]);

            $raw_kondisi = trim($item['kondisi'] ?? '');
            $isPinjaman = (strcasecmp($raw_kondisi, 'Pinjam') === 0 || strcasecmp($raw_kondisi, 'Pinjaman') === 0);

            // 3. Log to log_karyawan (RAW SQL FIX for "Invalid Column" Error)
            $sql = "INSERT INTO dbo.log_karyawan 
                    (nama_karyawan, npk, department, section, item_id, nama_barang, qty, gudang_sumber, tanggal_ambil, keterangan, status_peminjaman, kondisi) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $queryStatus = $db->query($sql, [
                (string)($this->request->getPost('nama_res') ?: '-'),
                (string)($this->request->getPost('npk') ?: '-'),
                (string)($this->request->getPost('dept_res') ?: '-'),
                (string)($this->request->getPost('sect_res') ?: '-'),
                (int)$id,
                (string)$item['nama_barang'],
                (int)$qty,
                (string)$source_label,
                date('Y-m-d H:i:s'),
                "Unified: " . ($this->request->getPost('keterangan') ?: 'No Description'),
                ($isPinjaman ? 'Dipinjam' : 'Permanen'),
                (string)$raw_kondisi
            ]);

            if (!$queryStatus) {
                $error = $db->error();
                $db->transRollback();
                die("SQL ERROR ON LOG: " . $error['message'] . " | QUERY: " . $db->getLastQuery());
            }

            $log_note = "Distribusi ke NPK " . $this->request->getPost('npk') . " dari " . $source_label;
            $final_action = 'DISTRIBUSI';

        } else {
            // --- MUTASI LOGIC ---
            $target_col = ($source_col == 'stok_depan') ? 'stok_belakang' : 'stok_depan';
            
            $model->update($id, [
                $source_col => $item[$source_col] - $qty,
                $target_col => $item[$target_col] + $qty
            ]);
            $log_note = "Mutasi Internal: $source_label ke " . (($target_col == 'stok_depan') ? 'Gudang Depan' : 'Gudang Belakang');
            $final_action = 'TRANSFER';
        }

        // 4. Update the Master Movement Log
        $this->log_movement($id, $item['nama_barang'], $final_action, $qty, $log_note);
    }

    $db->transComplete();
    
    if ($db->transStatus() === FALSE) {
        $error = $db->error();
        $db->transRollback(); 
        die("Transaction Failed: " . $error['message']);
    }

    return redirect()->to('inventory')->with('msg', 'Transaksi Berhasil & Stok Terupdate!');
}

// public function process_bulk_distribution()
// {
//     $model = new ItemModel();
//     $db = \Config\Database::connect();

//     $items_input = $this->request->getPost('items'); 
//     $npk = $this->request->getPost('npk');
//     $role = session()->get('role');
//     $selected_gudang = $this->request->getPost('gudang_sumber_pilihan'); 

//     if (empty($items_input)) {
//         return redirect()->back()->with('msg', 'Gagal! Keranjang belanja kosong.');
//     }

//     $db->transStart();

//     foreach ($items_input as $entry) {
//         $id = $entry['id'];
//         $qty = (int)$entry['qty'];
//         $item = $model->find($id);

//         // 1. Determine warehouse column (Logic matched to Unified)
//         if ($role == 'Super Admin' && !empty($selected_gudang)) {
//             $column = ($selected_gudang == 'Gudang Belakang') ? 'stok_belakang' : 'stok_depan';
//             $gudang_label = $selected_gudang;
//         } else {
//             $column = ($role == 'Admin Gudang Belakang') ? 'stok_belakang' : 'stok_depan';
//             $gudang_label = ($role == 'Admin Gudang Belakang') ? 'Gudang Belakang' : 'Gudang Depan';
//         }

//         if ($item[$column] < $qty) {
//             $db->transRollback();
//             return redirect()->back()->with('msg', "Gagal! Stok {$item['nama_barang']} tidak cukup.");
//         }

//         // 2. Update Inventory Table
//         $model->update($id, [
//             $column => $item[$column] - $qty,
//             'stok_aktual' => $item['stok_aktual'] - $qty
//         ]);

//         // 3. Normalized Loan Logic (Matches Step 1)
//         $raw_kondisi = trim($item['kondisi'] ?? 'Baru');
//         $is_loan = (strcasecmp($raw_kondisi, 'Pinjam') === 0 || strcasecmp($raw_kondisi, 'Pinjaman') === 0);

//         // 4. Insert into log_karyawan (Using synced column names)
//         $db->table('log_karyawan')->insert([
//             'npk'               => $npk,
//             'nama_karyawan'     => $this->request->getPost('nama_res'),
//             'department'        => $this->request->getPost('dept_res'),
//             'section'           => $this->request->getPost('sect_res'),
//             'item_id'           => $id,
//             'nama_barang'       => $item['nama_barang'],
//             'qty'               => $qty,
//             'gudang_sumber'     => $gudang_label,
//             'keterangan'        => "Bulk Dist: " . $this->request->getPost('keterangan'),
//             'tanggal_ambil'     => date('Y-m-d H:i:s'),
//             'status_peminjaman' => ($is_loan ? 'Dipinjam' : 'Permanen'),
//             'kondisi'           => $raw_kondisi, // Synced to 'kondisi'
//             'tgl_kembali'       => null,
//             'admin_npk'         => session()->get('npk'),
//             'admin_nama'        => session()->get('nama_karyawan')
//         ]);

//         $this->log_movement($id, $item['nama_barang'], 'Keluar', $qty, "Bulk checkout to NPK $npk");
//     }

//     $db->transComplete();

//     if ($db->transStatus() === FALSE) {
//         return redirect()->back()->with('msg', 'Terjadi kesalahan database.');
//     }

//     return redirect()->to('inventory')->with('msg', 'Distribusi multi-item berhasil!');
// }
//     // --- 4. STANDARD DISTRIBUTION & TRANSFER ---
//     public function distribute($id)
//     {
//         $role = session()->get('role');
//         if (!in_array($role, ['Super Admin', 'Admin Gudang Depan', 'Admin Gudang Belakang'])) {
//             return redirect()->to('inventory')->with('msg', 'Access Denied.');
//         }

//         $model = new ItemModel();
//         $data['item'] = $model->find($id);

//         if (!$data['item']) {
//             return redirect()->to('inventory')->with('msg', 'Item not found.');
//         }

//         return view('distribute_item', $data);
//     }

//     public function process_distribution()
// {
//     $model = new ItemModel();
//     $db = \Config\Database::connect();

//     $id = $this->request->getPost('id');
//     $qty_keluar = (int)$this->request->getPost('qty');
//     $role = session()->get('role');
//     $dist_type = $this->request->getPost('dist_type');

//     $item = $model->find($id);
    
//     // Define the label for logging/inserting
//     $column = ($role == 'Admin Gudang Belakang') ? 'stok_belakang' : 'stok_depan';
//     $gudang_label = ($role == 'Admin Gudang Belakang') ? 'Gudang Belakang' : 'Gudang Depan';

//     if ($item[$column] < $qty_keluar) {
//         return redirect()->back()->with('msg', "Error: Stok di $column tidak mencukupi!");
//     }

//     $new_warehouse_qty = $item[$column] - $qty_keluar;
//     $new_total_qty = $item['stok_aktual'] - $qty_keluar;

//     $update_data = [
//         $column => $new_warehouse_qty,
//         'stok_aktual' => $new_total_qty
//     ];

//     $keterangan = $this->request->getPost('keterangan');

//     if ($dist_type == 'Karyawan') {
//         $npk = $this->request->getPost('npk');
//         $raw_keterangan = $keterangan; // Keep original for the log table
//         $keterangan = "[NPK: $npk] " . $keterangan;

//         // --- FIXED LOGIC: Defined missing variables and kept 'kondisi' ---
//         $db->table('log_karyawan')->insert([
//             'npk'               => $npk,
//             'nama_karyawan'     => $this->request->getPost('nama_res'),
//             'department'        => $this->request->getPost('dept_res'),
//             'section'           => $this->request->getPost('sect_res'),
//             'item_id'           => $id,
//             'nama_barang'       => $item['nama_barang'], // Fixed: used $item instead of undefined $log_name
//             'qty'               => $qty_keluar,         // Fixed: used $qty_keluar instead of undefined $qty
//             'gudang_sumber'     => ($role == 'Super Admin') ? "Super Admin ($gudang_label)" : $gudang_label,
//             'keterangan'        => "Dist: " . $raw_keterangan,
//             'tanggal_ambil'     => date('Y-m-d H:i:s'),
            
//             'kondisi'           => $item['kondisi'] ?? 'N/A', 
//             'status_peminjaman' => (isset($item['kondisi']) && strtolower(trim($item['kondisi'])) == 'pinjam' ? 'Dipinjam' : 'Permanen'),
//             'tgl_kembali'       => null,
            
//             'admin_npk'         => session()->get('npk'),
//             'admin_nama'        => session()->get('nama_karyawan')
//         ]);
//     } else {
//         $tujuan = $this->request->getPost('tujuan_gudang');
//         $keterangan = "[TRANSFER KE: $tujuan] " . $keterangan;

//         if ($tujuan == 'Gudang Belakang' && $role == 'Admin Gudang Depan') {
//             $update_data['stok_belakang'] = $item['stok_belakang'] + $qty_keluar;
//             $update_data['stok_aktual'] = $item['stok_aktual'];
//         } elseif ($tujuan == 'Gudang Depan' && $role == 'Admin Gudang Belakang') {
//             $update_data['stok_depan'] = $item['stok_depan'] + $qty_keluar;
//             $update_data['stok_aktual'] = $item['stok_aktual'];
//         }
//     }

//     $model->update($id, $update_data);
//     $this->log_movement($id, $item['nama_barang'], 'Keluar', $qty_keluar, $keterangan);
//     return redirect()->to('inventory')->with('msg', 'Transaksi Berhasil!');
// }

    // --- 4. LOAN & RETURN MANAGEMENT ---

    public function monitoring_pinjam()
{
    $db = \Config\Database::connect();

    // Fetching items marked 'Dipinjam' with no return date (Active Loans)
    $data['loans'] = $db->table('log_karyawan')
        ->where('status_peminjaman', 'Dipinjam') 
        // Using a custom string ensures MSSQL handles the NULL comparison correctly
        ->where('tgl_kembali IS NULL') 
        ->orderBy('tanggal_ambil', 'DESC')
        ->get()->getResultArray();

    return view('inventory/monitoring_pinjam', $data);
}

public function return_item($log_id)
{
    $db = \Config\Database::connect();
    require_once APPPATH . 'Models/ItemModel.php'; // Ensure model is loaded
    $model = new \App\Models\ItemModel();

    // 1. Retrieve the specific log entry
    $log = $db->table('log_karyawan')->where('id', $log_id)->get()->getRowArray();

    // 2. SAFETY CHECK: Does it exist and is it already returned?
    if (!$log) {
        return redirect()->back()->with('msg', 'Data tidak ditemukan.');
    }
    
    if (!empty($log['tgl_kembali'])) {
        return redirect()->back()->with('msg', 'Gagal! Barang ini sudah dikembalikan sebelumnya.');
    }

    $db->transStart();

    $item = $model->find($log['item_id']);
    if (!$item) {
        $db->transRollback();
        return redirect()->back()->with('msg', 'Gagal! Data barang di inventory tidak ditemukan.');
    }

    // 3. RESTORE STOCK (Mirrors the Distribute logic)
    // We check 'gudang_sumber' to return it to the exact shelf it left from
    $isDepan = (strpos($log['gudang_sumber'], 'Depan') !== false);
    $column = $isDepan ? 'stok_depan' : 'stok_belakang';

    $model->update($log['item_id'], [
        $column       => $item[$column] + $log['qty'],
        'stok_aktual' => ($item['stok_aktual'] ?? 0) + $log['qty']
    ]);

    // 4. UPDATE LOG STATUS
    $db->table('log_karyawan')->where('id', $log_id)->update([
        'tgl_kembali'       => date('Y-m-d H:i:s'),
        'status_peminjaman' => 'Kembali'
    ]);

    // 5. PAPER TRAIL (log_movement)
    $this->log_movement(
        $log['item_id'], 
        $log['nama_barang'], 
        'Masuk', 
        $log['qty'], 
        "Retur: " . $log['gudang_sumber'] . " (NPK " . $log['npk'] . ")"
    );

    $db->transComplete();

    if ($db->transStatus() === FALSE) {
        return redirect()->to('inventory/monitoring_pinjam')->with('msg', 'DB Error: Gagal memproses pengembalian.');
    }

    return redirect()->to('inventory/monitoring_pinjam')->with('msg', 'Sukses! Barang dikembalikan ke ' . ($isDepan ? 'Gudang Depan' : 'Gudang Belakang'));
}

    public function reverse_transaction($log_id)
    {
        $db = \Config\Database::connect();
        $model = new \App\Models\ItemModel();
        
        $admin_user = session()->get('username') ?? session()->get('role');
        $reason = $this->request->getPost('alasan');

        $original_log = $db->table('log_karyawan')->where('id', $log_id)->get()->getRowArray();

        if ($original_log) {
            if ($original_log['status_peminjaman'] === 'Reversed') {
                return redirect()->back()->with('msg', 'Transaksi ini sudah pernah di-reverse.');
            }

            $db->transStart();

            $item = $model->find($original_log['item_id']);
            if ($item) {
                // Detect warehouse to ensure stock separation integrity
                $column = (strpos($original_log['gudang_sumber'], 'Depan') !== false) ? 'stok_depan' : 'stok_belakang';

                $model->update($original_log['item_id'], [
                    $column => $item[$column] + $original_log['qty'],
                    'stok_aktual' => $item['stok_aktual'] + $original_log['qty']
                ]);

                // Update original: No Deletion, only status change + Alasan + User Stamp
                $db->table('log_karyawan')->where('id', $log_id)->update([
                    'status_peminjaman' => 'Reversed',
                    'alasan_koreksi'    => $reason,
                    'corrected_by'      => $admin_user,
                    'tgl_kembali'       => date('Y-m-d H:i:s')
                ]);

                // Mechanism Reversal: Create the mirror log
                $this->log_movement(
                    $original_log['item_id'], 
                    $original_log['nama_barang'], 
                    'Masuk', 
                    $original_log['qty'], 
                    "REVERSAL: Koreksi ID #$log_id. Alasan: $reason (Oleh: $admin_user)"
                );
            }

            $db->transComplete();
            return redirect()->back()->with('msg', 'Transaksi berhasil di-reverse.');
        }

        return redirect()->back()->with('msg', 'Data tidak ditemukan.');
    }
    
    // --- 5. HISTORY VIEWS ---

    public function history()
{
    if (session()->get('role') !== 'Super Admin') {
        return redirect()->to('inventory')->with('msg', 'Akses Ditolak.');
    }

    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');
    
    $db = \Config\Database::connect();
    $builder = $db->table('stock_logs');

    // No Joins needed—using your exact SSMS column names
    $builder->select('*');

    if ($start_date && $end_date) {
        $builder->where('created_at >=', $start_date . ' 00:00:00');
        $builder->where('created_at <=', $end_date . ' 23:59:59');
    }

    $data['logs'] = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
    
    // Keeping these for your View's @$_GET['start_date'] logic
    $data['start_date'] = $start_date;
    $data['end_date'] = $end_date;

    // Fixed path: Reverting to the name that worked for you
    return view('history_view', $data);
}

    public function view_log_karyawan()
{
    $db = \Config\Database::connect();
    
    // Capture the dates
    $start_date = $this->request->getGet('start_date');
    $end_date   = $this->request->getGet('end_date');

    $builder = $db->table('log_karyawan l');
    $builder->select('l.*, i.nama_barang, i.merk, i.ukuran, i.warna');
    $builder->join('items i', 'i.id = l.item_id', 'left');

    // --- PASTE STEP 2 HERE ---
    if (!empty($start_date) && !empty($end_date)) {
        // Use CAST for MSSQL to ensure the comparison is DATE-to-DATE
        $builder->where("CAST(l.tanggal_ambil AS DATE) >=", $start_date);
        $builder->where("CAST(l.tanggal_ambil AS DATE) <=", $end_date);
    }
    // -------------------------

    $builder->orderBy('l.tanggal_ambil', 'DESC');
    $logs = $builder->get()->getResultArray();

    return view('inventory/log_karyawan_view', [
        'logs'       => $logs,
        'start_date' => $start_date,
        'end_date'   => $end_date
    ]);
}

    public function employee_history()
{
    // 1. Capture the inputs from your new View filters
    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');
    $npk = $this->request->getGet('npk');

    $db = \Config\Database::connect();
    $builder = $db->table('log_karyawan');

    // 2. Apply Filters only if they are filled
    if ($npk) {
        $builder->where('npk', $npk);
    }

    if ($start_date && $end_date) {
        // Filters between the two dates (inclusive)
        $builder->where('tanggal_ambil >=', $start_date . ' 00:00:00');
        $builder->where('tanggal_ambil <=', $end_date . ' 23:59:59');
    }

    // 3. Get the data and name it 'logs' to match your View
    $data['logs'] = $builder->orderBy('tanggal_ambil', 'DESC')->get()->getResultArray();
    
    // 4. Pass the inputs back so the date fields stay filled after clicking filter
    $data['start_date'] = $start_date;
    $data['end_date'] = $end_date;
    $data['search_npk'] = $npk;

    return view('inventory/employee_history', $data);
}

    // --- 6. EDIT & DELETE: Super Admin Only ---
    public function edit($id)
{
    // Check if the user is a Super Admin
    if (session()->get('role') !== 'Super Admin') {
        return redirect()->to('inventory')->with('msg', 'Access Denied: You do not have permission to edit items.');
    }

    $model = new ItemModel();
    $data['item'] = $model->find($id);

    // If item not found, handle it gracefully
    if (!$data['item']) {
        return redirect()->to('inventory')->with('msg', 'Item not found.');
    }

    return view('edit_item', $data);
}

    public function update($id)
{
    if (session()->get('role') !== 'Super Admin') {
        return redirect()->to('inventory');
    }

    $model = new ItemModel();
    $item = $model->find($id); 

    // Capture the updated data
    $data = [
        'nama_barang'    => $this->request->getPost('nama_barang'),
        'kategori'       => $this->request->getPost('kategori'),
        'status_seragam' => $this->request->getPost('status_seragam'),
        'stok_aktual'    => $this->request->getPost('stok_aktual'),
        'harga_satuan'   => $this->request->getPost('harga_satuan'),
        'min_stok'       => $this->request->getPost('min_stok'), // Now open to all Super Admins
    ];

    if ($model->update($id, $data)) {
    $db = \Config\Database::connect();
    $db->table('stock_logs')->insert([
        'item_id'     => $id, // <--- ADD THIS LINE
        'item_name'   => $data['nama_barang'],
        'action_type' => 'Edit Data',
        'quantity'    => 0,
        'user_name'   => session()->get('role') . " (" . session()->get('nama') . ")",
        'keterangan'  => "Update Detail: " . $data['nama_barang'] . " (Min Stok: " . $data['min_stok'] . ")",
        'created_at'  => date('Y-m-d H:i:s')
    ]);
}

    return redirect()->to('inventory')->with('msg', 'Item updated successfully!');
}

    public function delete($id)
{
    // 1. Security check: Only Super Admin allowed
    if (session()->get('role') !== 'Super Admin') {
        return redirect()->to('inventory')->with('msg', 'Access Denied.');
    }

    $model = new ItemModel();
    $item = $model->find($id); // Find the item so we can log its name and stock count

    if ($item) {
        $db = \Config\Database::connect();
        
        // 2. LOG THE DELETION BEFORE REMOVING
        $db = \Config\Database::connect();
    
    $db->table('stock_logs')->insert([
        'item_id'     => $id, // <--- ADD THIS LINE
        'item_name'   => $item['nama_barang'],
        'action_type' => 'Hapus Barang',
        'quantity'    => $item['stok_aktual'] ?? 0,
        'user_name'   => session()->get('role') . " (" . session()->get('nama') . ")",
        'keterangan'  => "Barang dihapus permanen dari sistem oleh Super Admin.",
        'created_at'  => date('Y-m-d H:i:s')
    ]);

        // 3. Perform the actual delete
        $model->delete($id);
        return redirect()->to('inventory')->with('msg', 'Item deleted and logged successfully!');
    }

    return redirect()->to('inventory')->with('msg', 'Item not found.');
}

    // --- 7. AJAX: Employee Lookup ---
    public function get_employee_info()
    {
        $npk = trim($this->request->getGet('npk'));
        $db = \Config\Database::connect();

        if (!$db->tableExists('employees')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Table employees missing.']);
        }

        $employee = $db->table('employees')->where('npk', $npk)->get()->getRowArray();

        if ($employee) {
            return $this->response->setJSON(['success' => true, 'data' => $employee]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => "NPK [$npk] not found."]);
        }
    }

    // --- 8. PRIVATE HELPERS ---
    // --- 8. PRIVATE HELPERS (UPDATED FOR ISO/GCG AUDIT) ---
private function log_movement($itemId, $itemName, $type, $qty, $note)
{
    $db = \Config\Database::connect();
    
    // Capture Admin Identity from Session (as we discussed)
    $npk_admin  = session()->get('npk'); 
    $nama_admin = session()->get('nama_karyawan'); 
    $ip_address = $this->request->getIPAddress();

    $db->table('stock_logs')->insert([
        'item_id'     => $itemId,
        'item_name'   => $itemName,
        'action_type' => $type,
        'quantity'    => $qty,
        'npk_admin'   => $npk_admin,   // ISO Requirement
        'nama_admin'  => $nama_admin,  // ISO Requirement
        'user_name'   => session()->get('username'), 
        'ip_address'  => $ip_address,  // GCG Requirement
        'keterangan'  => $note,
        'created_at'  => date('Y-m-d H:i:s')
    ]);
}

    // --- 1. VIEW FOR TRANSFER ---
    // This loads the transfer page and sends the item list
    public function transfer()
    {
        $model = new ItemModel();
        
        // Fetch items that have stock in either warehouse for the dropdown
        $data['items'] = $model->where('(stok_depan + stok_belakang) >', 0)->findAll();
        
        // --- ADDED FIX: Pass role from session to view ---
        $data['role'] = session()->get('role');
        
        return view('inventory/transfer_stock', $data);
    }

    // --- 2. NEW: INTERNAL TRANSFER LOGIC (GUDANG DEPAN <-> BELAKANG) ---
    public function process_transfer()
{
    $model = new ItemModel();
    $db = \Config\Database::connect();

    // 1. Capture Form Inputs
    $id = $this->request->getPost('item_id');
    $qty = (int)$this->request->getPost('qty');
    $asal = $this->request->getPost('gudang_asal');
    $tujuan = $this->request->getPost('gudang_tujuan');

    $item = $model->find($id);

    // 2. Determine Columns (Mapping "Gudang Depan" -> stok_depan, etc)
    $source_col = ($asal === 'Gudang Depan') ? 'stok_depan' : 'stok_belakang';
    $target_col = ($tujuan === 'Gudang Depan') ? 'stok_depan' : 'stok_belakang';

    // 3. Validation
    if (!$item) {
        return redirect()->back()->with('msg', 'Barang tidak ditemukan.');
    }

    if ($asal === $tujuan) {
        return redirect()->back()->with('msg', "Gudang asal dan tujuan tidak boleh sama.");
    }

    if ($item[$source_col] < $qty) {
        return redirect()->back()->with('msg', "Gagal! Stok di $asal tidak cukup (Tersedia: {$item[$source_col]}).");
    }

    $db->transStart();

    // 4. Update Warehouse Columns ONLY (stok_aktual remains same)
    // We use the current values from the $item array to calculate new totals
    $update_data = [
        $source_col => $item[$source_col] - $qty,
        $target_col => $item[$target_col] + $qty,
    ];

    $model->update($id, $update_data);

    // 5. Create Movement Log for Audit Trail
    $log_desc = "Mutasi internal: $qty pcs dipindahkan dari $asal ke $tujuan";
    
    // Using your existing log_movement function
    $this->log_movement($id, $item['nama_barang'], 'Transfer', $qty, $log_desc);

    $db->transComplete();

    if ($db->transStatus() === FALSE) {
        return redirect()->back()->with('msg', 'Terjadi kesalahan sistem saat transfer.');
    }

    return redirect()->to('inventory')->with('msg', "Transfer berhasil: $qty unit dipindahkan dari $asal ke $tujuan.");
}

public function export_excel_log()
{
    // --- SECURITY GATE: Updated to check for 'Super Admin' string ---
    if (session()->get('role') != 'Super Admin') { 
        return redirect()->to(base_url('inventory/view_log_karyawan'))
                         ->with('error', 'Unauthorized access: Only Super Admin can export logs.');
    }

    // Ensure all output buffers are cleared to prevent "corrupt file" errors
    while (ob_get_level()) {
        ob_end_clean();
    }

    $db = \Config\Database::connect();
    
    // 1. Get dates from the URL
    $start = $this->request->getGet('start_date');
    $end   = $this->request->getGet('end_date');

    $builder = $db->table('log_karyawan l');
    $builder->select('l.*, i.nama_barang, i.merk, i.ukuran, i.warna');
    $builder->join('items i', 'i.id = l.item_id', 'left');

    // 2. Apply Date Filter (Only if both dates are provided)
    if (!empty($start) && !empty($end)) {
        $builder->where("l.tanggal_ambil >=", $start . " 00:00:00");
        $builder->where("l.tanggal_ambil <=", $end . " 23:59:59");
    }
    
    $builder->orderBy('l.tanggal_ambil', 'DESC');
    $data = $builder->get()->getResultArray();

    // 3. Set the filename based on the choice
    $dateLabel = (!empty($start)) ? "_{$start}_to_{$end}" : "_AllTime";
    $filename  = "Log_Gudang_GA" . $dateLabel . ".xls";

    // Standard headers for Excel spoofing
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // 4. Output the Table
    echo '<table border="1">
            <tr style="background-color: #198754; color: #ffffff;">
                <th>No</th>
                <th>Tanggal</th>
                <th>Karyawan</th>
                <th>Barang</th>
                <th>Merk</th>
                <th>Qty</th>
                <th>Kondisi</th>
            </tr>';

    if (empty($data)) {
        echo '<tr><td colspan="7" style="text-align:center;">No data found for these dates.</td></tr>';
    } else {
        $no = 1;
        foreach ($data as $row) {
            echo '<tr>
                    <td>' . $no++ . '</td>
                    <td>' . date('d/m/Y', strtotime($row['tanggal_ambil'])) . '</td>
                    <td>' . ($row['nama_karyawan'] ?? '-') . '</td>
                    <td>' . ($row['nama_barang'] ?? '-') . '</td>
                    <td>' . ($row['merk'] ?: '-') . '</td>
                    <td>' . $row['qty'] . '</td>
                    <td>' . ($row['kondisi'] ?? '-') . '</td>
                  </tr>';
        }
    }
    echo '</table>';
    exit();
}

public function export_excel_stock_logs()
{
    // --- SECURITY GATE ---
    if (session()->get('role') != 'Super Admin') { 
        return redirect()->back()->with('error', 'Unauthorized access.');
    }

    // Bersihkan buffer agar file tidak corrupt
    while (ob_get_level()) {
        ob_end_clean();
    }

    $db = \Config\Database::connect();
    
    // 1. Ambil Filter Tanggal
    $start = $this->request->getGet('start_date');
    $end   = $this->request->getGet('end_date');

    $builder = $db->table('stock_logs');
    
    // 2. Filter Berdasarkan Tanggal (created_at)
    if (!empty($start) && !empty($end)) {
        $builder->where("created_at >=", $start . " 00:00:00");
        $builder->where("created_at <=", $end . " 23:59:59");
    }
    
    $builder->orderBy('created_at', 'DESC');
    $data = $builder->get()->getResultArray();

    // 3. Nama File
    $dateLabel = (!empty($start)) ? "_{$start}_to_{$end}" : "_Semua_Waktu";
    $filename  = "Laporan_Aktivitas_Stok" . $dateLabel . ".xls";

    // 4. Header Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // 5. Output Tabel berdasarkan kolom stock_logs
    echo '<table border="1">
            <tr style="background-color: #0d6efd; color: #ffffff;">
                <th>No</th>
                <th>Waktu</th>
                <th>Nama Barang</th>
                <th>Tipe Aksi</th>
                <th>Qty</th>
                <th>User/Admin</th>
                <th>Keterangan</th>
            </tr>';

    if (!empty($data)) {
        $no = 1;
        foreach ($data as $row) {
            echo '<tr>
                    <td>' . $no++ . '</td>
                    <td>' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</td>
                    <td>' . ($row['item_name'] ?? '-') . '</td>
                    <td>' . ($row['action_type'] ?? '-') . '</td>
                    <td>' . $row['quantity'] . '</td>
                    <td>' . ($row['user_name'] ?? '-') . '</td>
                    <td>' . ($row['keterangan'] ?? '-') . '</td>
                  </tr>';
        }
    } else {
        echo '<tr><td colspan="7" style="text-align:center;">Data log stok tidak ditemukan.</td></tr>';
    }
    echo '</table>';
    exit();
}
}