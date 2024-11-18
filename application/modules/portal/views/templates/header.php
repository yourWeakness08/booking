<?php 
	if(!isset($_SESSION['user_logged'])){
		redirect('/', 'refresh');
	}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>GC&amp;C Inc. | Booking</title>
		<link rel="icon" type="image/x-icon" href="<?=base_url('assets/media/logo.png') ?>">
        <meta name="description" content="Latest updates and statistic charts">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="<?=base_url('assets/css/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
        <link href="<?=base_url('assets/css/datatables/datatables.bundle.css') ?>" rel="stylesheet" type="text/css" />
        <link href="<?=base_url('assets/plugins/global/plugins.bundle.css') ?>" rel="stylesheet">
        <link href="<?=base_url('assets/css/style/style.bundle.css') ?>" rel="stylesheet">
		<link href="<?=base_url('assets/css/style/style.css') ?>" rel="stylesheet">

		<script src="<?=base_url('assets/js/jquery.min.js') ?>"></script>
		<script> 
			var user_session = <?=$_SESSION['id']; ?> 
			var user_role = <?=$_SESSION['role_id']; ?> 
		</script>
    </head>
    <body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
		
		<div class="d-flex flex-column flex-root">
			<div class="page d-flex flex-row flex-column-fluid">
				<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
					<div id="kt_header" style="" class="header align-items-stretch">
						<div class="container-fluid d-flex align-items-stretch justify-content-between">
                            <div class="d-lg-none menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch" id="#kt_header_menu" data-kt-menu="true">
                                <div class="menu-item menu-lg-down-accordion me-lg-1">
                                    <a href="<?=base_url() ?>portal/dashboard" class="py-3 fs-4">
                                        <img src="<?=base_url() ?>/assets/media/logo.png" alt="logo" class="h-30px logo">
                                        <span class="menu-title ms-3 text-black"><b>Meeting Room Booking System</b></span>
                                    </a>
                                </div>
                            </div>

							<div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
								<div class="d-flex align-items-stretch" id="kt_header_nav">
									<div class="header-menu">
										<div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch" id="#kt_header_menu" data-kt-menu="true">
                                            <div class="menu-item menu-lg-down-accordion me-lg-1">
												<a href="<?=base_url() ?>portal/dashboard" class="py-3">
													<img src="<?=base_url() ?>/assets/media/logo.png" alt="logo" class="h-45px logo">
                                                </a>
											</div>
                                            <div class="menu-item menu-lg-down-accordion me-lg-1">
												<a href="<?=base_url() ?>portal/dashboard" class="px-3 py-3 fs-2" style="color: #363535">
													<span class="menu-title"><b>Meeting Room Booking System</b></span>
													<span class="menu-arrow d-lg-none"></span>
                                                </a>
											</div>
										</div>
									</div>
								</div>
								
								<div class="d-flex align-items-stretch flex-shrink-0">
									<div class="d-flex align-items-center ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
										<div class="cursor-pointer symbol symbol-30px symbol-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
											<!-- <img src="<?//=base_url() ?>assets/media/avatars/300-1.jpg" alt="user" /> -->
											<i class="bi bi-person-circle" style="color: #363535; font-size: 2.5rem!important"></i>
										</div>
										<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold py-4 fs-6 w-350px" data-kt-menu="true">
											<div class="menu-item px-3">
												<div class="menu-content d-flex align-items-center px-3">
													<div class="symbol symbol-50px me-5">
														<!-- <img alt="Logo" src="<?=base_url() ?>assets/media/avatars/300-1.jpg" /> -->
														<i class="bi bi-person-circle" style="color: #363535; font-size: 2.5rem!important"></i>
													</div>
													<div class="d-flex flex-column">
														<div class="fw-bolder d-flex align-items-center fs-5">
															<?=ucwords($_SESSION['name']) ?>
														</div>
														<a href="javascript:void(0)" class="fw-bold text-muted text-hover-primary fs-7"><?=isset($_SESSION['email']) ? $_SESSION['email'] : '---' ?></a>
													</div>
												</div>
											</div>
											<?php if($_SESSION['role_id'] <= 1): ?>
												<div class="menu-item px-5">
													<a href="<?=base_url() ?>portal/facility" class="menu-link px-5">Facilities</a>
												</div>
												<div class="menu-item px-5">
													<a href="<?=base_url() ?>portal/users" class="menu-link px-5">Users</a>
												</div>
												<div class="menu-item px-5">
													<a href="<?=base_url() ?>portal/archives" class="menu-link px-5">Archives</a>
												</div>
											<?php endif; ?>
											<?php if($_SESSION['role_id'] == 0): ?>
												<div class="menu-item px-5">
													<a href="<?=base_url() ?>portal/activity_logs" class="menu-link px-5">Activity Logs</a>
												</div>
											<?php endif; ?>
                                            <div class="separator my-2"></div>
											<div class="menu-item px-5">
												<a href="<?=base_url() ?>auth/logout" class="menu-link px-5">Sign Out</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
						<div class="post d-flex flex-column-fluid" id="kt_post">
                            <div id="kt_content_container" class="container-fluid">
		