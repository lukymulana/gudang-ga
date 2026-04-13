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
        $data = [
            'serial_number' => $this->request->getPost('serial_number'),
            'vendor'        => $this->request->getPost('vendor'),
            'tgl_mulai'     => $this->request->getPost('tgl_mulai'),
            'tgl_berakhir'  => $this->request->getPost('tgl_berakhir'),
            'lokasi'        => $this->request->getPost('lokasi'),
            'status'        => 'Aktif' 
        ];

        if ($model->save($data)) {
            return redirect()->to('dispenser')->with('msg', 'Dispenser baru berhasil diregistrasi.');
        }
        return redirect()->back()->with('msg', 'Gagal menyimpan data dispenser.');
    }

    // 2. Updated Status Logic (Now includes 'Sedang Diperbaiki')
    public function update_status($id, $status) {
        $model = new DispenserModel();
        $dispenser = $model->find($id);

        if (!$dispenser) {
            return redirect()->to('dispenser')->with('msg', 'Data tidak ditemukan.');
        }

        // Logic: Force Non-Aktif if expired
        $today = date('Y-m-d');
        if ($dispenser['tgl_berakhir'] < $today) {
            $status = 'Non-Aktif';
            $finalMsg = "Masa sewa habis. Status otomatis diubah ke Non-Aktif.";
        } else {
            $finalMsg = "Status dispenser berhasil diubah menjadi $status.";
        }

        // Expanded Whitelist to include our new status
        if (!in_array($status, ['Aktif', 'Non-Aktif', 'Service', 'Sedang Diperbaiki'])) {
            return redirect()->to('dispenser')->with('msg', 'Status tidak valid.');
        }

        if ($model->update($id, ['status' => $status])) {
            return redirect()->to('dispenser')->with('msg', $finalMsg);
        }

        return redirect()->back()->with('msg', 'Gagal memperbarui status.');
    }

    // 3. Updated Repair Action
    public function send_to_service($id) {
        $model = new DispenserModel();
        
        // When button is pressed, status flips to 'Sedang Diperbaiki'
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