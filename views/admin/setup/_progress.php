<div class="row mt-2">
	<div class="col text-center">
		<ul class="row progressbar shadow-sm bg-dark rounded no-gutters">
			<?php
			$_currentSet = false;
			$lastStep = end($steps);
			foreach ( $steps as $step ) {
				if ( $currentStep['slug'] == $step['slug'] && $step['slug'] != $lastStep['slug'] ) {
					$class       = 'active bg-info text-light';
					$_currentSet = true;
					$status      = '<div class="spinner-grow" role="status"><span class="sr-only">In progress...</span></div>';
				} elseif ( ! $_currentSet || $currentStep['slug'] == $lastStep['slug']) {
					$class  = 'passed bg-success text-light';
					$status = '<i class="fa fa-check-circle"></i>';
				} else {
					$class  = 'pending bg-secondary text-dark';
					$status = '<i class="fa fa-times-circle"></i>';
				}
				?>
				<li class="col <?php echo $class; ?>">
					<div class="row no-gutters">
						<div class="icon col-1"><i class="fa <?php echo $step['icon']; ?>"></i></div>
						<div class="title text-left col-10"><?php echo $step['title']; ?></div>
						<div class="status col-1"><?php echo $status; ?></div>
					</div>
				</li>
				<?php
			}
			?>
		</ul>
	</div>
</div>