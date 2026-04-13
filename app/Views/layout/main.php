<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'GA Portal' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Base Flex Layout */
        body { 
            display: flex; 
            min-height: 100vh; 
            overflow-x: hidden; 
            margin: 0;
        }

        /* Sidebar Styling */
        #sidebar { 
            width: 250px; 
            min-width: 250px; /* Fixes the 'empty' look by preventing collapse */
            background: #2c3e50; 
            color: white; 
            transition: 0.3s; 
            display: flex;
            flex-direction: column;
        }

        /* Content Area Styling */
        #content { 
            flex: 1; 
            padding: 20px; 
            background: #f8f9fa; 
            min-width: 0; /* Prevents large tables from breaking the flex layout */
        }

        /* Navigation Links */
        .nav-link { color: rgba(255,255,255,0.8); margin: 5px 0; }
        .nav-link:hover, .nav-link.active { 
            background: #34495e; 
            color: white; 
            border-radius: 5px; 
        }

        .sidebar-header { 
            padding: 20px; 
            font-weight: bold; 
            font-size: 1.2rem; 
            border-bottom: 1px solid #444; 
        }
    </style>
</head>
<body>

<?= $this->include('layout/sidebar.php') ?>

<div id="content">
    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-info border-0 shadow-sm mb-4">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>