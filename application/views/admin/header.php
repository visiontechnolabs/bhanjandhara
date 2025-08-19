<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
<link rel="icon" href="<?= base_url('assets/images/favicon-32x32.png') ?>" type="image/png">

<!--plugins-->
<link href="<?= base_url('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/plugins/simplebar/css/simplebar.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/plugins/metismenu/css/metisMenu.min.css') ?>" rel="stylesheet">

<!-- loader-->
<link href="<?= base_url('assets/css/pace.min.css') ?>" rel="stylesheet"/>
<script src="<?= base_url('assets/js/pace.min.js') ?>"></script>

<!-- Bootstrap CSS -->
<link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/bootstrap-extended.css') ?>" rel="stylesheet">

<!-- Google Fonts (CDN) -->
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">

<!-- App Styles -->
<link href="<?= base_url('assets/sass/app.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/icons.css') ?>" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<!-- Theme Style CSS -->
<link rel="stylesheet" href="<?= base_url('assets/sass/dark-theme.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/sass/semi-dark.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/sass/bordered-theme.css') ?>">

	
	<title>ભજનધારા</title>
</head>

<body>
	<!--wrapper-->
	<div class="wrapper">
		<!--sidebar wrapper -->
		<div class="sidebar-wrapper" data-simplebar="true">
			<div class="sidebar-header">
				<div>
				</div>
				<div>
					<h4 class="logo-text fw-bold ms-5">ભજનધારા</h4>
				</div>
				<div class="mobile-toggle-icon ms-auto"><i class='bx bx-x'></i>
				</div>
			 </div>
			<!--navigation-->
			<ul class="metismenu" id="menu">
    <li>
        <a href="<?= base_url('dashboard');?>" class="">
            <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
            <div class="menu-title">Dashboard</div>
        </a>
    </li>
    
    <li>
        <a href="javascript:;" class="has-arrow">
            <div class="parent-icon"><i class='bx bx-category'></i></div>
            <div class="menu-title">Category</div>
        </a>
        <ul>
            <li><a href="<?= base_url('category');?>"><i class='bx bx-radio-circle'></i>All Category</a></li>
            <li><a href="<?= base_url('add_category');?>"><i class='bx bx-plus-circle'></i>Add new</a></li>
        </ul>
    </li>
    
    <li>
        <a href="javascript:;" class="has-arrow">
            <div class="parent-icon"><i class='bx bx-grid-alt'></i></div>
            <div class="menu-title">Sub Category</div>
        </a>
        <ul>
            <li><a href="<?= base_url('sub_category');?>"><i class='bx bx-radio-circle'></i>All Sub Category</a></li>
            <li><a href="<?= base_url('add_sub_category');?>"><i class='bx bx-plus-circle'></i>Add new</a></li>
        </ul>
    </li>
   <li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class='bx bxs-music'></i></div> <!-- main icon -->
        <div class="menu-title">Songs</div>
    </a>
    <ul>
        <li>
            <a href="<?= base_url('songs');?>">
                <i class='bx bx-list-ul'></i>All Songs
            </a>
        </li>
        <li>
            <a href="<?= base_url('add_new_song');?>">
                <i class='bx bx-music'></i>Add New
            </a>
        </li>
    </ul>
</li>

    
    <li>
        <a href="<?= base_url('customer');?>" class="">
            <div class="parent-icon"><i class='bx bx-user'></i></div>
            <div class="menu-title">Users</div>
        </a>
    </li>
   
	<li>
    <a href="<?= base_url('logout');?>" class="">
        <div class="parent-icon"><i class='bx bx-log-out'></i></div>
        <div class="menu-title">Logout</div>
    </a>
</li>
</ul>

			<!--end navigation-->
		</div>
		<!--end sidebar wrapper -->
		<!--start header -->
		<header>
			<div class="topbar">
				<nav class="navbar navbar-expand gap-2 align-items-center">
					<div class="mobile-toggle-menu d-flex"><i class='bx bx-menu'></i>
					</div>

					  
				</nav>
			</div>
		</header>
		<!--end header -->
		<!--start page wrapper -->