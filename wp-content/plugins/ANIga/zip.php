<?php
require_once("functions.php");
require_once("m-zip.php");

$aniga = new aniga_db();

echo '<p>processing your files...</p>';

$msg = "";

$path = $_POST['path'];
$album = $_POST['albid'];
$zip_upload = $_POST['zip_upload'];
$abspath = $_POST['abspath'];
$dirpath = $_POST['dirpath'];
$blog_url = $_POST['blog_url'];
$level = $_POST['level'];
$noresize = $_POST['noresize'];

if ($album == 0) $msg .= "<p>" . __("Select an Album!", 'aniga') . "</p>";
elseif ($path == '' && $zip_upload == '' && $bulk == '') $msg .= "<p>Choose a Directory, submit a zip file or Pictures!</p>";
elseif ($zip_upload == 'yes' && !okfiletype($_FILES['org']['name'], $aniga->allowed_zip) && get_option('anigal_zip_mode') == 'zip') $msg .= "<p>" . sprintf('\'%s\' is not a zip file!', basename($_FILES['org']['name'])) . "</p>";
elseif ($zip_upload == 'yes' && (ini_get('safe_mode') || ini_get('safe_mode') == 'on')) $msg .= "<p>your server has PHP safe mode restrictions in effect, choose manual ftp upload</p>";
else {

	if ($zip_upload == 'yes') {
	
		$dir_path = $abspath.'album'.$album;
			for ($i = 1; ; $i++) {
			   if (!is_dir($dir_path.'_'.$i) or $i > 99) {
				   break;
			   }
			}
		$dir_path = $dir_path.'_'.$i;
		$db_path = $dirpath.'album'.$album.'_'.$i.'/';
		$path = 'album'.$album.'_'.$i;
		mkdir($dir_path, 0777);
		chmod($dir_path, 0777);
		$msgzip = anigal_m_zip_upload($dir_path, $_FILES['org']['name'], $_FILES['org']['tmp_name'], $db_path);
		$msg .= $msgzip;
	}
}
?>
<p>..done</p>
<p>
	<form name="album" method="post" action="<?php echo $blog_url; ?>/wp-admin/admin.php?page=ANIga/admin_insert.php">
		<input name="msg" type="hidden" value="<?php echo $msg; ?>">
		<input name="path" type="hidden" value="<?php echo $path; ?>">
		<input name="albid" type="hidden" value="<?php echo $album; ?>">
		<input name="level" type="hidden" value="<?php echo $level; ?>">		
		<input name="zip_upload" type="hidden" value="no">
		<input name="event" type="hidden" value="dir">		
		<input name="noresize" type="hidden" value="<?php echo $noresize; ?>">			
		<input type="submit" name="Submit" value="click to complete the picture upload">
		</form>
</p>		
<?php

function anigal_m_zip_upload($dir, $file_org, $file_tmp, $db_path) {

	
	$file = $dir."/".$file_org;
	$msgf = "";
	if(move_uploaded_file($file_tmp, $file)) {
	
		$zip = m_zip_open($file);
		$i = 0;
		if ($zip) {
			while ($zip_entry = m_zip_read($zip)) {
				if (m_zip_entry_open($zip, $zip_entry, "r")) {
					$buf = m_zip_entry_read($zip_entry, m_zip_entry_filesize($zip_entry));
					$fp = fopen($dir."/".m_zip_entry_name($zip_entry),"w");
					fwrite($fp,$buf);
					//$msgf .= m_zip_entry_name($zip_entry)."<br>";
					m_zip_entry_close($zip_entry);
					$i++;
				}
			}
			m_zip_close($zip);
			$msgf .= "<p>" . sprintf('The file \'%s\' has been un-zipped to:', basename($file_org)) . " '$db_path'<p />";
		}
		else $msgf .= "<p>could not read zip file</p>";
		unlink($file);
	}
	else $msgf .= "<p>could not move zip file to '$db_path'</p>";
	return $msgf;
}

?>
