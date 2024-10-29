<?php
// Display Page "Options"
//########################################################################

global $wpdb;

require_once("functions.php");
$aniga = new aniga_db();

$msg = "";

$aniga_id = get_option('aniga_base-id');
$aniga_title = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE ID=$aniga_id");

if ($_POST['event'] == 'options') {

	if ($_POST['base_id_chg'] == 'yes') {
		$ID = $_POST['base_id'];
		update_option('aniga_base-id', $ID, 'base wp ID for ANIga');
	}
	else $ID = $aniga_id;
	
	$post_title = $_POST['base_title'];
	$post_name = $_POST['base_title'];
	$post_data = compact('post_title', 'ID', 'post_name');
	$post_data = add_magic_quotes($post_data);
	$post_ID = wp_update_post($post_data);
	
	$aniga_title->post_title = $_POST['base_title'];
	
	$zip_mode = $_POST['zip_mode'];
	update_option('aniga_zip_mode', $zip_mode, 'zip mode');
	
	$img_mode = $_POST['img_mode'];
	update_option('aniga_img_mode', $img_mode, 'Picture link mode');
	
	$img_order = $_POST['img_order'];
	update_option('aniga_img_order', $img_order, 'Picture order mode');
	
	$img_sort = $_POST['img_sort'];
	update_option('aniga_img_sort', $img_sort, 'Picture sort mode');
	
	$dir_path = $_POST['dir_path'];
	update_option('aniga_dirpath', $dir_path, 'dir path');

	$abs_path = $_POST['abs_path'];
	update_option('aniga_abspath', $abs_path, 'absolute path');
	
	$columns = $_POST['columns'];
	update_option('aniga_colums', $columns, 'colums in thumbnail page');
	
	$resize = $_POST['resize'];
	if ($resize != 'yes') $resize = 'no';
	update_option('aniga_resize', $resize, 'resize Images');
	
	$resize_qual = $_POST['resize_qual'];
	update_option('aniga_resize_qual', $resize_qual, 'resize Quality');
	
	$thumb_size = $_POST['thumb_size'];
	update_option('aniga_thumb_size', $thumb_size, 'max Thumbnail size');
	
	$thumb_csize = $_POST['thumb_csize'];
	update_option('aniga_thumb_csize', $thumb_csize, 'max crop Thumbnail size');
	
	$thumb_square = $_POST['thumb_square'];
	if ($thumb_square != 'yes') $thumb_square = 'no';
	update_option('aniga_thumb_square', $thumb_square, 'Create square thumbnails');
	
	$thumb_ref = $_POST['thumb_ref'];
	if ($thumb_ref != 'yes') $thumb_ref = 'no';
	update_option('aniga_thumb_reflection', $thumb_ref, 'create an Apple like reflection on thumbnails');
	
	$thumb_border = $_POST['thumb_border'];
	update_option('aniga_thumb_reflection_b', $thumb_border, 'border for reflection');
	
	$caimg_size = $_POST['caimg_size'];
	update_option('aniga_caimg_size', $caimg_size, 'max Cat/Alb size');

	$norm_size = $_POST['norm_size'];
	update_option('aniga_norm_size', $norm_size, 'max normal Image size');

	$rolemgr = $_POST['rolemgr'];
	if ($rolemgr != 'yes') $rolemgr = 'no';
	update_option('aniga_rolemgr', $rolemgr, 'Use Role Manager Plugin');

	$use_css = $_POST['use_css'];
	if ($use_css != 'yes') $use_css = 'no';
	update_option('aniga_css', $use_css, 'external CSS style');

	$slds_prefetch = $_POST['slds_prefetch'];
	update_option('aniga_slds_prefetch', $slds_prefetch, 'ANIga slideshow prefetch');
	
	$slds_time = $_POST['slds_time'];
	update_option('aniga_slds_time', $slds_time, 'ANIga slideshow time');

	$slds_filter = $_POST['slds_filter'];
	if ($slds_filter != 'yes') $slds_filter = 'no';
	update_option('aniga_slds_filter', $slds_filter, 'ANIga slideshow filter');

	$del_pic = $_POST['del_pic'];
	if ($del_pic != 'yes') $del_pic = 'no';
	update_option('aniga_delete', $del_pic, 'ANIga delete pictures from filesystem');

	if ($_POST['aniga_trackback'] == "yes") {
	// idea from WP-OnlineCounter plugin - http://faked.org/blog/wp-onlinecounter/
		$trackback_body = "at my site \"".get_option('blogname')."\"";
		trackback("http://www.animalbeach.net/aniga/trackback/", "I just installed ANIga", $trackback_body, $ID);
		update_option('aniga_trackback_sent', 'yes', 'ANIga trackback sent');
		$msg .= "<p>" . __('trackback sent', 'aniga') . "</p>";
	}
	elseif ($_POST['aniga_trackback_no'] == "yes") {
		update_option('aniga_trackback_sent', 'yes', 'ANIga trackback sent');
		$msg .= "<p>" . __('trackback not sent', 'aniga') . "</p>";
	}
	
	$msg .= "<p>" . __('Options saved', 'aniga') . "</p>";
}

$msg .= $aniga->checkpath();

?>
<div class="wrap">
<h2><?php _e('ANIga Options', 'aniga'); ?></h2>

<?php
if ($msg!='') : ?>

	<div id="message" class="updated fade"><strong><?php print $msg; ?></strong></div>
	
<?php endif; ?>

<blockquote>

	<form name="album" method="post" action="">
	<input name="event" type="hidden" value="options">
	
	<blockquote>
	
		<fieldset class="gallery_fieldset">
			<legend><strong><?php _e('ANIga Gallery Name', 'aniga'); ?></strong></legend>
			<table width="100%"><tr><td width="50%">
				<fieldset class="gallery_fieldset">
					<legend><?php _e('Page title of gallery index page', 'aniga'); ?></legend>
					<input name="base_title" type="text" value="<?php echo $aniga_title->post_title; ?>" style="width:95%" />
				</fieldset>
			</td><td>
				<fieldset class="gallery_fieldset">
					<legend><input name="base_id_chg" type="checkbox" value="yes" /> <?php _e('WP ID (normally no need to change this!!)', 'aniga'); ?></legend>
					<input name="base_id" type="text" value="<?php echo $aniga_id; ?>" style="width:95%">
				</fieldset>
			</td></tr></table>
		</fieldset>
		
		<fieldset class="gallery_fieldset">
			<legend><strong><?php _e('Picture Directory Settings', 'aniga'); ?></strong></legend>
			<?php _e('This is the directory where your pictures and album images are stored.<br />Make sure this directory has write access (CHMOD 777).<br />Inside this directory you need to make these directories (with write access): &quot;<strong>00-gfx</strong>&quot; and &quot;<strong>00-single</strong>&quot;.', 'aniga'); ?>
			<table width="100%"><tr><td width="50%">
				<fieldset class="gallery_fieldset">
					<legend><?php _e('http url to Picture Directory', 'aniga'); ?></legend>
					<? echo bloginfo('url') . '/' . __('Your_folder', 'aniga') . '/'; ?>
					<input name="dir_path" type="text" value="<?php echo get_option('aniga_dirpath'); ?>" style="width:95%" />
				</fieldset>
			</td><td valign="top">
				<fieldset class="gallery_fieldset">
					<legend><?php _e('Absolute path to Picture Directory', 'aniga'); ?></legend>
					<? echo str_replace("\\", "/", ABSPATH) . __('Your_folder', 'aniga') . '/'; ?>
					<input name="abs_path" type="text" value="<?php echo get_option('aniga_abspath'); ?>" style="width:95%" />
				</fieldset>
			</td></tr></table>
			</fieldset>
			
			<fieldset class="gallery_fieldset">
				<legend><strong><?php _e('Picture archive unzipping method', 'aniga'); ?></strong></legend>
				<?php _e('According to your server configuration you should choose', 'aniga'); ?>	<strong> <?php 
				if (ini_get('safe_mode') || ini_get('safe_mode') == 'on') _e('\'safe mode\' on, no zip functionality', 'aniga');
				else {
					if (function_exists('zip_open')) {
					   _e('php zip library', 'aniga');
					} else {
						if (function_exists('exec')) {
						   _e('m_zip library for .php files', 'aniga');
						} else {
						   _e('m_zip library for .phpx files', 'aniga');
						}
					}
				}		
				?>
				</strong>
				<blockquote>
					<?php $zip_mode_opt = get_option("aniga_zip_mode"); ?>
					<p>
					<input name="zip_mode" type="radio" value="zip" <?php if ($zip_mode_opt == 'zip') echo 'checked="checked"'; ?> /> 
					<?php _e('php zip library (if your server has php zip library enabled).', 'aniga'); ?></p>
					<p>
					<input name="zip_mode" type="radio" value="mzip" <?php if ($zip_mode_opt == 'mzip') echo 'checked="checked"'; ?> /> 
					<?php _e('m_zip library for .php files (if php zip library disabled).', 'aniga'); ?></p>
					<p>
					<input name="zip_mode" type="radio" value="mzipx" <?php if ($zip_mode_opt == 'mzipx') echo 'checked="checked"'; ?> /> 
					<?php _e('m_zip library for .phpx files (if php zip library disabled and you cant use exec() in .php files).', 'aniga'); ?></p>
					<p>
					<input name="zip_mode" type="radio" value="none" <?php if ($zip_mode_opt == 'none') echo 'checked="checked"'; ?> /> 
					<?php _e('\'safe mode\' on, no zip functionality', 'aniga'); ?>
					</p>
				</blockquote>
			</fieldset>
			
			<fieldset class="gallery_fieldset">
				<legend><input name="resize" type="checkbox" value="yes" <?php if (get_option('aniga_resize') == 'yes') echo 'checked="checked"'; ?> /> <strong><?php _e('Resize Pictures automatically (recommended)', 'aniga'); ?></strong></legend>
				<?php _e('GD lib 2.x needs to be installed! Javascript must be enabled for upload! Supported image types: <strong>jpg</strong>, <strong>png</strong>, <strong>gif</strong>', 'aniga'); ?>
				<table width="100%"><tr><td width="50%">
					<fieldset class="gallery_fieldset">
						<legend><?php _e('Resize Quality', 'aniga'); ?></legend>
						<?php _e('Quality for jpg resizing (from 0 to 100).', 'aniga'); ?><br />
						<input name="resize_qual" type="text" value="<?php echo get_option('aniga_resize_qual'); ?>" style="width:95%">
					</fieldset>
				</td><td>
					<fieldset class="gallery_fieldset">
						<legend><?php _e('Max. album image size', 'aniga'); ?></legend>
						<?php _e('Image size in px for Album Images.', 'aniga'); ?><br />
						<input name="caimg_size" type="text" value="<?php echo get_option('aniga_caimg_size'); ?>" style="width:95%">
					</fieldset>
				</td></tr><tr><td>
					<fieldset class="gallery_fieldset">
						<legend><?php _e('Max. Normal picture size', 'aniga'); ?></legend>
						<?php _e('Image size in px for the &quot;normal_&quot; pictures, that show up in the post.<br />The original picture will not be resized and will be linked from this one!', 'aniga'); ?><br />
						<input name="norm_size" type="text" value="<?php echo get_option('aniga_norm_size'); ?>" style="width:95%">
					</fieldset>
                    <fieldset class="gallery_fieldset">
						<legend><input name="thumb_ref" type="checkbox" value="yes" <?php if (get_option('aniga_thumb_reflection') == 'yes')echo 'checked="checked"'; ?> /> <?php _e('Create reflection from a thumbnail', 'aniga'); ?></legend>
						<?php _e('If checked, thumbnails will have an Apple style reflection.', 'aniga'); ?><br />
						<?php _e('Border color rendered into the thumbnail.', 'aniga'); ?><br />
						#<input name="thumb_border" type="text" style="width:95%" value="<?php echo get_option('aniga_thumb_reflection_b'); ?>" maxlength="6" >
					</fieldset>
				</td><td valign="top">
					<fieldset class="gallery_fieldset">
						<legend><?php _e('Max. thumbnail size', 'aniga'); ?></legend>
						<?php _e('Image size in px for thumbnails.', 'aniga'); ?><br />
						<input name="thumb_size" type="text" value="<?php echo get_option('aniga_thumb_size'); ?>" style="width:95%">
					</fieldset>
					<fieldset class="gallery_fieldset">
						<legend><input name="thumb_square" type="checkbox" value="yes" <?php if (get_option('aniga_thumb_square') == 'yes')echo 'checked="checked"'; ?> /> <?php _e('Create square thumbnails', 'aniga'); ?></legend>
						<?php _e('If checked, thumbnails will crop images to a square.', 'aniga'); ?><br />
						<?php _e('Image size in px for thumbnails before square crop.', 'aniga'); ?><br />
						<input name="thumb_csize" type="text" value="<?php echo get_option('aniga_thumb_csize'); ?>" style="width:95%">
					</fieldset>
				</td></tr></table>
			</fieldset>
					
			<table width="100%"><tr><td width="50%" valign="top">
			
			<fieldset class="gallery_fieldset">
				<legend><strong><?php _e('Slideshow settings', 'aniga'); ?></strong></legend>
				<?php _e('Javascript must be activated for slideshow.', 'aniga'); ?>
				<fieldset class="gallery_fieldset">
					<legend><?php _e('Time between Pictures', 'aniga'); ?></legend>
					<?php _e('change picture every x milliseconds', 'aniga'); ?><br />
					<input name="slds_time" type="text" value="<?php echo get_option('aniga_slds_time'); ?>" style="width:95%">
				</fieldset>
				<fieldset class="gallery_fieldset">
					<legend><?php _e('Preload Pictures', 'aniga'); ?></legend>
					<?php _e('-1 for all pictures to preloade (not recommended for large amount or big picture sizes)', 'aniga'); ?><br />
					<input name="slds_prefetch" type="text" value="<?php echo get_option('aniga_slds_prefetch'); ?>" style="width:95%">
				</fieldset>
				<p><input name="slds_filter" type="checkbox" value="yes" <?php if (get_option('aniga_slds_filter') == 'yes')echo 'checked="checked"'; ?> /> 
				<?php _e('Use fading filter for picture change (may not work on MAC, Mozilla &amp; Opera)', 'aniga'); ?></p>
			</fieldset>
			
	<!--		<fieldset class="gallery_fieldset">
				<legend><input name="rolemgr" type="checkbox" value="yes" <?php if (get_option('aniga_rolemgr') == 'yes')echo 'checked="checked"'; ?> /> <strong><?php _e('Use Role Manager', 'aniga'); ?></strong></legend>
				<?php _e('If checked, the <a href=\'http://redalt.com/wiki/Role+Manager\'>Role Manager Plugin</a> must be installed! You should create the new Capabilites \'Gallery Manage\', \'Gallery Album\', \'Gallery Insert\', \'Gallery Options\' <br />If unchecked only admins (User Level 10) can manage ANIga!', 'aniga'); ?>
			</fieldset>-->
			
			<fieldset class="gallery_fieldset">
				<legend><input name="use_css" type="checkbox" value="yes" <?php if (get_option('aniga_css') == 'yes')echo 'checked="checked"'; ?> /> <strong><?php _e('Use external CSS style sheet', 'aniga'); ?></strong></legend>
				<?php _e('Use the gallery-style.css file for css styles.', 'aniga'); ?>
			</fieldset>
						
			</td><td valign="top">
			
			<fieldset class="gallery_fieldset">
				<legend><strong><?php _e('Picture Link Handling', 'aniga'); ?></strong></legend>
				<?php $img_hand = get_option("aniga_img_mode"); ?>
				<p>
				<input name="img_mode" type="radio" value="none" <?php if ($img_hand == 'none') echo 'checked="checked"'; ?> /> 
				<?php _e('no link to original Picture.', 'aniga'); ?></p>
				<p>
				<input name="img_mode" type="radio" value="link" <?php if ($img_hand == 'link') echo 'checked="checked"'; ?> /> 
				<?php _e('normal link to original Picture, opens in same window.', 'aniga'); ?></p>
				<p>
				<input name="img_mode" type="radio" value="js" <?php if ($img_hand == 'js') echo 'checked="checked"'; ?> /> 
				<?php _e('javascript pop-up to original Picture.', 'aniga'); ?></p>
				<p>
				<input name="img_mode" type="radio" value="lightbox" <?php if ($img_hand == 'lightbox') echo 'checked="checked"'; ?> /> 
				<?php _e('\'lightbox\' support. Adds <strong>rel=\'lightbox\'</strong> to link.', 'aniga'); ?></p>
			</fieldset>
			
			<fieldset class="gallery_fieldset">
				<legend><strong><?php _e('Picture sorting & order', 'aniga'); ?></strong></legend>
				<?php $img_order = get_option("aniga_img_order"); ?>
				<?php $img_sort = get_option("aniga_img_sort"); ?>
				<p>
				<input name="img_sort" type="radio" value="filename" <?php if ($img_sort == 'filename') echo 'checked="checked"'; ?> /> 
				<?php _e('sort by filename', 'aniga'); ?></p>
				<p>
				<input name="img_sort" type="radio" value="hits" <?php if ($img_sort == 'hits') echo 'checked="checked"'; ?> /> 
				<?php _e('sort by hits', 'aniga'); ?></p>
				<p>
				<input name="img_sort" type="radio" value="pic_date" <?php if ($img_sort == 'pic_date') echo 'checked="checked"'; ?> /> 
				<?php _e('sort by adding date', 'aniga'); ?></p>
				<p>
				<input name="img_order" type="radio" value="DESC" <?php if ($img_order == 'DESC') echo 'checked="checked"'; ?> /> 
				<?php _e('order descending', 'aniga'); ?></p>
				<p>
				<input name="img_order" type="radio" value="ASC" <?php if ($img_order == 'ASC') echo 'checked="checked"'; ?> /> 
				<?php _e('order ascending', 'aniga'); ?></p>
			</fieldset>
			
			<fieldset class="gallery_fieldset">
				<legend><input name="del_pic" type="checkbox" value="yes" <?php if (get_option('aniga_delete') == 'yes')echo 'checked="checked"'; ?> /> 
				<strong><?php _e('Delete Pictures', 'aniga'); ?></strong></legend>
				<?php _e('Delete Pictures and album images from filesystem, not only from database - then they are really gone!', 'aniga'); ?>
			</fieldset>
			
			<fieldset class="gallery_fieldset">
				<legend><strong><?php _e('Columns on thumbnail page', 'aniga'); ?></strong></legend>
				<input name="columns" type="text" value="<?php echo get_option('aniga_colums'); ?>" style="width:95%">
			</fieldset>
						
			</td></tr></table>
			
			<?php if (get_option('aniga_trackback_sent') != 'yes'): ?>
			
			<fieldset class="gallery_fieldset">
				<legend><strong><?php _e('Send Trackback', 'aniga'); ?></strong></legend>
				<input name="aniga_trackback" type="checkbox" value="yes" /> <?php _e('Send a Trackback <strong>once</strong> to the <a href=\'http://www.animalbeach.net/aniga/\' >author</a> of ANIga gallery to notify him that you installed it.', 'aniga'); ?><br />
				<input name="aniga_trackback_no" type="checkbox" value="yes" /> <?php _e('Do not send a Trackback and do not show this option any more!', 'aniga'); ?>
			</fieldset>
			
			<?php endif; ?>	
			
		</blockquote>
		<p>
			<input type="submit" name="Submit" value="<?php _e('Update Options', 'aniga'); ?>">
		</p>
		</form>
	</blockquote>
</div>

<?php
aniga_admin_footer();
?>