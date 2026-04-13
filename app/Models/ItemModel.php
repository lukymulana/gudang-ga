<?php
namespace App\Models;
use CodeIgniter\Model;
   
class ItemModel extends Model {
    protected $table = 'items';
    protected $primaryKey = 'id';
    
    // Pembaruan Lengkap: Menambahkan sub_kategori, gender, dan gudang agar sesuai dengan database SSMS
    protected $allowedFields = [
        'nama_barang', 
        'kategori', 
        'sub_kategori',   
        'stok_depan', 
        'stok_belakang', 
        'stok_aktual',
        'min_stok',       
        'harga_satuan', 
        'kondisi', 
        'status_seragam', 
        'gender',         
        'ukuran', 
        'merk', 
        'warna',
        'gudang'          
    ];
}