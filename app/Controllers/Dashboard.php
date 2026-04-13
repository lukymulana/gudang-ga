<?php

namespace App\Controllers;

use App\Models\ItemModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('login');
        }

        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        $role = session()->get('role'); 

        // 1. Flexible Role Detection (Case-Insensitive)
        if (stripos($role, 'Super') !== false) {
            $targetCol = 'stok_aktual';
        } elseif (stripos($role, 'Depan') !== false) {
            $targetCol = 'stok_depan';
        } elseif (stripos($role, 'Belakang') !== false) {
            $targetCol = 'stok_belakang';
        } else {
            $targetCol = 'stok_aktual';
        }

        // --- NEW ADAPTIVE OPNAME LOGIC START ---
        $opnameModel = new \App\Models\OpnameModel();
        $data['history'] = $opnameModel->orderBy('tanggal', 'DESC')->findAll(20); 
        $data['latest_opname'] = !empty($data['history']) ? $data['history'][0] : null;
        // --- NEW ADAPTIVE OPNAME LOGIC END ---

        if (stripos($role, 'Super') !== false) {
            $data['warehouse_name'] = 'SEMUA GUDANG (PUSAT)';
        } elseif (stripos($role, 'Depan') !== false) {
            $data['warehouse_name'] = 'GUDANG DEPAN';
        } else {
            $data['warehouse_name'] = 'GUDANG BELAKANG';
        }

        // --- SECTION A: HEADLINE KPIs (UPDATED LOGIC) ---
        if (stripos($role, 'Super') !== false) {
            $data['total_pcs'] = $db->table('items')->selectSum('stok_aktual')->get()->getRow()->stok_aktual ?? 0;
            $data['total_value'] = $db->query("SELECT SUM(stok_aktual * harga_satuan) as total FROM items")->getRow()->total ?? 0;
            $data['value_depan'] = $db->query("SELECT SUM(stok_depan * harga_satuan) as total FROM items")->getRow()->total ?? 0;
            $data['value_belakang'] = $db->query("SELECT SUM(stok_belakang * harga_satuan) as total FROM items")->getRow()->total ?? 0;
        } else {
            $query = $db->table('items')->selectSum($targetCol, 'total_sum')->get()->getRow();
            $data['total_pcs'] = $query->total_sum ?? 0;
            // Strict reset for non-super admins
            $data['total_value'] = 0; 
            $data['value_depan'] = 0;
            $data['value_belakang'] = 0;
        }
        
        // --- SECTION B: MONITORING ---
$isSuper = (stripos($role, 'Super') !== false);
$isOffice = (stripos($role, 'Office') !== false);

if ($isSuper || $isOffice) {
    // Global View: Check if any warehouse is low
    $builder = $db->table('items');
    $builder->groupStart()
                ->where("stok_depan <= min_stok")
                ->orWhere("stok_belakang <= min_stok")
                ->orWhere("stok_aktual <= min_stok")
            ->groupEnd();
    
    $data['low_stock_count'] = $builder->countAllResults(false); 
    $data['low_stock_list'] = $builder->get()->getResultArray();
} else {
    // Local View: Only show items where THEIR specific warehouse is low
    // $targetCol is already defined as 'stok_depan' or 'stok_belakang' based on role
    $data['low_stock_count'] = $db->table('items')->where("$targetCol <= min_stok")->countAllResults(false);
    $data['low_stock_list'] = $db->table('items')->where("$targetCol <= min_stok")->get()->getResultArray();
}

    $startOfDay = $today . ' 00:00:00';
    $endOfDay   = $today . ' 23:59:59';

        $data['in_today'] = $db->table('stock_logs')
            ->where("created_at >=", $startOfDay)
            ->where("created_at <=", $endOfDay)
            ->groupStart()
                ->where("LTRIM(RTRIM(UPPER(action_type))) =", 'MASUK')
                ->orWhere("LTRIM(RTRIM(UPPER(action_type))) =", 'TAMBAH')
            ->groupEnd()
            ->get()->getResultArray();

        $data['out_today'] = $db->table('log_karyawan')
            ->where("tanggal_ambil >=", $startOfDay)
            ->where("tanggal_ambil <=", $endOfDay)
            ->where('status_peminjaman !=', 'Reversed')
            ->get()->getResultArray();

            // --- FIXED: Strict Transfer Counter ---
        $data['transfers_today'] = $db->table('stock_logs')
            ->where("created_at >=", $startOfDay)
            ->where("created_at <=", $endOfDay)
            ->groupStart()
                ->where("LTRIM(RTRIM(UPPER(action_type))) =", 'TRANSFER')
                ->orWhere("LTRIM(RTRIM(UPPER(action_type))) =", 'PINDAH')
            ->groupEnd()
            // Only count if it's NOT a distribution (which goes to log_karyawan)
            // Real transfers usually have a warehouse name in the description
            ->groupStart()
                ->where("keterangan LIKE", "%Gudang%")
                ->orWhere("keterangan LIKE", "%Ke %")
            ->groupEnd()
            ->get()->getResultArray();

        // --- SECTION C: CONSUMABLE INSIGHT ---
        $data['top_qty'] = $db->table('log_karyawan l')
            ->select('l.nama_barang, SUM(l.qty) as total_qty')
            ->join('items i', 'l.item_id = i.id')
            ->where('i.kategori', 'Consumable')
            ->where('l.status_peminjaman !=', 'Reversed')
            ->groupBy('l.nama_barang')
            ->orderBy('total_qty', 'DESC')
            ->limit(5)->get()->getResultArray();

        $data['top_value'] = $db->table('log_karyawan l')
            ->select('l.nama_barang, SUM(l.qty * i.harga_satuan) as total_rp')
            ->join('items i', 'l.item_id = i.id')
            ->where('i.kategori', 'Consumable')
            ->where('l.status_peminjaman !=', 'Reversed')
            ->groupBy('l.nama_barang')
            ->orderBy('total_rp', 'DESC')
            ->limit(5)->get()->getResultArray();

        $data['monthly_trend'] = $db->query("
            SELECT TOP 6
                FORMAT(tanggal_ambil, 'MMM yyyy') as bulan,
                SUM(qty) as total_qty
            FROM log_karyawan
            WHERE status_peminjaman != 'Reversed'
            GROUP BY FORMAT(tanggal_ambil, 'MMM yyyy'), YEAR(tanggal_ambil), MONTH(tanggal_ambil)
            ORDER BY YEAR(tanggal_ambil) DESC, MONTH(tanggal_ambil) DESC
        ")->getResultArray();

        // --- SECTION D: SERAGAM & LOANS ---
        $data['total_seragam_edar'] = $db->table('log_karyawan l')
            ->join('items i', 'l.item_id = i.id')
            ->where('i.kategori', 'Seragam')
            ->where('l.status_peminjaman !=', 'Reversed')
            ->selectSum('l.qty')
            ->get()->getRow()->qty ?? 0;

        $data['active_loans'] = $db->table('log_karyawan')->where('status_peminjaman', 'Dipinjam')->where('tgl_kembali', null)->countAllResults();
        $data['loan_list'] = $db->table('log_karyawan')->where('status_peminjaman', 'Dipinjam')->where('tgl_kembali', null)->orderBy('tanggal_ambil', 'ASC')->get()->getResultArray();

        // History-based size distribution (Keep as is)
        $data['size_dist'] = $db->table('log_karyawan l')->select('i.ukuran, SUM(l.qty) as total')->join('items i', 'l.item_id = i.id')->whereIn('i.kategori', ['Seragam', 'Sepatu'])->where('l.status_peminjaman !=', 'Reversed')->groupBy('i.ukuran')->orderBy('total', 'DESC')->get()->getResultArray();

        // NEW: Active Stock Distribution (The fix for your "N/A" and missing size issue)
        $builderSize = $db->table('items');
        $builderSize->select("ukuran, SUM($targetCol) as total"); // Uses the adaptive $targetCol
        $builderSize->where("$targetCol >", 0);
        
        // --- STRIKT FILTERS FOR MSSQL ---
        $builderSize->where('ukuran IS NOT NULL');
        $builderSize->where("ukuran != ''");
        // This handles N/A and the dash you found, keeping the window clean
        $builderSize->whereNotIn("UPPER(ukuran)", ['N/A', 'NA', '-', '--']); 
        // --------------------------------

        $builderSize->groupBy('ukuran');
        $builderSize->orderBy('total', 'DESC');
        $data['active_size_dist'] = $builderSize->get()->getResultArray();

        // --- SECTION G: ASSET DISPENSER SEWA ---
        $data['total_dispenser_aktif'] = $db->table('assets_dispenser')->where('status', 'Aktif')->countAllResults();
        $data['dispenser_list'] = $db->table('assets_dispenser')->orderBy('tgl_berakhir', 'ASC')->limit(10)->get()->getResultArray();

        // --- SECTION E: INVENTORY VALUE & COST CONTROL (RESTRICTED) ---
        if (stripos($role, 'Super') !== false) {
            $data['value_per_category'] = $db->table('items')->select("kategori, SUM($targetCol * harga_satuan) as total_value")->groupBy('kategori')->orderBy('total_value', 'DESC')->get()->getResultArray();
            $data['monthly_out_value'] = $db->table('log_karyawan l')->join('items i', 'l.item_id = i.id')->where("FORMAT(l.tanggal_ambil, 'yyyy-MM') =", $thisMonth)->where('l.status_peminjaman !=', 'Reversed')->select('SUM(l.qty * i.harga_satuan) as total')->get()->getRow()->total ?? 0;
            $data['top_10_value_items'] = $db->table('items')
                ->select("nama_barang, ($targetCol * harga_satuan) as asset_value, $targetCol as stok_display, $targetCol as stok_aktual")
                ->orderBy('asset_value', 'DESC')
                ->limit(10)
                ->get()->getResultArray();
        } else {
            $data['value_per_category'] = [];
            $data['monthly_out_value'] = 0;
            $data['top_10_value_items'] = [];
        }

        // --- SECTION F: REAL-TIME LATEST SESSION & VARIANCE ---
        $latestAudit = $db->table('stok_opname_log')->select('tanggal')->orderBy('tanggal', 'DESC')->limit(1)->get()->getRow();

        if ($latestAudit) {
            $lastTimestamp = $latestAudit->tanggal;
            $sessionTotal = $db->table('stok_opname_log')->where('tanggal', $lastTimestamp)->countAllResults();
            $sessionPerfect = $db->table('stok_opname_log')->where('tanggal', $lastTimestamp)->where('selisih', 0)->countAllResults();
            $data['accuracy_rate'] = ($sessionTotal > 0) ? ($sessionPerfect / $sessionTotal) * 100 : 100;

            if (stripos($role, 'Super') !== false) {
                $data['selisih_depan'] = $db->query("SELECT SUM(ABS(selisih)) as total FROM stok_opname_log WHERE tanggal = '$lastTimestamp' AND gudang = 'DEPAN'")->getRow()->total ?? 0;
                $data['selisih_belakang'] = $db->query("SELECT SUM(ABS(selisih)) as total FROM stok_opname_log WHERE tanggal = '$lastTimestamp' AND gudang = 'BELAKANG'")->getRow()->total ?? 0;
            } else {
                $data['selisih_depan'] = 0;
                $data['selisih_belakang'] = 0;
            }
        } else {
            $data['accuracy_rate'] = 100;
            $data['selisih_depan'] = 0;
            $data['selisih_belakang'] = 0;
        }

        $data['opname_history'] = $db->table('stok_opname_log')->orderBy('tanggal', 'DESC')->limit(5)->get()->getResultArray();
        $data['recent_discrepancy'] = $db->table('stok_opname_log')->where('selisih !=', 0)->where('tanggal >=', date('Y-m-d', strtotime('-7 days')))->countAllResults();
        $data['unusual_usage'] = $db->table('log_karyawan')->where('qty >', 100)->where('status_peminjaman !=', 'Reversed')->where('tanggal_ambil >=', date('Y-m-d', strtotime('-2 days')))->get()->getResultArray();

        // --- NEW: DYNAMIC CATEGORY LIST FOR MUTATION GRAPH ---
        // This ensures every category in your 'items' table appears in the filter automatically
        $data['categories_list'] = $db->table('items')
            ->select('kategori')
            ->distinct()
            ->where('kategori IS NOT NULL')
            ->where("kategori != ''")
            ->orderBy('kategori', 'ASC')
            ->get()->getResultArray();

        return view('inventory/dashboard', $data);
    }

    public function getMutationData()
{
    $db = \Config\Database::connect();
    
    // 1. Capture the new Date variables from the AJAX call
    $category = $this->request->getGet('category') ?: 'All';
    $start = $this->request->getGet('start');
    $end = $this->request->getGet('end');

    $builder = $db->table('stock_logs sl');
    
    // 2. Select the data
    $builder->select("CONVERT(DATE, sl.created_at) as tgl");
    
    // Aggregation logic using UPPER/TRIM to prevent data misses
    // We check both 'tipe' and 'action_type' to be safe
    // We target action_type because 'tipe' is currently NULL in your database
    $builder->select("SUM(CASE 
    WHEN LTRIM(RTRIM(UPPER(sl.action_type))) IN ('MASUK', 'TAMBAH') 
    THEN sl.quantity ELSE 0 END) as total_in");

    $builder->select("SUM(CASE 
    WHEN LTRIM(RTRIM(UPPER(sl.action_type))) IN ('KELUAR', 'DISTRIBUSI', 'AMBIL', 'PINDAH') 
    THEN sl.quantity ELSE 0 END) as total_out");
    
    $builder->join('items i', 'i.id = sl.item_id');

    // 3. Apply Filters
    if ($category !== 'All') {
        $builder->where('i.kategori', $category);
    }

    // NEW: Use the Custom Date Range
    if (!empty($start) && !empty($end)) {
        // We convert created_at to DATE to ignore the HH:mm:ss for a clean comparison
        $builder->where("CONVERT(DATE, sl.created_at) BETWEEN '$start' AND '$end'");
    } else {
        // Fallback: Default to last 7 days if dates are missing
        $builder->where("sl.created_at >= DATEADD(day, -7, GETDATE())");
    }
    
    $builder->groupBy("CONVERT(DATE, sl.created_at)");
    $builder->orderBy("tgl", "ASC");

    $results = $builder->get()->getResultArray();

    return $this->response->setJSON($results);
}

public function export_graph_data()
{
    // --- SECURITY GATE: Allows Super Admin or For Office (View Only) ---
    $userRole = session()->get('role');
    if (!in_array($userRole, ['Super Admin', 'Admin Gudang Depan', 'Admin Gudang Belakang'])) { 
        return redirect()->to(base_url('dashboard'))
                         ->with('error', 'Unauthorized access.');
    }

    // Clear output buffers to prevent file corruption
    while (ob_get_level()) {
        ob_end_clean();
    }

    $db = \Config\Database::connect();
    
    // 1. Get filters from the Analytics Graph
    $start = $this->request->getGet('start');
    $end   = $this->request->getGet('end');
    $cat   = $this->request->getGet('cat');

    $builder = $db->table('stock_logs l');
    $builder->select('l.created_at, i.nama_barang, i.kategori, i.ukuran, l.action_type, l.quantity, l.keterangan');
    $builder->join('items i', 'i.id = l.item_id');

    // 2. Apply Dynamic Filters
    if (!empty($start)) $builder->where('CAST(l.created_at AS DATE) >=', $start);
    if (!empty($end))   $builder->where('CAST(l.created_at AS DATE) <=', $end);
    if (!empty($cat) && $cat !== 'All') $builder->where('i.kategori', $cat);

    // Filter out N/A and noise per your Feb 27 rules
    $builder->whereNotIn("UPPER(i.ukuran)", ['N/A', 'NA', '-', '--']);
    $builder->orderBy('l.created_at', 'DESC');
    $data = $builder->get()->getResultArray();

    // 3. File naming
    $filename = "Laporan_Mutasi_Stok_" . date('Ymd') . ".xls";

    // Standard headers for Excel spoofing
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // 4. Output the Table with the Red Header (#dc3545)
    echo '<table border="1">
            <tr style="background-color: #dc3545; color: #ffffff;">
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Ukuran</th>
                <th>Status</th>
                <th>Qty</th>
                <th>Keterangan</th>
            </tr>';

    if (empty($data)) {
        echo '<tr><td colspan="8" style="text-align:center;">No data found.</td></tr>';
    } else {
        $no = 1;
        foreach ($data as $row) {
            // Standardize Status logic
            $rawType = strtoupper($row['action_type']);
            $status = (in_array($rawType, ['DISTRIBUSI', 'KELUAR', 'PINDAH', 'DELETE'])) ? 'Keluar' : 'Masuk';
            
            // Format size
            $size = (empty($row['ukuran']) || $row['ukuran'] == '') ? '-' : $row['ukuran'];

            echo '<tr>
                    <td>' . $no++ . '</td>
                    <td>' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</td>
                    <td>' . $row['nama_barang'] . '</td>
                    <td>' . $row['kategori'] . '</td>
                    <td>' . $size . '</td>
                    <td>' . $status . '</td>
                    <td>' . $row['quantity'] . '</td>
                    <td>' . ($row['keterangan'] ?? '-') . '</td>
                  </tr>';
        }
    }
    echo '</table>';
    exit();
}
}