<div class="row mt-2">
	<div class="col">
		<div class="card text-center">
			<h5 class="card-header"><i class="fa fa-user text-success"></i> <?php echo C_TRAX_INTEGRATION_PROJECT; ?> Setup</h5>
			<div class="card-body" id="connect-account-contain">
				<h5 class="card-title"><?php echo $currentStep['title']; ?></h5>
				<p class="card-text">
					Congratulations, <?php echo $ctraxUser->first_name; ?>! The setup process is complete and you are now able to use the <?php echo C_TRAX_INTEGRATION_PROJECT; ?> plugin!
				</p>
				<div id="setup-continue-contain">
					<a href="<?php \C_Trax_Integration\Includes\Output::menu_page_url( C_TRAX_INTEGRATION_PREFIX ); ?>" class="btn btn-primary btn-lg btn-block">
						Return to <?php echo C_TRAX_INTEGRATION_PROJECT; ?> Dashboard
					</a>
				</div>
			</div>
		</div>
	</div>
</div>