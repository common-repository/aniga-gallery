<?php 
// Display Page "ANIga"
//########################################################################

require_once("functions.php");
$aniga = new aniga_db();
$aniga_file = new aniga_file();
$aniga->user = $aniga->user();

$aniga_id = get_option('aniga_base-id');

$msg = "";
$msg .= $aniga->checkpath();
?>

<div class="wrap">

<?php
if ($_GET['event'] == 'edit_alb') { // print form to change album

$wp_id = $_GET['wpid'];
$alb_return = $aniga->getalbum($wp_id);
$resize = get_option('aniga_resize');
$aniga_title = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE ID=$aniga_id");
$aniga->getchildren_wo_self(get_option('aniga_base-id'),'- ', $wp_id);
$parent_id = $aniga->getparentalbum($wp_id);
?>

<h2><?php _e('edit Album', 'aniga'); ?> "<?php echo $alb_return->name; ?>"</h2>


<?php 

$pictures_ret = $aniga->getpics($wp_id, get_option("aniga_img_sort"), get_option("aniga_img_order"));

if ($_POST['update'] == 'do') { // start update pictures

?>

<script type="text/javascript" src="../wp-content/plugins/ANIga/mootools.v1.11.js"></script>

<script type="text/javascript">

var images = new Array()
<?php

$http_path = get_option('aniga_dirpath');
$abs_path = get_option('aniga_abspath');

	$i = 0;
	$insert_pic = '<span style="color:green">'.__('successful', 'aniga').'</span>';
	if (!empty($pictures_ret)) {
		foreach ($pictures_ret as $pictures) {
		
		$path = str_replace($http_path, $abs_path, $pictures->path);
		
			if (is_file($path.'thumb_'.$pictures->filename)) unlink($path.'thumb_'.$pictures->filename);
			if (is_file($path.'normal_'.$pictures->filename)) unlink($path.'normal_'.$pictures->filename);

		$files[$i] = array('name' => $pictures->filename, 'xsize' => $pictures->width, 'ysize' => $pictures->height, 'db' => $insert_pic, 'path' => $path);
		echo 'images['.$i.'] = "'.$pictures->filename.'"';
		echo "\n";
		
		$i++;
		}
	}

?>

function addLoadEventFunc(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      if (oldonload) {
        oldonload();
      }
      func();
    }
  }
};

<?php echo "addLoadEventFunc(goResize());"; 

$t_size = get_option('aniga_thumb_size');
$n_size = get_option('aniga_norm_size');
$c_size = get_option('aniga_thumb_csize');
$crop = get_option('aniga_thumb_square');
$r_qual = get_option('aniga_resize_qual');
$refl = get_option('aniga_thumb_reflection');
$border = get_option('aniga_thumb_reflection_b');

?>

function goResize(){
	
	for (i=0;i<images.length;i++)
	{
	<?php $j = 0; ?>
		new Ajax('../wp-content/plugins/ANIga/resize.inc.php',{postBody:'file=' + images[i] + '&path=<?php echo $files[$j]["path"].'/&crop='.$crop.'&csize='.$c_size.'&rsize='.$t_size.'&mode=thumb&qual='.$r_qual.'&refl='.$refl.'&border='.$border; ?>', update:'cont_thumb_' + i}).request();
		new Ajax('../wp-content/plugins/ANIga/resize.inc.php',{postBody:'file=' + images[i] + '&path=<?php echo $files[$j]["path"].'/&crop=no&csize='.$c_size.'&rsize='.$n_size.'&mode=normal&qual='.$r_qual; $j++; ?>', update:'cont_norm_' + i}).request();
	}
};

</script>

	<?php _e('Updating Pictures... please wait', 'aniga'); ?>

	<div id="message" class="updated fade">
	
	<p><?php _e('Please be patient. Do not close this window, until the process is finished!', 'aniga'); ?></p>

<table align="center" width="90%" border="0" cellpadding="3" cellspacing="0">
<tr><th><?php _e('Picture', 'aniga'); ?></th><th><?php _e('Size', 'aniga'); ?></th><th><?php _e('Database Insert', 'aniga'); ?></th><th><?php _e('Thumbnail', 'aniga'); ?></th><th><?php _e('Normal Picture', 'aniga'); ?></th></tr>

<?php


foreach ($files as $i => $val) {

?>
<tr>
	<td><div id="container<?php echo $i; ?>"><?php echo $val['name']; ?></div></td>
	<td align="center"><?php echo $val['xsize']; ?> x <?php echo $val['ysize']; ?> px
	<td align="center"><div id="cont_db_<?php echo $i; ?>">insert <?php echo $val['db']; ?></div></td>
	<td align="center"><div id="cont_thumb_<?php echo $i; ?>"><?php if ($noresize != 'yes' && get_option('aniga_resize') == 'yes') _e('waiting for resize ...', 'aniga'); else _e('resize disabled', 'aniga'); ?></div></td>
	<td align="center"><div id="cont_norm_<?php echo $i; ?>"><?php if ($noresize != 'yes' && get_option('aniga_resize') == 'yes') _e('waiting for resize ...', 'aniga'); else _e('resize disabled', 'aniga'); ?></div></td>
</tr>
<?php
}
?>
</table>
</div>

<?php
}
?>

<blockquote>

	<form name="album" method="post" action="?page=ANIga/admin_manage.php&event=edited_alb" enctype="multipart/form-data">			
	<input name="wpid" type="hidden" value="<?php echo $wp_id; ?>" />
	<input name="albid" type="hidden" value="<?php echo $alb_return->id; ?>" />
	<input name="old_parent_id" type="hidden" value="<?php echo $parent_id; ?>" />
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Album title', 'aniga'); ?></legend>
		<input name="titel" type="text" value="<?php echo $alb_return->name; ?>" style="width:95%">
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Album description', 'aniga'); ?></legend>
		<input name="longtitel" type="text" value="<?php echo $alb_return->desc; ?>" style="width:95%">
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><input name="changecat" type="checkbox" value="yes" /> <?php _e('change parent Album', 'aniga'); ?></legend>
		<select name="parent_id" style="width:95%">
			<option value="<?php echo get_option('aniga_base-id'); ?>"><?php echo $aniga_title->post_title; ?></option>
<?php

foreach ($aniga->child_array as $i => $val) {
	if ($i != $wp_id) {
		echo '<option value="' . $i . '"';
		if ($i == $parent_id) echo ' selected="selected"';
		echo '>' . $val['sep'] . $val['name'] . '</option>';
	}
}

?>			
		</select>
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><input name="overwrite" type="checkbox" value="yes" /> <?php _e('Overwrite Album image', 'aniga'); ?> <?php if ($resize == 'no') { sprintf(__('(max. %s px)', 'aniga'), get_option('aniga_thumb_size')); } ?></legend> 
		<img src="<?php echo get_option('aniga_dirpath'); ?>00-gfx/album_<?php echo $alb_return->id; ?>.jpg" class="gallery_border"/><br />
		<input type="file" name="org" />
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Order', 'aniga'); ?></legend>
		 <input name="order" type="text" value="<?php echo $alb_return->order; ?>" size="5" > <?php _e('Sorting order from higher numbers to lower numbers.', 'aniga'); ?>
	</fieldset>

	<fieldset class="gallery_fieldset">
		<legend><?php _e('Comments', 'aniga'); ?></legend>
		<?php $comstat = $aniga->getupdatedpost($wp_id); ?>
		 <select name="commentstat">
			<option value="open" <?php if ($comstat->comment_status == 'open') echo 'selected="selected"'; ?>><?php _e('enabled', 'aniga'); ?></option>
			<option value="closed" <?php if ($comstat->comment_status == 'closed') echo 'selected="selected"'; ?>><?php _e('disabled', 'aniga'); ?></option>
		</select>
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Level', 'aniga'); ?></legend>
		<select name="level">
<?php
	foreach ($aniga_level as $k => $v) {
		echo '<option value="' . $k . '"';
		if ($alb_return->level == $k) { echo ' selected="selected"'; }
		echo '>' . $v . '</option>';
	}		
?>
		</select> <?php _e('is allowed to view this Album ', 'aniga'); ?>
	</fieldset>
	
	<p><input type="submit" name="Submit" value="<?php _e('Update Album', 'aniga'); ?>"> <input type="button" value="<?php _e('Cancel', 'aniga'); ?>" onclick="history.back()" /></p>
	
	</form>
    
<?php if (!empty($pictures_ret)) { ?>
    
	<p>&nbsp;</p>

    <h2><?php _e('Regenerate thumbnails and normal pictures', 'aniga'); ?></h2>
    
    

    	<form name="updatepics" method="post" action="?page=ANIga/admin_manage.php&event=edit_alb&wpid=<?php echo $wp_id; ?>">			
		<input name="update" type="hidden" value="do" />
		<input type="submit" name="Submit" value="<?php _e('Update Pictures for this Album', 'aniga'); ?>">
		</form>
        
     </div>
<?php } ?> 
    
</blockquote>

<?php

}


elseif ($_GET['event'] == 'edited_alb') { // write changes for album in db

	$post_content = $_POST['longtitel'];
	$post_title = $_POST['titel'];
	$post_name = $_POST['titel'];
	$ID = $_POST['wpid'];
	$parent_id = $_POST['parent_id'];
	$overwrite = $_POST['overwrite'];
	$changecat = $_POST['changecat'];
	$old_parent_id = $_POST['old_parent_id'];
	$albid = $_POST['albid'];
	$comment_status = $_POST['commentstat'];
	$level = $_POST['level'];
	$order = (int) $_POST['order'];
	
	if ($overwrite == 'yes') {

		$path = get_option('aniga_abspath').'00-gfx/';
		$name = 'album_'.$ID.'.jpg';
					
		$t_size = get_option('aniga_caimg_size');
		$c_size = get_option('aniga_thumb_csize');
	
		if (get_option('aniga_thumb_square') == 'yes') $crop = true;
		else $crop = false;
		
		if (okfiletype($_FILES['org']['name'], $aniga->allowed_pic)) {
		
			$msg .= $aniga_file->handle($_FILES['org']['tmp_name'], $name, $path, $t_size, $c_size, true, false, true, $crop);
			
		}
		else $msg .= "<p>".$_FILES['org']['name']." is not a picture file!</p>";
			
	}

	if ($changecat == 'yes') {
		if ($cat_ID == '0') {
			$msg .= "<p>" . __('You MUST select a Category, if you want to move this Album...', 'aniga') . "</p>";
			$post_data = compact('post_content','post_title', 'ID', 'comment_status');
		}
		else {
			$update_albcat = mysql_query("UPDATE `$wpdb->aniga_rel` SET `parent_id` = '$parent_id' WHERE `id` = '$ID' LIMIT 1");
			$post_parent = $parent_id;
			$post_data = compact('post_content', 'post_title', 'post_name', 'ID', 'post_parent', 'comment_status');
			//anigal_calc_gallery();
			$msg .= "<p>" . __('Album successfully moved...', 'aniga') . "</p>";
		}
	}
	else {
		$post_data = compact('post_content','post_title', 'post_name', 'ID', 'comment_status');
	}

	$post_data = add_magic_quotes($post_data);
	$post_ID = wp_update_post($post_data);
		
	$get_new_cat = $aniga->getupdatedpost($ID);
	$update_alb = mysql_query("UPDATE `$wpdb->aniga_alb` SET `name` = '$get_new_cat->post_title', `desc` = '$get_new_cat->post_content', `level` = '$level', `order` = '$order' WHERE id = '$ID'");
		
	$msg .= "<p>" . __('Album edited:', 'aniga') . " '$get_new_cat->post_title'</p>";
	
	anigal_mng_gallery($msg);
}


elseif ($_GET['event'] == 'album') { // call anigal_mng_pictures($msg, $alb_id) from $_GET

	$ID = (int) $_GET['wpid'];
	anigal_mng_pictures($msg, $ID);
}


elseif ($_GET['event'] == 'mng_album') { // call anigal_mng_pictures($msg, $alb_id) from $_POST

	if ($_POST['albid'] != 0) $ID = $_POST['albid'];
	else $ID = $_POST['wpid'];

	anigal_mng_pictures($msg, $ID);
}


elseif ($_GET['event'] == 'edited_pic') { // write changes for picture in db 

	$new_id = (int) $_POST['albid'];
	$old_id = (int) $_POST['wpid'];

	if ($_POST['multiple_edit']) {
	
		if ( !empty( $_POST['edit_img'] ) ) :
			$i = 0;
			foreach ($_POST['edit_img'] as $pid) :
				$pid = (int) $pid;
				$filename = $_POST['filename'.$pid];
				$hits = $_POST['hits'.$pid];
				$caption = $_POST['caption'.$pid];
				$caption = htmlentities($caption, ENT_QUOTES, 'UTF-8');
			
				if ($new_id != $old_id) {
					$new_alb = $aniga->getalbum($new_id);
					$move_pic = mysql_query("UPDATE $wpdb->aniga_pic SET `parent_id` = '$new_alb->id' WHERE pid = $pid");
					$move_com_req = "SELECT * FROM $wpdb->comments WHERE comment_pid_ID = '$pid'";
					$move_com_ret = $wpdb->get_results($move_com_req);
					if ($move_com_ret) {
						$count = $wpdb->num_rows;
						$update_com = mysql_query("UPDATE $wpdb->comments SET comment_post_ID = '$new_id' WHERE comment_pid_ID = $pid");
						$new_post = $aniga->getupdatedpost($new_id);
						$old_post = $aniga->getupdatedpost($old_id);
						$comment_count_new = $new_post->comment_count + $count;
						$comment_count_old = $old_post->comment_count - $count;		
						$update_post_new = mysql_query("UPDATE $wpdb->posts SET comment_count = '$comment_count_new' WHERE ID = $new_id");
						$update_post_old = mysql_query("UPDATE $wpdb->posts SET comment_count = '$comment_count_old' WHERE ID = $old_id");
					}
					$msg .= "<p>" . sprintf(__('Picture \'%1$s\' moved to Album \'%2$s\'.', 'aniga'), $filename, $new_alb->name) . "</p>";
				}
			
				$update_pic = mysql_query("UPDATE $wpdb->aniga_pic SET hits = '$hits', caption = '$caption' WHERE pid = $pid");
	
				$msg .= "<p>" . sprintf(__('Picture \'%s\' updated.', 'aniga'), $filename) . "</p>";			
				$i++;
			endforeach;
			
			//anigal_calc_gallery();
		endif;
	}

	elseif ($_POST['multiple_level']) {

		if ( !empty( $_POST['edit_img'] ) ) :
		$i = 0;
		foreach ($_POST['edit_img'] as $pid) :
			$pid = (int) $pid;
			$level = $_POST['mlevel'];
		
			$update_pic = mysql_query("UPDATE $wpdb->aniga_pic SET level = '$level' WHERE pid = $pid");
		
			$i++;
		endforeach;
		$msg .= "<p>" . sprintf(__('level for %s pictures updated', 'aniga'), $i) . "</p>";	
	endif;
	}

	elseif ($_POST['multiple_delete']) {

		if ( !empty( $_POST['edit_img'] ) ) :
			$i = 0;
			foreach ($_POST['edit_img'] as $pid) :
				$pid = (int) $pid;
		
				$ID = $_POST['wpid'];
		
				$del_com_req = "SELECT * FROM $wpdb->comments WHERE comment_pid_ID = $pid";
				$del_com_ret = $wpdb->get_results($del_com_req);
				if ($del_com_ret) {
					foreach ($del_com_ret as $del_com) {
						wp_delete_comment($del_com->comment_ID);
					}
				}
				if (get_option('aniga_delete') == 'yes') {
					$picture = $aniga->getpic($pid);
					$abspath = str_replace(get_option('aniga_dirpath'), get_option('aniga_abspath'), $picture->path);
					if (is_file($abspath.$picture->filename)) unlink($abspath.$picture->filename);
					if (is_file($abspath.'thumb_'.$picture->filename)) unlink($abspath.'thumb_'.$picture->filename);
					if (is_file($abspath.'normal_'.$picture->filename)) unlink($abspath.'normal_'.$picture->filename);
					if (substr($abspath, -10, 10) != '00-single/') @rmdir(substr($abspath, 0, -1));
				}
		
				$del_pic = mysql_query("DELETE FROM $wpdb->aniga_pic WHERE pid = $pid LIMIT 1");
		
				$i++;
			endforeach;
			//anigal_calc_gallery();
			$msg .= "<p>". sprintf(__('%s pictures deleted!', 'aniga'), $i) . "</p>";
		endif;
	}

	else { // only one picture updated.
		foreach($_POST as $varname => $value) {
			if (substr($varname, 0, 3) == 'pid'):
				$pid = substr($varname, 3);
				$filename = $_POST['filename'.$pid];
				$hits = $_POST['hits'.$pid];
				$caption = $_POST['caption'.$pid];
				$caption = htmlentities($caption, ENT_QUOTES, 'UTF-8');
				$level = $_POST['level'.$pid];
				
				$update_pic = mysql_query("UPDATE $wpdb->aniga_pic SET hits = '$hits', caption = '$caption', level = '$level' WHERE pid = $pid");
		
				$msg .= "<p>" . sprintf(__('Picture \'%s\' updated.', 'aniga'), $filename) . "</p>";
			endif;
		}
	}

	$ID = $_POST['wpid'];
	anigal_mng_pictures($msg, $ID);
}

elseif ($_GET['event'] == 'delete_alb') { // confirm delete album and pictures from db
	
	switch ( (int) $_GET['msg'] ):
	case 1:
		$msg = "<p>". __('album with all pictures deleted!', 'aniga') . "</p>";
		break;
	case 2:
		$msg = "<p>". __('huh. which album?', 'aniga') . "</p>";
		break;
	case 3:
		$msg = "<p>". __('album still has some child pages and was NOT deleted!', 'aniga') . "</p>";
	endswitch;
	
	anigal_mng_gallery($msg);
}


elseif ($_GET['event'] == 'delete_pic') { // delete picture with commens from db

	switch ( (int) $_GET['msg'] ):
	case 1:
		$msg = "<p>". __('picture deleted!', 'aniga') . "</p>";
		break;
	endswitch;
	
	anigal_mng_pictures($msg, $_GET['wpid']);
}


else {

	anigal_mng_gallery($msg);

}

?>

</div>

<?php

// print admin footer with version information
aniga_admin_footer();



// FUNCTIONS FOR MANAGE PAGE
//===========================

function anigal_mng_pictures($msg, $alb_id) {
	// print the picture management for the backend

	global $aniga_level, $aniga;
	$album = $aniga->getalbum($alb_id);
	$aniga->getchildren(get_option('aniga_base-id'), ' ', true);
	$row_color=""; ?>
	
	<h2><?php _e('Manage Pictures for Album', 'aniga'); ?> '<?php echo $album->name; ?>'</h2>
	
	<?php if ($msg!='') : ?>
		<div id="message" class="updated fade"><p><strong><?php print $msg; ?></strong></p></div>
	<?php endif; ?>
	
	<blockquote>
	<script type="text/javascript">
	<!--
	function checkAll(form)
	{
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox") {
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
			}
		}
	}
	//-->
	</script>
	
	<table cellpadding="3" cellspacing="3" border="0" width="100%" align="center">
		<tr><td colspan="8" align="right">&nbsp;</td></tr>
		<tr><td colspan="8" align="right">
		<form name="selectalb" method="post" action="?page=ANIga/admin_manage.php&event=mng_album">			
		<input name="wpid" type="hidden" value="<?php echo $alb_id; ?>" />
		<select name="albid">
			<option value="0"><?php _e('manage Pictures for Album..', 'aniga'); ?></option>
	<?php
	foreach ($aniga->child_array as $i => $val) {
		echo '<option value="' . $i . '">' . $val['sep'] . $val['name'] . '</option>';
	}
	?>			
		</select> <input type="submit" name="Submit" value="go!">
		</form>
		</td></tr>
			<form name="editimg" id="editimg"  method="post" action="?page=ANIga/admin_manage.php&event=edited_pic">			
			<input name="wpid" type="hidden" value="<?php echo $alb_id; ?>" />
	<?php
	$i = 0;
	$pictures_ret = $aniga->getpics($album->id, get_option("aniga_img_sort"), get_option("aniga_img_order"));
	if ($pictures_ret) {
	?>
		<tr><td colspan="7" align="right">&nbsp;</td></tr>
		<tr>
			<th scope="col">*</th>
			<th scope="col" colspan="2"><?php _e('Filename', 'aniga'); ?></th>
			<th scope="col"><?php _e('Picture Caption', 'aniga'); ?></th>
			<th scope="col"><?php _e('Hits', 'aniga'); ?></th>
			<th scope="col"><?php _e('Level', 'aniga'); ?></th>
			<th scope="col" colspan="2"><?php _e('Action', 'aniga'); ?></th>
		</tr>
		<?php
		foreach ($pictures_ret as $pictures) { ?>
		
		<tr>
		<input name="filename<?php echo $pictures->pid; ?>" type="hidden" value="<?php echo $pictures->filename; ?>" />
		<td width="1%" <?php echo $row_color; ?>><input name="edit_img[]" type="checkbox" value="<?php echo $pictures->pid; ?>" /></td>
		<td width="1%" align="left" <?php echo $row_color; ?>><img src="<?php echo $pictures->path . 'thumb_' . $pictures->filename; ?>" height="50px" class="gallery_border"></td>
		<td <?php echo $row_color; ?> width="1%" nowrap="nowrap"><a href="<?php echo aniga_get_permalink($alb_id, $pictures->pid, 'pid'); ?>" class="edit"><?php echo $pictures->filename; ?></a></td>
		<td <?php echo $row_color; ?> nowrap="nowrap" align="center"><input name="caption<?php echo $pictures->pid; ?>" type="text" style="width:90%;" value="<?php echo $pictures->caption; ?>"></td>
		<td width="1%" <?php echo $row_color; ?> nowrap="nowrap"><input name="hits<?php echo $pictures->pid; ?>" type="text" size="5" value="<?php echo $pictures->hits; ?>"></td>
		<td width="1%" <?php echo $row_color; ?> nowrap="nowrap">
		<select name="level<?php echo $pictures->pid; ?>">
			<?php
			foreach ($aniga_level as $k => $v) {
				echo '<option value="' . $k . '"';
				if ($pictures->level == $k) { echo ' selected="selected"'; }
				echo '>' . $v . '</option>';
			} ?>	
		</select></td>
		<td width="1%" <?php echo $row_color; ?> nowrap="nowrap"><input type="submit" name="pid<?php echo $pictures->pid; ?>" value="<?php _e('update', 'aniga'); ?>"></td>
		<td width="1%" <?php echo $row_color; ?> nowrap="nowrap">
			<a href="<?php echo wp_nonce_url("../wp-content/plugins/ANIga/admin-delete.php?event=delete_pic&amp;pid=".$pictures->pid."&amp;wpid=".$alb_id, 'delete-pic_' . $pictures->pid); ?>" onclick="return confirm('<?php _e('You are about to delete this Picture with ALL comments.', 'aniga'); ?> \n<?php _e("\\'Cancel\\' to stop, \\'OK\\' to delete.", 'aniga')?>')" class="delete" ><?php _e('delete', 'aniga'); ?></a>
		</td>
		</tr>
			<?php
			if ($row_color=="class='alternate'"){
				$row_color="";
			}
			else {
				$row_color="class='alternate'";
			}
			$i++;
			if ($i == 20): ?>
		<tr>
			<th scope="col">*</th>
			<th scope="col" colspan="2"><?php _e('Filename', 'aniga'); ?></th>
			<th scope="col"><?php _e('Picture Caption', 'aniga'); ?></th>
			<th scope="col"><?php _e('Hits', 'aniga'); ?></th>
			<th scope="col"><?php _e('Level', 'aniga'); ?></th>
			<th scope="col" colspan="2"><?php _e('Action', 'aniga'); ?></th>
		</tr>	
			<?php
				$i = 0;
			endif;
			} ?>
		<tr><td colspan="8">&nbsp;</td></tr>
		<tr><td colspan="8">
		<fieldset class="gallery_fieldset">
			<legend><strong><?php _e('Edit multiple pictures', 'aniga'); ?></strong></legend>
			<table width="100%" cellpadding="0" cellspacing="0"><tr>
			<td width="70%"><a href="javascript:;" onclick="checkAll(document.getElementById('editimg')); return false; "><?php _e('Invert Checkbox Selection', 'aniga'); ?></a></td>
			<td align="right">
			<input type="submit" name="multiple_delete" value="<?php _e('delete checked pictures', 'aniga'); ?>" onclick="return confirm('<?php _e('You are about to delete these pictures.', 'aniga'); ?> \n <?php if (get_option('aniga_delete') == 'yes') {
			_e('The files (original, normal & thumb) will be deleted from your server.', 'aniga');
		}else{
			_e('The files (original, normal & thumb) will stay on your server.', 'aniga');}""; ?>\n<?php _e("\\'Cancel\\' to stop, \\'OK\\' to delete.", 'aniga')?>')"></td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr><td>
			<fieldset class="gallery_fieldset">
				<legend><?php _e('Update checked pictures and move them to Album', 'aniga'); ?></legend>
				<select name="albid">
		<?php	
		foreach ($aniga->child_array as $i => $val) {
			echo '<option value="' . $i . '"';
			if ($i == $alb_id) echo ' selected="selected"';
			echo '>' . $val['sep'] . $val['name'] . '</option>';
		}	?>	
				</select> 
				<input type="submit" name="multiple_edit" value="<?php _e('update', 'aniga'); ?>">
			</fieldset>
			</td><td>
			<fieldset class="gallery_fieldset">
				<legend><?php _e('Update Level for checked Pictures', 'aniga'); ?></legend>
				<?php _e('set to', 'aniga'); ?> <select name="mlevel">
		<?php
		foreach ($aniga_level as $k => $v) {
			echo '<option value="' . $k . '"';
			echo '>' . $v . '</option>';
		} ?>	
				</select> <input type="submit" name="multiple_level" value="<?php _e('update', 'aniga'); ?>">
			</fieldset>
			</td></tr></table>
		</fieldset>
		</td></tr>
		<?php
		}
		else { ?>
		<tr><td colspan="7"><?php _e('no Pictures in this Album.', 'aniga'); ?></td></tr>
		<?php 
		} ?>
		</form>
	</table>

	</blockquote>
<?php
}


function anigal_mng_gallery($msg) {
	// print the gallery managment for the backend

	global $aniga; ?>
	
	<h2><?php _e('Manage Albums and Pictures', 'aniga'); ?></h2>
	
	<?php if ($msg!='') : ?>
		<div id="message" class="updated fade"><p><strong><?php print $msg; ?></strong></p></div>
	<?php endif; ?>
	
	<blockquote>
	<table cellpadding="3" cellspacing="3" border="0">
		<tr>
			<th>&nbsp;</th>
			<th scope="col"><?php _e('Album', 'aniga'); ?></th>
			<th scope="col"><?php _e('Order', 'aniga'); ?></th>
			<th scope="col"><?php _e('Pictures', 'aniga'); ?></th>
			<th scope="col"><?php _e('Level', 'aniga'); ?></th>
			<th scope="col" colspan="3"><?php _e('Action', 'aniga'); ?></th>
		</tr>
	<?php
	$aniga->getchildren(get_option('aniga_base-id'), ' ', true);
	$row_color="";
	foreach ($aniga->child_array as $i => $val):
	
		 $count = $aniga->c_alb_pic($i); 
		 if ($val['has_child']) $sum_count =  $count + $aniga->getchildren_c_pic($i); ?>
			
		<tr>
		<td width="1%"><img src="<?php echo get_option('aniga_dirpath'); ?>/00-gfx/album_<?php echo $i; ?>.jpg" height="50px" class="gallery_border"/></td>
		<td <?php echo $row_color; ?>><div style="float:left; padding-right:4px"><?php echo $val['sep']; ?></div><div style="float:left"><a href="?page=ANIga/admin_manage.php&event=album&wpid=<?php echo $i; ?>"><?php echo $val['name']; ?></a><br /><?php echo $val['desc']; ?></div></td>
		<td width="1%" <?php echo $row_color; ?> nowrap="nowrap"><a href="?page=ANIga/admin_manage.php&event=edit_alb&wpid=<?php echo $i; ?>" class="edit"><?php echo $val['order']; ?></a></td>
		<td width="1%" <?php echo $row_color; ?> nowrap="nowrap"><a href="?page=ANIga/admin_manage.php&event=album&wpid=<?php echo $i; ?>" class="edit"><?php echo $count; ?> <?php if ($val['has_child']) echo "(".$sum_count.")"; ?></a></td>
		<td width="1%" <?php echo $row_color; ?> nowrap="nowrap"><a href="?page=ANIga/admin_manage.php&event=edit_alb&wpid=<?php echo $i; ?>" class="edit"><?php echo $val['lvl']; ?></a></td>
		<td width="1%" <?php echo $row_color; ?> nowrap="nowrap"><a href="<?php echo get_permalink($i); ?>" class="edit"><?php _e('view', 'aniga'); ?></a></td>
		<td width="1%" <?php echo $row_color; ?> nowrap="nowrap"><a href="?page=ANIga/admin_manage.php&event=edit_alb&wpid=<?php echo $i; ?>" class="edit"><?php _e('edit', 'aniga'); ?></a></td>
		<td width="1%" <?php echo $row_color; ?> nowrap="nowrap">
        <?php if ($val['has_child'] != true) { ?>
		<a href="<?php echo wp_nonce_url("../wp-content/plugins/ANIga/admin-delete.php?event=delete_alb&amp;wpid=".$i, 'delete-album_' . $i); ?>" onclick="return confirm('<?php _e('You are about to delete this Album with ALL pictures', 'aniga'); ?> \n <?php _e("\\'Cancel\\' to stop, \\'OK\\' to delete.", 'aniga')?>')" class="delete" ><?php _e('delete', 'aniga'); ?></a>
        <?php } else { _e('delete', 'aniga'); } ?>
		</td></tr>
		<?php		
			if ($row_color=="class='alternate'"){
				$row_color="";
			}else{
				$row_color="class='alternate'";
			}
	endforeach;
?>
		<tr><td colspan="8" align="right">&nbsp;</td></tr>
		<tr><td colspan="8" align="right">
		<form name="selectalb" method="post" action="?page=ANIga/admin_manage.php&event=mng_album">			
		<input name="wpid" type="hidden" value="<?php echo $alb_id; ?>" />
		<select name="albid">
			<option value="0"><?php _e('manage Pictures for Album..', 'aniga'); ?></option>
	<?php
	foreach ($aniga->child_array as $i => $val) {
		echo '<option value="' . $i . '">' . $val['sep'] . $val['name'] . '</option>';
	}
	?>			
		</select> <input type="submit" name="Submit" value="go!">
		</form>
		</td></tr>
	</table>
	
	</blockquote>
	
<?php
}
?>
