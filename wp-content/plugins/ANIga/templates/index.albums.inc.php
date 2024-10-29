<div class="gallery_clear gallery_list">
	
		<div class="<?php echo $this->alg; // align album picture left and right ?>">
		
			<a href="<?php echo get_permalink($id); ?>">
			
				<img src="<?php echo $this->path.$id; ?>.jpg" border="0" width="<?php echo get_option('aniga_caimg_size'); ?>" class="gallery_thumb_border" alt="<?php echo $description; ?>" />
			
			</a>
			
		</div>
		
		<div class="<?php echo $this->alg.' '.$this->talg; // align album picture left and right ?> gallery_list_p">
		
			<a href="<?php echo get_permalink($id); ?>"><?php echo $name; ?></a>
			
			<br />
			
			<span class="gallery_meta"><?php echo $description; ?>
				
				<br />
				
				<small>
				
					<?php echo $meta['albums']; ?> <?php _e('galleries', 'aniga'); ?> | <?php echo $meta['all_pics']; ?> <?php _e('pictures', 'aniga'); ?> | <?php echo $meta['all_hits']; ?> <?php _e('views', 'aniga'); ?> | <?php echo $meta['all_comments']; ?> <?php echo __('comments', 'aniga'); ?>
				
				</small>
				
			</span>
			
		</div>
	
</div>
