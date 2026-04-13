<?php

namespace App\Controllers;

use App\Models\OpnameModel;

class Opname extends BaseController
{
    protected $opnameModel;
    protected $db;

    public function __construct()
    {
        $this->opnameModel = new OpnameModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (session()->get('role') !== 'Super Admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses Dibatasi!');
        }

        $db = \Config\Database::connect();
        
        $gudang_choice = $this->request->getGet('gudang');

        // Logic Change: We remove the ->where filter so that items 
        // with ANY 'gudang' label appear in the master list.
        $builder = $db->table('items');
        
        // This part is removed/commented out to ensure all items show up:
        // if ($gudang_choice) {
        //     $builder->where('gudang', $gudang_choice);
        // }
        
        $data['master_stok'] = $builder->get()->getResultArray();
        $data['history'] = $this->opnameModel->orderBy('tanggal', 'DESC')->findAll();

        $data['selisih_depan'] = $db->query("SELECT SUM(ABS(selisih)) as total FROM stok_opname_log WHERE gudang = 'Gudang Depan'")->getRow()->total ?? 0;
        $data['selisih_belakang'] = $db->query("SELECT SUM(ABS(selisih)) as total FROM stok_opname_log WHERE gudang = 'Gudang Belakang'")->getRow()->total ?? 0;

        $totalLogs = $db->table('stok_opname_log')->countAllResults();
        $perfectLogs = $db->table('stok_opname_log')->where('selisih', 0)->countAllResults();

        $data['accuracy_rate'] = ($totalLogs > 0) ? ($perfectLogs / $totalLogs) * 100 : 100;
        
        $data['selected_gudang'] = $gudang_choice;

        return view('opname', $data);
    }

    public function save_multi()
    {
        $itemIds        = $this->request->getPost('id_item');
        $namaBarang     = $this->request->getPost('nama_barang');
        $stokFisikDepan = $this->request->getPost('fisik_depan');
        $stokFisikBelak = $this->request->getPost('fisik_belakang');
        $sysDepan       = $this->request->getPost('sys_depan');
        $sysBelakang    = $this->request->getPost('sys_belakang');

        if ($itemIds === null) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diproses.');
        }

        $adminName = session()->get('username') ?? 'Super Admin';

        foreach ($itemIds as $index => $id) {
            
            // --- PROCESS GUDANG DEPAN ---
            if (isset($stokFisikDepan[$index]) && $stokFisikDepan[$index] !== '') {
                $curSysDepan = $sysDepan[$index] ?? 0;
                $selisihDepan = $stokFisikDepan[$index] - $curSysDepan;
                
                $this->db->table('items')->where('id', $id)->update(['stok_depan' => $stokFisikDepan[$index]]);
                
                $this->db->table('stok_opname_log')->insert([
                    'tanggal'       => date('Y-m-d H:i:s'),
                    'gudang'        => 'Gudang Depan', 
                    'nama_barang'   => $namaBarang[$index] ?? 'Unknown Item',
                    'sistem_stok'   => $curSysDepan,
                    'stok_fisik'    => $stokFisikDepan[$index],
                    'selisih'       => $selisihDepan,
                    'petugas_admin' => $adminName
                ]);
            }

            // --- PROCESS GUDANG BELAKANG ---
            if (isset($stokFisikBelak[$index]) && $stokFisikBelak[$index] !== '') {
                $curSysBelakang = $sysBelakang[$index] ?? 0;
                $selisihBelakang = $stokFisikBelak[$index] - $curSysBelakang;
                
                $this->db->table('items')->where('id', $id)->update(['stok_belakang' => $stokFisikBelak[$index]]);
                
                $this->db->table('stok_opname_log')->insert([
                    'tanggal'       => date('Y-m-d H:i:s'),
                    'gudang'        => 'Gudang Belakang',
                    'nama_barang'   => $namaBarang[$index] ?? 'Unknown Item',
                    'sistem_stok'   => $curSysBelakang,
                    'stok_fisik'    => $stokFisikBelak[$index],
                    'selisih'       => $selisihBelakang,
                    'petugas_admin' => $adminName
                ]);
            }
        }

        return redirect()->to('/opname')->with('message', 'Audit Berhasil Disimpan & Dikunci!');
    }
}