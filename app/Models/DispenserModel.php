<?php
namespace App\Models;
use CodeIgniter\Model;

class DispenserModel extends Model {
    protected $table = 'assets_dispenser';
    protected $primaryKey = 'id';
    
    // Enabling timestamps is a best practice for asset tracking
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'serial_number', 
        'nama_asset', 
        'lokasi', 
        'tgl_mulai', 
        'tgl_berakhir', 
        'status', 
        'vendor', 
        'biaya_sewa_per_bulan'
    ];

    // Added a helper function to find expiring rentals (for your future alerts)
    public function getExpiringSoon($days = 30) {
        return $this->where('tgl_berakhir <=', date('Y-m-d', strtotime("+$days days")))
                    ->where('status', 'Aktif')
                    ->findAll();
    }
}