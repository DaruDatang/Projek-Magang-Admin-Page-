<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - Admin Panel Wilayah Indonesia' : 'Admin Panel Wilayah Indonesia'; ?></title>
    
    <link rel="icon" href="<?= base_url('assets/img/logo.png'); ?>" type="image/png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Segoe UI", Arial, sans-serif;
            color: #212529;
        }

        .top-header {
            height: 56px; 
            width: 100%;
            position: fixed; 
            top: 0;
            left: 0;
            z-index: 1030;
        }

        #wrapper { 
            display: flex; 
            padding-top: 56px; 
        }

        .sidebar {
            width: 220px;
            flex-shrink: 0; 
            height: calc(100vh - 56px); 
            background: #212529;
            color: white;
            position: fixed; 
            left: 0;
            top: 56px; 
        }

        .sidebar .nav-link { 
            padding: 10px 20px;
            color: #ccc;
            text-decoration: none;
            transition: background-color 0.2s, padding-left 0.2s;
            white-space: nowrap;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active { 
            background-color: #0d6efd; 
            color: #fff !important; 
            font-weight: 600;
            border-left: 4px solid #0b5ed7;
        }
        
        .sidebar .nav-link:hover {
            padding-left: 25px;
        }
        
        .sidebar-footer {
            position: absolute; 
            bottom: 1rem;
            left: 0;
            right: 0;
        }
        
        .sidebar .nav-link .fa-fw {
            margin-right: 8px;
        }

        #page-content {
            flex-grow: 1; 
            background-color: #f8f9fa;
            min-height: calc(100vh - 56px);
            position: relative; 
            margin-left: 220px; 
        }

        .dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }
        
        @media (max-width: 768px) {
            #wrapper { 
                padding-top: 56px;
            }
            .sidebar { 
                position: relative; 
                width: 100%; 
                height: auto; 
                top: auto;
            }
            #page-content { 
                margin-left: 0; 
                width: 100%; 
            }
            .sidebar-footer {
                position: relative;
                margin-top: 1rem;
            }
            .dropdown:hover .dropdown-menu {
                display: none;
            }
            .dropdown.show .dropdown-menu {
                display: block;
            }
        }
    </style>
</head>
<body>

<nav class="top-header navbar navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= base_url('wilayah'); ?>">
            Admin Panel Wilayah Indonesia
        </a>
    </div>
</nav>

<div id="wrapper">

    <?php $this->load->view('partials/sidebar'); ?>
    
    <div id="page-content">
        
        <div class="container-fluid p-4">