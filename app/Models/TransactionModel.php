<?php namespace App\Models;
use CodeIgniter\Model;

class TransactionModel extends Model {
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['item_id', 'user_id_action', 'transaction_type', 'qty', 'karyawan_nik', 'karyawan_nama', 'keterangan'];
}