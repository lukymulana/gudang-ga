<?php
namespace App\Controllers;
use App\Models\DispenserModel;

class Dispenser extends BaseController {
    
    public function index() {
        $model = new DispenserModel();
        $data['dispensers'] = $model->findAll();
        $data['title'] = "Manajemen Dispenser Sewa";
        $data['today'] = date('Y-m-d'); 
        
        return view('dispenser/index', $data);
    }

    public function save() {
        $model = new DispenserModel();
        
        $rawBiaya = $this->request->getPost('biaya_sewa_per_bulan');
        $cleanBiaya = preg_replace('/[^0-9]/', '', $rawBiaya);

        $data = [
            'serial_number'        => $this->request->getPost('serial_number'),
            'vendor'               => $this->request->getPost('vendor'),
            'tgl_mulai'            => $this->request->getPost('tgl_mulai'),
            'tgl_berakhir'         => $this->request->getPost('tgl_berakhir'),
            'lokasi'               => $this->request->getPost('lokasi'),
            'biaya_sewa_per_bulan' => $cleanBiaya, 
            'status'               => 'Aktif' 
        ];

        if ($model->save($data)) {
            return redirect()->to('dispenser')->with('msg', 'Dispenser baru berhasil diregistrasi.');
        }
        return redirect()->back()->with('msg', 'Gagal menyimpan data dispenser.');
    } 

    public function update_status($id, $status) {
        $model = new DispenserModel();
        $dispenser = $model->find($id);

        if (!$dispenser) {
            return redirect()->to('dispenser')->with('msg', 'Data tidak ditemukan.');
        }

        $today = date('Y-m-d');
        if ($dispenser['tgl_berakhir'] < $today) {
            $status = 'Non-Aktif';
            $finalMsg = "Masa sewa habis. Status otomatis diubah ke Non-Aktif.";
        } else {
            $finalMsg = "Status dispenser berhasil diubah menjadi $status.";
        }

        if (!in_array($status, ['Aktif', 'Non-Aktif', 'Service', 'Sedang Diperbaiki'])) {
            return redirect()->to('dispenser')->with('msg', 'Status tidak valid.');
        }

        if ($model->update($id, ['status' => $status])) {
            return redirect()->to('dispenser')->with('msg', $finalMsg);
        }

        return redirect()->back()->with('msg', 'Gagal memperbarui status.');
    }

    public function send_to_service($id) {
        $model = new DispenserModel();
        if ($model->update($id, ['status' => 'Sedang Diperbaiki'])) {
            return redirect()->to('dispenser')->with('msg', 'Unit telah dikirim. Status: Sedang Diperbaiki.');
        }
        return redirect()->back()->with('msg', 'Gagal memproses perbaikan.');
    }

    public function delete($id) {
        $model = new DispenserModel();
        if ($model->delete($id)) {
            return redirect()->to('dispenser')->with('msg', 'Data dispenser telah dihapus.');
        }
        return redirect()->back()->with('msg', 'Gagal menghapus data.');
    }
} 