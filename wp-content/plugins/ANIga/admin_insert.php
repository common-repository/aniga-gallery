<?php
// Display Page "Insert Picture Directory"
//########################################################################

require_once("functions.php");
$aniga = new aniga_db();
$aniga_file = new aniga_file();
$aniga->user = $aniga->user();

$msg = "";
$msg .= $aniga->checkpath();

	$msg .= stripslashes($_POST['msg']);
	$path = $_POST['path'];
	$album = $_POST['albid'];
	$level = $_POST['level'];
	$zip_upload = $_POST['zip_upload'];
	$mode = $_POST['mode'];
	$noresize = $_POST['noresize'];

if ($_POST['event'] == 'upload') {

	if ($mode == 'bulk') {

		$filepath = '00-single/';
		$db_path = get_option('aniga_dirpath').$filepath;
		$dir_path = get_option('aniga_abspath').$filepath;
		
		$bulkfile = array();
		
		for ($i = 1; $i <= 5; $i++) {
			if ($_FILES['pic'.$i]['name']) {
			
				$filename = $_FILES['pic'.$i]['name'];
				if(!ereg("thumb_",$filename) && !ereg("normal_",$filename) && okfiletype($filename, $aniga->allowed_pic)){
					
					$msg .= $aniga_file->move_file($_FILES['pic'.$i]['tmp_name'], $dir_path, $filename, true);
			
					$imagesize = getimagesize($dir_path.$aniga_file->fname);
					
					$bulkfile[$i] = array('name' => $aniga_file->fname, 'x' => $imagesize[0], 'y' => $imagesize[1]);
						
				}
				else $msg .= sprintf(__('!!%1$s%2$s is not allowed!!', 'aniga'), $filepath, $filename) . "<br>\n";		
			}
			
		}
	
	?>
<div class="wrap">

	<h2><?php _e('Uploading Images', 'aniga'); ?></h2>

	<div id="message" class="updated fade">
	<?php echo $msg; ?>
	
		<form name="album" method="post" action="">
			<input name="albid" type="hidden" value="<?php echo $album; ?>">
			<input name="level" type="hidden" value="<?php echo $level; ?>">		
			<input name="event" type="hidden" value="dir">
			<input name="mode" type="hidden" value="bulk">
			<input name="path" type="hidden" value="00-single">
			<?php	for ($i = 1; $i <= 5; $i++) {
					   echo '<input name="pic'.$i.'" type="hidden" value="'.$bulkfile[$i]['name'].'">';
					   echo '<input name="picx'.$i.'" type="hidden" value="'.$bulkfile[$i]['x'].'">';
					   echo '<input name="picy'.$i.'" type="hidden" value="'.$bulkfile[$i]['y'].'">';
					} ?>
			<input type="submit" name="Submit" value="<?php _e('Uploading complete. Press the button to proceed with inserting and resizing.', 'aniga'); ?>">
		</form>

</div>
	
	<?php
	}
	
}

elseif ($zip_upload == 'yes') {

	$zipok = false;
	if (!okfiletype($_FILES['org']['name'], $aniga->allowed_zip) && get_option('aniga_zip_mode') == 'zip') $msg .= "<p>" . sprintf(__('\'%s\' is not a zip file!', 'aniga'), basename($_FILES['org']['name'])) . "</p>";
	else {
		$dir_path = get_option('aniga_abspath').'album'.$album;
		for ($i = 1; ; $i++) {
			if (!is_dir($dir_path.'_'.$i) or $i > 250) {
				break;
			 }
		}
		
		$dir_path = $dir_path.'_'.$i;
		$path = 'album'.$album.'_'.$i;
		$db_path = get_option('aniga_dirpath').'album'.$album.'_'.$i.'/';
		mkdir($dir_path, 0777);
		chmod($dir_path, 0777);
		
		$msg .= aniga_zip_upload($dir_path, $_FILES['org']['name'], $_FILES['org']['tmp_name'], $db_path);
		$zipok = true;
	}
	
	?>
<div class="wrap">

	<h2><?php _e('Uploading Images from zip file', 'aniga'); ?></h2>

	<div id="message" class="updated fade">
	<?php
	echo $msg;
	if ($zipok) { ?>
		<form name="album" method="post" action="">
			<input name="albid" type="hidden" value="<?php echo $album; ?>">
			<input name="level" type="hidden" value="<?php echo $level; ?>">
			<input name="noresize" type="hidden" value="<?php echo $noresize; ?>">	
			<input name="event" type="hidden" value="dir">
			<input name="path" type="hidden" value="<?php echo $path; ?>">
			<input type="submit" name="Submit" value="<?php _e('Uploading complete. Press the button to proceed with inserting and resizing.', 'aniga'); ?>">
		</form>
	<?php } ?>

</div>
	
	<?php
	
}

elseif ($_POST['event'] == 'dir') { // start insert pictures

	$db_path = get_option('aniga_dirpath').$path . '/';
	$dir_path = get_option('aniga_abspath').$path;

?>

<script type="text/javascript" src="../wp-content/plugins/ANIga/mootools.v1.11.js"></script>

<script type="text/javascript">

var images = new Array()
<?php
if ($mode == 'bulk') {

	for ($i = 1; $i <= 5; $i++) {
		if ($_POST['pic'.$i] != '') {
			$date = date("Y-m-d H:i:s");
	
			$insert_pic = '<span style="color:red">'.__('failed', 'aniga').'</span>';
			if ($wpdb->query("INSERT INTO $wpdb->aniga_pic (`parent_id`, `path`, `filename`, `hits`, `width`, `height`, `pic_date`, `level`) VALUES ('$album', '$db_path', '".$_POST['pic'.$i]."', '0', '".$_POST['picx'.$i]."', '".$_POST['picy'.$i]."', '$date', '$level')")) $insert_pic = '<span style="color:green">'.__('successful', 'aniga').'</span>';
				
			$files[$i] = array('name' => $_POST['pic'.$i], 'xsize' => $_POST['picx'.$i], 'ysize' => $_POST['picy'.$i], 'db' => $insert_pic);
			echo 'images['.$i.'] = "'.$_POST['pic'.$i].'"';
			echo "\n";
		}
	}

}
else {
	$dir = @opendir("$dir_path");
	$i = 0;
	$files = array();
	while($filename = readdir($dir)) {
	
		if(!ereg("thumb_",$filename) && !ereg("normal_",$filename) && okfiletype($filename, $aniga->allowed_pic)){
		
			$imagesize = getimagesize($dir_path.'/'.$filename);
			
			$date = date("Y-m-d H:i:s");
			
			$insert_pic = '<span style="color:red">'.__('failed', 'aniga').'</span>';
			if ($wpdb->query("INSERT INTO $wpdb->aniga_pic (`parent_id`, `path`, `filename`, `hits`, `width`, `height`, `pic_date`, `level`) VALUES ('$album', '$db_path', '$filename', '0', '$imagesize[0]', '$imagesize[1]', '$date', '$level')")) $insert_pic = '<span style="color:green">'.__('successful').'</span>';
			
			$files[$i] = array('name' => $filename, 'xsize' => $imagesize[0], 'ysize' => $imagesize[1], 'db' => $insert_pic);
			echo 'images['.$i.'] = "'.$filename.'"';
			echo "\n";
			$i++;
				
		}
	}
	closedir($dir);
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

<?php if ($noresize != 'yes' && get_option('aniga_resize') == 'yes') echo "addLoadEventFunc(goResize());"; 

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
		new Ajax('../wp-content/plugins/ANIga/resize.inc.php',{postBody:'file=' + images[i] + '&path=<?php echo $dir_path.'/&crop='.$crop.'&csize='.$c_size.'&rsize='.$t_size.'&mode=thumb&qual='.$r_qual.'&refl='.$refl.'&border='.$border; ?>', update:'cont_thumb_' + i}).request();
		new Ajax('../wp-content/plugins/ANIga/resize.inc.php',{postBody:'file=' + images[i] + '&path=<?php echo $dir_path. '/&crop=no&csize='.$c_size.'&rsize='.$n_size.'&mode=normal&qual='.$r_qual; ?>', update:'cont_norm_' + i}).request();
	}
};

</script>

<div class="wrap">

	<h2><?php _e('Inserting Pictures... please wait', 'aniga'); ?></h2>

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
	<td align="center"><div id="cont_db_<?php echo $i; ?>"><?php _e('insert', 'aniga'); ?> <?php echo $val['db']; ?></div></td>
	<td align="center"><div id="cont_thumb_<?php echo $i; ?>"><?php if ($noresize != 'yes' && get_option('aniga_resize') == 'yes') _e('waiting for resize ...', 'aniga'); else _e('resize disabled', 'aniga'); ?></div></td>
	<td align="center"><div id="cont_norm_<?php echo $i; ?>"><?php if ($noresize != 'yes' && get_option('aniga_resize') == 'yes') _e('waiting for resize ...', 'aniga'); else _e('resize disabled', 'aniga'); ?></div></td>
</tr>
<?php
}
?>
</table>
</div>

</div>
<?php
}
else {

?>
<div class="wrap">

	<h2><?php _e('Insert new Pictures into an Album', 'aniga'); ?></h2>
	
<?php
if ($msg!='') : ?>

	<div id="message" class="updated fade"><p><strong><?php print $msg; ?></strong></p></div>
	
<?php endif; ?>	

<blockquote>

	<fieldset class="gallery_fieldset">
		<legend><strong><?php _e('Mass Upload', 'aniga'); ?></strong></legend>

		<form name="album" method="post" action="<?php if (get_option('aniga_zip_mode') == 'mzipx') echo "../wp-content/plugins/ANIga/zip.phpx"; elseif (get_option('aniga_zip_mode') == 'mzip') echo "../wp-content/plugins/ANIga/zip.php"; ?>" enctype="multipart/form-data" />
		<input name="event" type="hidden" value="dir">
		<input name="abspath" type="hidden" value="<?php echo get_option('aniga_abspath'); ?>">
		<input name="dirpath" type="hidden" value="<?php echo get_option('aniga_dirpath'); ?>">
		<input name="blog_url" type="hidden" value="<?php echo get_option('siteurl'); ?>">
	
		<blockquote>
		
		<fieldset class="gallery_fieldset">
			<legend><?php _e('Select Album', 'aniga'); ?></legend>
			<select name="albid" style="width:95%">
<?php
$aniga->getchildren(get_option('aniga_base-id'),' '); 
foreach ($aniga->child_array as $i => $val) {
	echo '<option value="' . $i . '">' . $val['sep'] . $val['name'] . '</option>';
}		
?>			
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
	
		<fieldset class="gallery_fieldset">
			<legend><?php _e('Choose Upload Method', 'aniga'); ?></legend>
			<input name="dummy" type="checkbox" value="yes" /> <?php _e('Pictures already uploaded with FTP', 'aniga'); ?>
			<blockquote>
				<fieldset class="gallery_fieldset">
					<legend><?php _e('Url to directory (with write access)', 'aniga'); ?></legend>
					<?php echo get_option('aniga_dirpath'); ?><input name="path" type="text" size="30">/
				</fieldset>
			</blockquote>
			<input name="zip_upload" type="checkbox" value="yes" <?php if (get_option("anigal_zip_mode") == 'none'): ?>DISABLED<?php endif; ?> /> <?php _e('Upload Pictures in zip file', 'aniga'); ?>
			<blockquote>
				<fieldset class="gallery_fieldset">
					<legend><?php _e('Zip file (only images, no folders in archive, no blanks in filename!)', 'aniga'); ?></legend>
					<input type="file" name="org" <?php if (get_option("anigal_zip_mode") == 'none'): ?>DISABLED<?php endif; ?> />
				</fieldset>
			</blockquote>
		</fieldset>
		<p>
		<input name="noresize" type="checkbox" value="yes" onclick="return confirm('You have to provide also the \'normal_\' and \'thumb_\' Pictures for this Upload!  \n \'Cancel\' to stop, \'OK\' to proceed.')" <?php if (get_option("anigal_resize") == 'no'): ?>DISABLED<?php endif; ?> /> <?php _e('disable automatic Picture resizing for this Upload', 'aniga'); ?>
		</p>
		
		</blockquote>

		<p>
		<input type="submit" name="Submit" value="<?php _e('Insert Pictures', 'aniga'); ?>">
		</p>
		</form>
		
	</fieldset>
	
<script language="JavaScript" type="text/javascript">
<!--
function checkform ( form )
{
  if (form.pic1.value == "") {
    alert( "Please submit at least one file." );
    form.pic1.focus();
    return false ;
  }
  return true ;
}
//-->
</script>

	<fieldset class="gallery_fieldset">
		<legend><strong><?php _e('Bulk Upload', 'aniga'); ?></strong></legend>
		<?php _e('Bulk upload for up to 5 Pictures', 'aniga'); ?>

		<form name="album_bulk" method="post" action="" enctype="multipart/form-data" onsubmit="return checkform(this);" />
		<input name="event" type="hidden" value="upload">
		<input name="mode" type="hidden" value="bulk">
		
		<blockquote>

			<fieldset class="gallery_fieldset">
				<legend><?php _e('Select Album', 'aniga'); ?></legend>
				<select name="albid" style="width:95%" <?php if (get_option('aniga_resize') == 'no'): ?>DISABLED<?php endif; ?> />
<?php

$aniga->getchildren(get_option('aniga_base-id'),' '); 
foreach ($aniga->child_array as $i => $val) {
	echo '<option value="' . $i . '">' . $val['sep'] . $val['name'] . '</option>';
}		
?>			
				</select>
			</fieldset>
			
			<fieldset class="gallery_fieldset">
				<legend><?php _e('Level', 'aniga'); ?></legend>
				<select name="level" <?php if (get_option('aniga_resize') == 'no'): ?>DISABLED<?php endif; ?> />
	<?php
	foreach ($aniga_level as $k => $v) {
		echo '<option value="' . $k . '"';
		echo '>' . $v . '</option>';
	}
?>
				</select> <?php _e('is allowed to view the Pictures', 'aniga'); ?>
			</fieldset>

			<fieldset class="gallery_fieldset">
				<legend><?php _e('Pictures Files', 'aniga'); ?></legend>
				1 <input type="file" name="pic1" <?php if (get_option('aniga_resize') == 'no'): ?>DISABLED<?php endif; ?> /><br />
				2 <input type="file" name="pic2" <?php if (get_option('aniga_resize') == 'no'): ?>DISABLED<?php endif; ?> /><br />
				3 <input type="file" name="pic3" <?php if (get_option('aniga_resize') == 'no'): ?>DISABLED<?php endif; ?> /><br />
				4 <input type="file" name="pic4" <?php if (get_option('aniga_resize') == 'no'): ?>DISABLED<?php endif; ?> /><br />
				5 <input type="file" name="pic5" <?php if (get_option('aniga_resize') == 'no'): ?>DISABLED<?php endif; ?> />
			</fieldset>
			
		</blockquote>
		
		<p>
		<input type="submit" name="Submit" value="<?php _e('Upload Pictures', 'aniga'); ?>" <?php if (get_option('aniga_resize') == 'no'): ?>DISABLED<?php endif; ?> />
		</p>
		</form>
		
	</fieldset>
	
	<fieldset class="gallery_fieldset">
		<legend><?php _e('Information', 'aniga'); ?></legend>
		<p><?php _e('Supported Image types:  <strong>jpg, gif, png</strong>', 'aniga'); ?></p>
		
		<?php if (get_option('aniga_resize') == 'no') { ?>
	
			<p><?php _e('<strong>Automatic Picture resizing is disabled, therefore you cannot use the Bulk Upload </strong><br />You need to provide 3 files for each Picture:<br /><strong>filename.jpg</strong> - the original Picture (size does not matter)<br /><strong>normal_filename.jpg</strong> - this is shown in the post and links to the original Picture(e.g. max. 400px)<br /><strong>thumb_filename.jpg</strong> - the thumbnail of the original Picture(e.g. max. 150px)', 'aniga'); ?>	</p>
		
		<?php } ?>
	
	</fieldset>

</blockquote>

</div>

<?php

}

aniga_admin_footer();
?>