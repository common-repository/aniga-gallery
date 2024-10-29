<div class="gallery_aligncenter gallery_pad">
	<table cellpadding="0" cellspacing="0" border="0" class="gallery_slds_table" align="center"><tr><td valign="middle" align="center" class="gallery_slds_td"><a href="javascript:SLIDES.hotlink()" title="<?php _e('click to view picture', 'aniga'); ?>"><img name="SLIDESIMG" src="<?php echo $first->path."normal_".$first->filename; ?>" <?php if (get_option('aniga_slds_filter') == 'yes'): ?>class="gallery_slds_filter"<?php endif; ?> alt="<?php _e('Slideshow Picture', 'aniga'); ?>" /></a></td></tr></table>
	<div align="center"><small><a href="javascript:SLIDES.previous()">&laquo; <?php _e('previous', 'aniga'); ?></a> | <a href="javascript:SLIDES.pause()"><?php _e('pause', 'aniga'); ?></a> | <a href="javascript:SLIDES.play()"><?php _e('play', 'aniga'); ?></a> | <a href="javascript:SLIDES.shuffle()"><?php _e('random', 'aniga'); ?></a> | <a href="javascript:SLIDES.next()"><?php _e('next', 'aniga'); ?> &raquo;</a></small></div>
	</div>
		<script type="text/javascript">
		<!--
		if (document.images)
		{
			SLIDES.set_image(document.images.SLIDESIMG);
			SLIDES.set_textid("SLIDESTEXT");
			SLIDES.update();
		<?php if ($_GET['slide'] != '') echo "SLIDES.goto_slide(".$_GET['slide'].");\r\n"; ?>
			SLIDES.play(); //optional
		<?php if (get_option('aniga_slds_filter') == 'yes'): ?>
			// Create a function to ramp up the image opacity in Mozilla
			var fadein_opacity = 0.04;
			var fadein_img = SLIDES.image;
			function fadein(opacity) {
			  if (typeof opacity != 'undefined') { fadein_opacity = opacity; }
			  if (fadein_opacity < 0.99 && fadein_img && fadein_img.style &&
				  typeof fadein_img.style.MozOpacity != 'undefined') {
			
				fadein_opacity += .05;
				fadein_img.style.MozOpacity = fadein_opacity;
				setTimeout("fadein()", 50);
			  }
			}
			
			// Tell the slideshow to call our function whenever the slide is changed
			SLIDES.post_update_hook = function() { fadein(0.04); }
		<?php endif; ?>
		}
		//-->
		</script>
		<div class="gallery_pad">
			<div class="gallery_alt" align="center">
				<div class="gallery_line_h"><a href="javascript:SLIDES.hotlink()"><?php _e('view this picture with comments', 'aniga'); ?></a></div>
				<div id="SLIDESTEXT">&nbsp;</div>
				<noscript><?php _e('You need to enable javascript to view the Slideshow.', 'aniga'); ?></noscript>
			</div>
		</div>		