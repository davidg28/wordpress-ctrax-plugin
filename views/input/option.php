<?php
$selected = ( $isSelected ) ? 'selected="selected"' : '';
?>
<option value="<?php echo $value; ?>" <?php echo $selected; ?>>
	<?php echo $display; ?>
</option>
