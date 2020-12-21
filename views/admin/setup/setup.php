<div class="row mt-2">
	<div class="col">
		<div class="card text-center">
			<h5 class="card-header"><i class="fa fa-cogs text-success"></i> <?php echo C_TRAX_INTEGRATION_PROJECT; ?> Setup</h5>
			<div class="card-body">
				<h5 class="card-title">Welcome to the <?php echo C_TRAX_INTEGRATION_PROJECT; ?> setup process.</h5>
				<p class="card-text">
					In the following steps, you will connect your C-Trax account information to your Wordpress environment.
					<br/>
					Please have your your access token ready as this is needed in order to validate your account.
				</p>
				<a href="<?php \C_Trax_Integration\Includes\Output::menu_page_url( C_TRAX_INTEGRATION_SLUG_SETUP ); ?>&step=<?php echo $nextStep['slug']; ?>" class="btn btn-primary btn-lg btn-block">Begin Setup</a>
			</div>
		</div>
	</div>
</div>