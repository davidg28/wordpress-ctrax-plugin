<?php

use C_Trax_Integration\Includes\Output as Output;

$wpnonce = \wp_create_nonce( 'upload-file-' . $entry->id );
?>

<li class="<?php echo $id; ?>">
	<?php echo Output::get_file_icon( $file['type'] ); ?> <?php echo $file['name']; ?>
	(<?php echo Output::size_format( $file['size'], 2 ); ?>) - Uploaded moments ago
</li>