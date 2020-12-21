<?php
$attrs['class'] = ( isset( $attrs['class'] ) ) ? $attrs['class'] . ' form-control' : 'form-control';
switch ( $type ) {
	case 'select':
	case 'file':
	case 'textarea':
		$view = $type;
		break;
	default:
		$view = 'generic';
}
$str = '';
foreach ( $attrs as $_k => $_v ) {
	$str .= ' ' . $_k . '="' . $_v . '"';
}
$group       = ( $label && $labelPosition != 'top' ) ? 'input-group' : 'form-group';
$describedBy = 'inputGroup-' . $name;
?>
<div class="<?php echo $group; ?> mb-3">
	<?php if ( $label && $labelPosition != 'top' ) { ?>
		<div class="input-group-prepend">
			<span class="input-group-text" id="<?php echo $describedBy; ?>"><?php echo $label; ?></span>
		</div>
	<?php } else { ?>
		<label for="<?php echo $name; ?>"><?php echo $label; ?></label>
	<?php }

	\C_Trax_Integration\Views\View::make( 'input/' . $view . '.php', [ 'str' => $str, 'type' => $type, 'name' => $name, 'value' => $value, 'label' => $label, 'describedBy' => $describedBy ] ); ?>
</div>