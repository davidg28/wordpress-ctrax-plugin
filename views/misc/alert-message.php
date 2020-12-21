<?php
switch ( $type ) {
	case 'success':
	case 'warning':
	case 'info':
		$status = ucwords( $type );
		break;
	case 'danger':
	case 'error':
		$status = 'Error';
		$type   = 'danger';
		break;
	default:
		$status = 'Info';
		break;
}
?>
<div class="alert alert-<?php echo $type; ?> alert-dismissible fade show" role="alert">
	<?php if ( isset( $heading ) ) { ?>
		<h4><?php echo $heading; ?></h4>
	<?php } ?>
	<strong><?php echo $status; ?>:</strong> <?php echo $message; ?>
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>