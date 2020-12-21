<?php

use C_Trax_Integration\Includes\Output as Output;

$wpnonce = \wp_create_nonce( 'upload-file-' . $entry->id );
$file    = \maybe_unserialize( $entry->FILE_DATA );
?>

<li class="<?php echo $entry->UPLOAD_ID; ?>">
	<?php echo Output::get_file_icon( $file['type'] ); ?> <?php echo $file['name']; ?>
	(<?php echo Output::size_format( $file['size'], 2 ); ?>) - Uploaded <?php echo Output::nice_date_time( $entry->updated_at ); ?>
</li>