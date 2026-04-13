<?php

namespace App\Controllers;
use App\Models\ItemModel;

class Master extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $model = new ItemModel();
        $data['title'] = "Listing Kategori Master";

        // 1. Adaptive Categories (Calculates Total physical stock)
        $data['live_categories'] = $db->table('items')
            ->select('kategori, COUNT(id) as total_items, SUM(ISNULL(stok_aktual, 0)) as total_stok')
            ->where('kategori !=', '')
            ->where('kategori IS NOT NULL')
            ->groupBy('kategori')
            ->orderBy('kategori', 'ASC')
            ->get()
            ->getResultArray();

        // 2. Adaptive Statuses (Will now show Kartap, Kontrak, etc. dynamically)
        $data['live_statuses'] = $db->table('items')
            ->select('status_seragam, COUNT(id) as total_items')
            ->where('status_seragam !=', '')
            ->where('status_seragam IS NOT NULL')
            ->groupBy('status_seragam')
            ->orderBy('status_seragam', 'ASC')
            ->get()
            ->getResultArray();

        // 3. Adaptive Sizes (Excludes empty or placeholder sizes)
        $data['live_sizes'] = $db->table('items')
            ->select('ukuran, COUNT(id) as total_items')
            ->whereNotIn('ukuran', ['', '-', 'None', 'N/A'])
            ->where('ukuran IS NOT NULL')
            ->groupBy('ukuran')
            ->orderBy('ukuran', 'ASC')
            ->get()
            ->getResultArray();

        return view('master_view', $data);
    }
}