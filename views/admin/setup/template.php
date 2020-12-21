<div class="wrap">
	<div class="entry">
		<div class="container-fluid">
			<div class="row no-gutters" style="background-color: #3B8525;">
				<div class="col text-center">
					<img src="<?php echo C_TRAX_INTEGRATION_ASSETS_URL . 'images/c-trax-logo.png'; ?>" height="50px" alt="<?php echo C_TRAX_INTEGRATION_PROJECT; ?>"/>
				</div>
			</div>
			<?php
			if ( $currentStep['slug'] != 'setup' ) {
				\C_Trax_Integration\Views\View::make( 'admin/setup/_progress.php', [ 'steps' => $steps, 'currentStep' => $currentStep, 'nextStep' => $nextStep ] );
			}
			?>
			<?php \C_Trax_Integration\Views\View::make( 'admin/setup/' . $currentStep['slug'] . '.php', [ 'steps' => $steps, 'currentStep' => $currentStep, 'nextStep' => $nextStep, 'ctraxUser' => $ctraxUser ] ); ?>
		</div>
	</div>
</div>