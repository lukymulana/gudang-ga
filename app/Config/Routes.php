<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// 1. Landing Page (Default)
$routes->get('/', 'Dashboard::index');
$routes->get('login', 'Authentication::index');

// 2. Authentication (The Gatekeeper)
$routes->get('authentication', 'Authentication::index');
$routes->post('authentication/loginProcess', 'Authentication::loginProcess');
$routes->get('logout', 'Authentication::logout');
$routes->get('authentication/logout', 'Authentication::logout');

// 3. Inventory Dashboard & Functions
$routes->get('inventory', 'Inventory::index');
$routes->get('inventory/add', 'Inventory::add');
$routes->post('inventory/save', 'Inventory::save');
$routes->get('inventory/get_min_stock', 'Inventory::get_min_stock'); // Added for auto-fill feature

// --- ADDED THESE FOR SUPER ADMIN ---
$routes->get('inventory/edit/(:num)', 'Inventory::edit/$1');
$routes->post('inventory/update/(:num)', 'Inventory::update/$1');
$routes->get('inventory/delete/(:num)', 'Inventory::delete/$1');

// 4. Master Data (Management)
$routes->get('master', 'Master::index');

// 5. Distribution (Single Item)
$routes->get('inventory/distribute/(:num)', 'Inventory::distribute/$1');
$routes->post('inventory/process_distribution', 'Inventory::process_distribution');

// History Page 
$routes->get('inventory/history', 'Inventory::history');

// 6. Employee info 
$routes->get('inventory/get_employee_info', 'Inventory::get_employee_info');
$routes->get('inventory/view_log_karyawan', 'Inventory::view_log_karyawan');

// 7. Distribusi (Renamed from quick_distribute)
$routes->get('inventory/distribusi', 'Inventory::distribusi'); // Changed to match your controller
$routes->post('inventory/process_quick_distribute', 'Inventory::process_quick_distribute');

// Transfer Functions
$routes->get('inventory/transfer', 'Inventory::transfer');
$routes->post('inventory/process_transfer', 'Inventory::process_transfer');

// Monitoring & Loans
$routes->get('inventory/monitoring_pinjam', 'Inventory::monitoring_pinjam');
$routes->get('inventory/return_item/(:num)', 'Inventory::return_item/$1');
$routes->get('inventory/employee_history', 'Inventory::employee_history');

// Route for processing the bulk distribution (Basket)
$routes->post('inventory/process_bulk_distribution', 'Inventory::process_bulk_distribution');
$routes->post('inventory/process_unified_basket', 'Inventory::process_unified_basket');

// For reverse a transaction
$routes->post('inventory/reverse_transaction/(:num)', 'Inventory::reverse_transaction/$1');

// Dashboard 
$routes->get('dashboard', 'Dashboard::index');

$routes->get('dispenser', 'Dispenser::index');
// Optional: Add routes for adding/editing later
$routes->post('dispenser/save', 'Dispenser::save');
$routes->get('dispenser/delete/(:num)', 'Dispenser::delete/$1');

$routes->get('inventory/export_excel_log', 'Inventory::export_excel_log');

// Existing route for status toggle (Active/Non-Active)
$routes->get('dispenser/update_status/(:num)/(:any)', 'Dispenser::update_status/$1/$2');

// New route specifically for the Repair button
$routes->get('dispenser/send_to_service/(:num)', 'Dispenser::send_to_service/$1');

// Route for the Stok Opname Module
$routes->get('opname', 'Opname::index');
$routes->post('opname/save', 'Opname::save');

$routes->get('opname', 'Opname::index');
$routes->post('opname/save_multi', 'Opname::save_multi');

$routes->get('inventory/export_excel_stock_logs', 'Inventory::export_excel_stock_logs');

//Mutasi Transaksi
$routes->get('dashboard/getMutationData', 'Dashboard::getMutationData');

// Add this line to allow the Export function to be reached
$routes->get('dashboard/export_graph_data', 'Dashboard::export_graph_data');