<?php
// Display Page "New Album"
//########################################################################

global $wpdb;

require_once("functions.php");
$aniga = new aniga_db();
$aniga_file = new aniga_file();
$aniga->user = $aniga->user();

$msg = "";
$msg .= $aniga->checkpath();

if ($_POST['event'] == 'album') {

	$parent_id = $_POST['cat'];
	$order = (int) $_POST['order'];

	if ($parent_id == 0) $msg .= __('You MUST select a parent Album!', 'aniga');
	
	else {

		$post_author = 1;
		$post_content = $_POST['longtitel'];
		$post_title = $_POST['titel'];
		$post_date = gmdate('Y-m-d H:i:s');
		$post_date_gmt = gmdate('Y-m-d H:i:s');
		$post_category = 1;
		$post_status = 'publish';
		$comment_status = $_POST['commentstat'];
		$ping_status = 'open';
		$post_parent = $parent_id;
		$post_type = 'page';
		$page_template = 'gallery-picture.php';

		$post_data = compact('post_content','post_title','post_date','post_date_gmt','post_author','post_category', 'post_status', 'comment_status', 'ping_status', 'post_status', 'post_parent', 'post_type', 'page_template');

		$post_data = add_magic_quotes($post_data);
		
		$post_ID = wp_insert_post($post_data);
		
		$level = $_POST['level'];
		
		$get_new_alb = $aniga->getupdatedpost($post_ID);
		
		$insert_new_alb = mysql_query("INSERT INTO $wpdb->aniga_alb (`id`, `name`, `desc`, `level`, `count`, `order`) VALUES ('$post_ID', '$get_new_alb->post_title', '$get_new_alb->post_content', '$level', '0', '$order')");
		
		$insert_new_rel = mysql_query("INSERT INTO $wpdb->aniga_rel (`id`, `parent_id`) VALUES ('$post_ID', '$parent_id')");
	
		$path = get_option('aniga_abspath').'00-gfx/';
		$name = 'album_'.$post_ID.'.jpg';
			
		copy(ABSPATH . 'wp-content/plugins/ANIga/noimage.jpg', $path.$name);
		
		$t_size = get_option('aniga_caimg_size');
		$c_size = get_option('aniga_thumb_csize');
	
		if (get_option('aniga_thumb_square') == 'yes') $crop = true;
		else $crop = false;
		
		if (okfiletype($_FILES['org']['name'], $aniga->allowed_pic)) {
		
			$msg .= $aniga_file->handle($_FILES['org']['tmp_name'], $name, $path, $t_size, $c_size, true, false, true, $crop);
			
		}
		else $msg .= "<p>".$_FILES['org']['name']." ". __('is not a picture file!', 'aniga')."</p>";

		//anigal_calc_gallery();
		
		$msg .= "<p>" . __('Album created:', 'aniga') . " <strong>'" . $get_new_alb->post_title . "'</strong></p>";
	}
}

$aniga_id = get_option('aniga_base-id');
$aniga_title = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE ID=$aniga_id");

?>
<div class="wrap">

	<h2><?php _e('Add new Album', 'aniga'); ?></h2>
	
<?php if ($msg!='') : ?>

	<div id="message" class="updated fade"><p><strong><?php print $msg; ?></strong></p></div>
	
<?php endif; ?>
	
<blockquote>

<script language="JavaScript" type="text/javascript">
<!--
function checkform ( form )
{
  if (form.org.value == "") {
    alert( "<?php _e('Please submit an Album image.', 'aniga'); ?>" );
    form.org.focus();
    return false ;
  }
  return true ;
}
//-->
</script>
	
	<form name="album" method="post" action="" enctype="multipart/form-data"  onsubmit="return checkform(this);" />
	<input name="event" type="hidden" value="album">
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Parent Album', 'aniga'); ?></legend>
		<select name="cat" style="width:95%">
			<option value="<?php echo get_option('aniga_base-id'); ?>"><?php echo $aniga_title->post_title; ?></option>
<?php
$aniga->getchildren(get_option('aniga_base-id'),'- '); 
foreach ($aniga->child_array as $i => $val) {
	echo '<option value="' . $i . '">' . $val['sep'] . $val['name'] . '</option>';
}	
?>			
		</select>
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Album title', 'aniga'); ?></legend>
		<input name="titel" type="text" style="width:95%">
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Album description', 'aniga'); ?></legend>
		<input name="longtitel" type="text" style="width:95%">
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Album image', 'aniga'); ?> <?php if (get_option('aniga_resize') == 'no') { sprintf(__('(max. %s px)', 'aniga'), get_option('aniga_caimg_size')); } ?></legend>
		<input type="file" name="org" />
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Order', 'aniga'); ?></legend>
		 <input name="order" type="text" value="0" size="5" > <?php _e('Sorting order from higher numbers to lower numbers.', 'aniga'); ?>
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Comments', 'aniga'); ?></legend>
		<select name="commentstat">
			<option value="open" selected="selected"><?php _e('enabled', 'aniga'); ?></option>
			<option value="closed"><?php _e('disabled', 'aniga'); ?></option>
		</select>
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Level', 'aniga'); ?></legend>
		<select name="level">
<?php
	foreach ($aniga_level as $k => $v) {
		echo '<option value="' . $k . '"';
		echo '>' . $v . '</option>';
	}
?>
		</select> <?php _e('is allowed to view the Pictures', 'aniga'); ?>
	</fieldset>
	
	<p><input type="submit" name="Submit" value="<?php _e('Create Album', 'aniga'); ?>"></p>
	
	</form>
	
</blockquote>

</div>

<?php aniga_admin_footer(); ?>