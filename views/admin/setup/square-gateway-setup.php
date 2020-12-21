<div class="row mt-2">
	<div class="col">
		<div class="card text-center">
			<h5 class="card-header"><i class="fa fa-square text-success"></i> <?php echo C_TRAX_INTEGRATION_PROJECT; ?> Setup</h5>
			<div class="card-body" id="connect-account-contain">
				<h5 class="card-title"><?php echo $currentStep['title']; ?></h5>
				<p class="card-text">
					Use the form below to save your Square Gateway credentials.
					<br/>
					The provided information can be found in your e-commerce gateway settings.
				</p>
				<div id="setup-input-contain">
					<div class="row justify-content-md-center">
						<div class="col col-md-5">
							<?php echo \C_Trax_Integration\Includes\Form::text( 'username', 'Username', null, [ 'id' => 'username', 'required' => true ], 'left' ); ?>
							<?php echo \C_Trax_Integration\Includes\Form::password( 'password', 'Password', null, [ 'id' => 'password', 'required' => true ], 'left' ); ?>
						</div>
					</div>

					<button type="button" id="connect-account" class="btn btn-success btn-lg btn-block">Connect Account</button>
					<a href="<?php \C_Trax_Integration\Includes\Output::menu_page_url( C_TRAX_INTEGRATION_SLUG_SETUP ); ?>&step=<?php echo $nextStep['slug']; ?>" class="text-primary">
						Skip Step >>
					</a>
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