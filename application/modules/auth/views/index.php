<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>GC&amp;C Inc.</title>
		<link rel="icon" type="image/x-icon" href="<?=base_url('assets/media/logo.png') ?>">
        <meta name="description" content="Latest updates and statistic charts">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="<?=base_url('assets/css/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
        <link href="<?=base_url('assets/plugins/global/plugins.bundle.css') ?>" rel="stylesheet">
        <link href="<?=base_url('assets/css/style/style.bundle.css') ?>" rel="stylesheet">
    </head>
    <body id="kt_body" class="bg-body">
        <div class="d-flex flex-column flex-root">
			<div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed" style="background-image: url(<?=base_url() ?>assets/media/illustrations/sketchy-1/14.png">
				<div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
					<img alt="Logo" src="<?=base_url('assets/media/logo.png') ?>" class="h-100px mb-5" />
					<a href="javascript:void(0)" class="mb-7">
						<h2 class="text-dark mb-0">Meeting Room Booking System</h2>
					</a>
					<div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
						<form class="form w-100" id="kt_sign_in_form" method="POST">
							<div class="text-center mb-10">
								<h1 class="text-dark mb-0">Sign In</h1>
							</div>
							<div id="alert" class="alert d-flex justify-content-center align-items-center text-center d-none">
								<span id="msg" style="font-weight: 600"></span>
							</div>
							<div class="fv-row mb-10">
								<label class="form-label fs-6 fw-bolder text-dark">Username</label>
								<input class="form-control form-control-lg form-control-solid" type="text" name="username" autocomplete="off"  required  >
							</div>
							<div class="fv-row mb-10">
								<div class="d-flex flex-stack mb-2">
									<label class="form-label fw-bolder text-dark fs-6 mb-0">Password</label>
								</div>
								<input class="form-control form-control-lg form-control-solid" type="password" name="password" autocomplete="off" required >
							</div>
							<div class="text-center">
								<button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
									<span class="indicator-label">Login</span>
									<span class="indicator-progress">Please wait...
									<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
								</button>
                                <div class="d-none">
                                    <div class="text-center text-muted text-uppercase fw-bolder mb-5">or</div>
                                    <a href="#" class="btn btn-flex flex-center btn-light btn-lg w-100 mb-5">
                                        Continue as Guest
                                    </a>
                                </div>
							</div>
						</form>
					</div>
                </div>
			</div>
		</div>
		
        <script src="<?=base_url() ?>assets/js/jquery.min.js"></script>
        <script src="<?=base_url() ?>assets/js/bootstrap/bootstrap.min.js"></script>
        <script src="<?=base_url() ?>assets/plugins/global/plugins.bundle.js"></script>
        <script src="<?=base_url() ?>assets/js/scripts.bundle.js"></script>
        <script type="javascript" src="<?=base_url() ?>assets/js/custom/authentication/sign-in/general.js"></script>
        <script src="<?=base_url() ?>assets/js/app/login/login.js"></script>
    </body>
</html>