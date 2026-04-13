<?php

namespace App\Models;

use CodeIgniter\Model;

class OpnameModel extends Model
{
    protected $table            = 'stok_opname_log';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // We disable this because we want the "Tanggal" to be 
    // a permanent snapshot from the moment of input.
    protected $useTimestamps = false; 

    // These are the only fields the system is allowed to "touch"
    protected $allowedFields = [
        'tanggal', 
        'gudang', 
        'nama_barang', 
        'sistem_stok', 
        'stok_fisik', 
        'selisih', 
        'petugas_admin'
    ];

    /**
     * Custom function to get data for the 2-line chart
     * Comparing System vs Physical over time
     */
    public function getChartData()
    {
        return $this->orderBy('tanggal', 'ASC')->findAll();
    }
}