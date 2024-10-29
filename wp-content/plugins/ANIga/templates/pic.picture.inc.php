<div class="gallery_aligncenter gallery_pad">
		
	<table cellpadding="0" cellspacing="0" border="0" class="gallery_slds_table" align="center"><tr><td valign="middle" align="center" class="gallery_slds_td"><?php echo $this->pic_link; ?></td></tr></table>
		
	<div align="center"><small>
			
			<a href="<?php echo aniga_get_permalink($this->id, $cur_page, 'page', ''); ?>#thumb_nav" title="<?php _e('back to thumbnails for', 'aniga'); ?> <?php the_title(); ?>">
			<? _e('back to thumbnails', 'aniga'); ?>
			</a> | 
			<a href="<?php echo aniga_get_permalink($this->id, $current_pic - 1, 'slideshow=start&amp;ssid='.$this->id.'&amp;slide'); ?>" title="<?php _e('view all pictures as a slideshow', 'aniga'); ?>"><? _e('start slideshow', 'aniga'); ?></a> | 
			<?php echo $picture->filename . ' | ' . $picture->width . 'x' . $picture->height . 'px | ' . $hits; ?> <?php _e('views', 'aniga'); ?>
			
	</small></div>
		
</div>