<?php
// Set the pre message string
switch ( $type ) {
	case 'success':
		$icon = '<i class="fa fa-check"></i> Success: ';
		break;
	case 'error':
		$icon = '<i class="fa fa-exclamation-triangle"></i> Error: ';
		break;
	case 'warning':
		$icon = '<i class="fa fa-warning"></i> Warning: ';
		break;
	default:
		$icon = '<i class="fa fa-info"></i> Info: ';
		break;
}

// Display if there is something to display
if ( $message != '' ) {
	?>
	<div id="system-message" class="notice notice-<?php echo $type; ?> is-dismissible">
		<p><strong><?php echo $icon; ?><?php echo $message; ?></strong></p>
		<?php if ( $exception ) { ?>
			<p><?php echo $exception; ?></p>
		<?php } ?>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text">Dismiss this notice.</span>
		</button>
	</div>
	<?php
}
?>