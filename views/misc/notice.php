<div class="row mt-2">
	<div class="col">
		<div class="alert alert-<?php echo $type; ?>" role="alert">
			<strong><i class="fa <?php echo (isset($icon)) ? $icon : 'fa-exclamation-circle'; ?>"></i> Attention!</strong> <?php echo $message; ?>
		</div>
	</div>
</div>