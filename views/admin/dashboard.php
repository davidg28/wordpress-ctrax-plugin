<div class="wrap">
	<div class="entry">
		<div class="container-fluid">
			<div class="row no-gutters" style="background-color: #3B8525;">
				<div class="col text-center">
					<img src="<?php echo C_TRAX_INTEGRATION_ASSETS_URL . 'images/c-trax-logo.png'; ?>" height="200px" alt="<?php echo C_TRAX_INTEGRATION_PROJECT; ?>"/>
				</div>
			</div>
			<?php do_action( C_TRAX_INTEGRATION_ACTION . '_setup_needed' ); ?>

			<div class="row mt-2">
				<div class="col-sm">
					<div class="card">
						<h5 class="card-header"><i class="fa fa-user text-success"></i> C-Trax Account Details</h5>
						<?php if ( $ctraxUser && $ctraxUser instanceof \C_Trax_Integration\Models\User ) { ?>
							<ul class="list-group list-group-flush">
								<li class="list-group-item">
									<h6 class="mb-1">C-Trax Domain</h6>
									<?php echo $ctraxUser->instance_domain; ?>
								</li>
								<li class="list-group-item">
									<h6 class="mb-1">Username</h6>
									<?php echo $ctraxUser->username; ?>
								</li>
							</ul>
						<?php } else { ?>
							<div class="card-body">
								<p class="card-text">You have not connected your account yet. Please following the setup guide to get started!</p>
								<a href="<?php \C_Trax_Integration\Includes\Output::menu_page_url( C_TRAX_INTEGRATION_SLUG_SETUP, true ); ?>&step=connect-account" class="btn btn-primary btn-block">Setup Account</a>
							</div>
						<?php } ?>
					</div>
				</div>

				<div class="col-sm">
					<div class="card">
						<h5 class="card-header"><i class="fa fa-square text-info"></i> Square</h5>
						<?php if ( $squareAppId ) { ?>
							<ul class="list-group list-group-flush">
								<li class="list-group-item">
									<h6 class="mb-1">Square App Id</h6>
									<?php echo $squareAppId; ?>
								</li>
							</ul>
						<?php } else { ?>
							<div class="card-body">
								<p class="card-text">You have not connected your square account information yet. Please following the setup guide to get started!</p>
								<a href="<?php \C_Trax_Integration\Includes\Output::menu_page_url( C_TRAX_INTEGRATION_SLUG_SETUP, true ); ?>" class="btn btn-primary btn-block">Setup Square</a>
							</div>
						<?php } ?>
					</div>
				</div>

				<div class="col-sm">
					<div class="card">
						<h5 class="card-header"><i class="fa fa-support text-danger"></i> Need Help?</h5>
						<ul class="list-group list-group-flush">
							<li class="list-group-item">
								<a href="#"><i class="fa fa-user"></i> Account Connection</a><br/><small>Review how to connect your C-Trax account with your Wordpress site</small>
							</li>
							<li class="list-group-item"><a href="#"><i class="fa fa-square"></i> Square Setup</a><br/><small>Learn how to setup your e-commerce plugin using Square</small></li>
							<li class="list-group-item"><a href="#"><i class="fa fa-globe"></i> C-Trax Site</a></li>
							<li class="list-group-item">
								<a href="<?php \C_Trax_Integration\Includes\Output::menu_page_url( C_TRAX_INTEGRATION_SLUG_SETUP, true ); ?>&step=connect-account">
									<i class="fa fa-user"></i> <?php echo C_TRAX_INTEGRATION_PROJECT; ?> Setup
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="row mt-2">
				<div class="col">
					<div class="card text-center">
						<div class="card-header">
							Featured
						</div>
						<div class="card-body">
							<h5 class="card-title">Special title treatment</h5>
							<p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
							<a href="#" class="btn btn-primary">Go somewhere</a>
						</div>
						<div class="card-footer text-muted">
							2 days ago
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

