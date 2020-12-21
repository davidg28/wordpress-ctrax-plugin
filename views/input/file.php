<?php

use C_Trax_Integration\Views\View as View;

$fileTypes = [];
?>

<div id="file-contain">
	<label>
		<input type="file" name="<?php echo $name; ?>" id="<?php echo $id; ?>" accept="<?php echo $fileTypes; ?>>"/>
		<br/>
		<small><i>*Allowed file types: <?php echo $fileTypes; ?></i></small>
	</label>

	<h3>Current File</h3>
	<ul class="files">
		<?php
		if ( $current ) {
			echo View::make( 'misc/file-list.php', [ 'file' => $current, 'id' => $id ] );
		} else {
			echo '<i>No file currently set for this attachment.</i>';
		}
		?>
	</ul>
</div>