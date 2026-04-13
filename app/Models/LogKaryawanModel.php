<?php

namespace App\Models;

use CodeIgniter\Model;

class LogKaryawanModel extends Model
{
    protected $table      = 'log_karyawan';
    protected $primaryKey = 'id';

    // --- FIX FOR THE ERROR ---
    protected $useTimestamps = true;
    protected $createdField  = 'tanggal_ambil'; // Tell CI to use this instead of created_at
    protected $updatedField  = '';              // Leave empty if you don't have an update column

    protected $allowedFields = [
        'item_id', 
        'nama_karyawan', 
        'qty', 
        'kondisi', 
        'tanggal_ambil'
    ];
}