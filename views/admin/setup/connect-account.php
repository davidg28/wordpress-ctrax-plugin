<div class="row mt-2">
	<div class="col">
		<div class="card text-center">
			<h5 class="card-header"><i class="fa fa-user text-success"></i> <?php echo C_TRAX_INTEGRATION_PROJECT; ?> Setup</h5>
			<div class="card-body" id="connect-account-contain">
				<h5 class="card-title"><?php echo $currentStep['title']; ?></h5>
				<p class="card-text">
					In order to connect your account with your instance of C-Trax, enter the domain at which it resides (ex: https://www.my-ctrax.com).
					<br/>
					Next, input your C-Trax Username and Password in the following inputs and click "Connect Account".
					<br/>
					This will access your C-Trax account information and store limited data in your Wordpress database (passwords are not saved).
					<br>
					An access token will be stored in order to keep the user account connected and will be refreshed daily.
				</p>
				<div id="setup-input-contain">
					<div class="row justify-content-md-center">
						<div class="col col-md-5">
							<?php echo \C_Trax_Integration\Includes\Form::text( 'instance_domain', 'C-Trax Domain', $ctraxUser->instance_domain, [ 'id' => 'instance_domain', 'required' => true ], 'left' ); ?>
							<?php echo \C_Trax_Integration\Includes\Form::text( 'username', 'Username', null, [ 'id' => 'username', 'required' => true ], 'left' ); ?>
							<?php echo \C_Trax_Integration\Includes\Form::password( 'password', 'Password', null, [ 'id' => 'password', 'required' => true ], 'left' ); ?>
						</div>
					</div>

					<button type="button" id="connect-account" class="btn btn-success btn-lg btn-block">Connect Account</button>
				</div>
				<div id="setup-continue-contain" class="hidden">
					<a href="<?php \C_Trax_Integration\Includes\Output::menu_page_url( C_TRAX_INTEGRATION_SLUG_SETUP ); ?>&step=<?php echo $nextStep['slug']; ?>" class="btn btn-primary btn-lg btn-block">
						Continue to <?php echo $nextStep['title']; ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>