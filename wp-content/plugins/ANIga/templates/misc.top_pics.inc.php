<div class="gallery_clear">

	<h2><?php echo $numb; ?> <?php _e('most viewed pictures', 'aniga'); ?></h2>
    
	<br />
		
	<?php	
	if (!empty($gal_ret)){
		foreach ($gal_ret as $toppic) { ?>
			
			<a href="<?php echo aniga_get_permalink($toppic->parent_id, $toppic->pid, 'pid'); ?>" title="<?php echo $toppic->hits; ?> hits"><img src="<?php echo $toppic->path . 'thumb_' . $toppic->filename; ?>" border="0" class="gallery_thumb_border" height="<?php if ($i == 1) echo get_option('aniga_thumb_size'); elseif ($i == 2) echo 0.75*get_option('aniga_thumb_size'); else echo 0.5*get_option('aniga_thumb_size'); ?>px" align="bottom" alt="<?php echo $toppic->hits; ?> hits" /></a>&nbsp;
				
	<?php 		$i++;
			}
	} ?>

</div>