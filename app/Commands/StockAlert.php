<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class StockAlert extends BaseCommand
{
    protected $group       = 'Inventory';
    protected $name        = 'stock:send-alerts';
    protected $description = 'Checks stok_depan and stok_belakang against min_stok and sends report via Astra API.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        // 1. DATA GATHERING - Using your exact column names
        // Trigger: If either warehouse is below minimum
        $sql = "SELECT 
                    nama_barang, kategori, sub_kategori, gender, ukuran, 
                    merk, warna, kondisi, stok_depan, stok_belakang, 
                    min_stok, harga_satuan,
                    ((stok_depan + stok_belakang) * harga_satuan) as stock_value
                FROM items 
                WHERE stok_depan < min_stok 
                   OR stok_belakang < min_stok";

        $lowStockItems = $db->query($sql)->getResultArray();

        if (empty($lowStockItems)) {
            CLI::write('Semua stok aman.', 'green');
            return;
        }

        // 2. PREPARE THE MESSAGE
        $message = "REPORT: LOW STOCK DETECTED - " . date('d/m/Y H:i') . "\n";
        $message .= str_repeat("=", 45) . "\n";

        foreach ($lowStockItems as $item) {
            $message .= "Item         : {$item['nama_barang']}\n";
            $message .= "Category     : {$item['kategori']} ({$item['sub_kategori']})\n";
            $message .= "Spec         : {$item['gender']} | Size: {$item['ukuran']} | Color: {$item['warna']}\n";
            $message .= "Brand/Cond   : {$item['merk']} | {$item['kondisi']}\n";
            $message .= "Stok Depan   : {$item['stok_depan']} " . ($item['stok_depan'] < $item['min_stok'] ? '[LOW]' : '') . "\n";
            $message .= "Stok Belakang: {$item['stok_belakang']} " . ($item['stok_belakang'] < $item['min_stok'] ? '[LOW]' : '') . "\n";
            $message .= "Min. Stock   : {$item['min_stok']}\n";
            // $message .= "Unit Price   : Rp " . number_format($item['harga_satuan'], 0, ',', '.') . "\n";
            // $message .= "Stock Value  : Rp " . number_format($item['stock_value'], 0, ',', '.') . "\n";
            $message .= str_repeat("-", 45) . "\n";
        }

        // 3. SEND VIA ASTRA INTERNAL API
        $apiUrl = 'https://portal2.incoe.astra.co.id/vendor_rating_infor/api/send_email_text';
        
        $postData = [
            'to'      => 'qinjiwei5@gmail.com', // Update this to the superior's email
            'subject' => '[ALERT] Inventory Below Minimum (Depan/Belakang)',
            'message' => $message,
            'from'    => 'inventory.system@incoe.astra.co.id'
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 4. VERDICT
        if ($httpCode === 200) {
            CLI::write('BERHASIL: Alert telah dikirim menggunakan data items.', 'cyan');
        } else {
            CLI::error("GAGAL: API Astra merespons dengan kode $httpCode");
            CLI::write("Response: $response");
        }
    }
}