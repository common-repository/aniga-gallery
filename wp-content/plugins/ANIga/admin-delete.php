<?php
require_once('../../../wp-config.php');
require_once('../../../wp-admin/admin.php');

require_once("functions.php");
$aniga = new aniga_db();
$aniga->user = $aniga->user();


if ($_GET['event'] == 'delete_alb') { // delete album and pictures from db
	
	$ID = (int) $_GET['wpid'];
	check_admin_referer('delete-album_' . $ID);
	

	if ($ID) {

		$count = $aniga->getpostparent($ID);	
		if ($count > 0) {
			$msg = 3;
		}
	else {
				
		if (get_option('aniga_delete') == 'yes') {
			
			$pics = $aniga->getpics($ID, get_option("aniga_img_sort"), get_option("aniga_img_order"));
			
			if ($pics) {
			
				foreach ($pics as $picture) {
					$delete_pics = mysql_query("DELETE FROM $wpdb->aniga_pic WHERE pid = ".$picture->pid." LIMIT 1");
					$abspath = str_replace(get_option('aniga_dirpath'), get_option('aniga_abspath'), $picture->path);
					if (is_file($abspath.$picture->filename)) unlink($abspath.$picture->filename);
					if (is_file($abspath.'thumb_'.$picture->filename)) unlink($abspath.'thumb_'.$picture->filename);
					if (is_file($abspath.'normal_'.$picture->filename)) unlink($abspath.'normal_'.$picture->filename);
					if (substr($abspath, -10, 10) != '00-single/') @rmdir(substr($abspath, 0, -1));
				}
			}
			unlink(get_option('aniga_abspath').'00-gfx/album_'.$ID.'.jpg');
		}
				
		$delete_alb = mysql_query("DELETE FROM $wpdb->aniga_alb WHERE id = $ID LIMIT 1");
		$delete_rel = mysql_query("DELETE FROM $wpdb->aniga_rel WHERE id = $ID LIMIT 1");
		
		wp_delete_post($ID);
		
		//anigal_calc_gallery();
		
		$msg = 1;
	}
	}
	else $msg = 2;

	wp_redirect(get_settings('siteurl') .'/wp-admin/admin.php?page=ANIga/admin_manage.php&event=delete_alb&msg='.$msg);
}

elseif ($_GET['event'] == 'delete_pic') { // delete picture with commens from db

	$pid = (int) $_GET['pid'];
	$ID = (int) $_GET['wpid'];
	check_admin_referer('delete-pic_' . $pid);
	
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
	
	//anigal_calc_gallery();
	
	$msg = 1;
	
	wp_redirect(get_settings('siteurl') .'/wp-admin/admin.php?page=ANIga/admin_manage.php&event=delete_pic&wpid='.$ID.'&msg='.$msg);
}
?>
