<?php
$checked = ( $isChecked ) ? 'checked="checked"' : '';
?>
<label>
	<input type="checkbox" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?> /> <?php echo $value; ?>
</label>