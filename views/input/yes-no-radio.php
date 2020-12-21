<?php
$checkedYes = ( $isYes ) ? 'checked="checked"' : '';
$checkedNo  = ( $isNo ) ? 'checked="checked"' : '';
$valYes     = ( $isBool ) ? 1 : 'Yes';
$valNo      = ( $isBool ) ? 0 : 'No';
?>
<label class="inline">
	<input type="radio" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $valYes; ?>>" <?php echo $checkedYes; ?> /> Yes
</label>
<label class="inline">
	<input type="radio" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $valNo; ?>>" <?php echo $checkedNo; ?> /> No
</label>