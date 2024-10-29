<?php
/*
Plugin Name: ANIga gallery Updater
Plugin URI: http://animalbeach.net/aniga
Description: Update ANIga 0.31fix to 0.50
Version: 0.50
Author: Michael Naab
Author URI: http://animalbeach.net
*/



/*
    You cant update any Versions prior to 0.31fix!! - use at own risk! - make a backup first!
	
    How does this work?
    1. make a backup of your database (all ANIga and Wordpress tables), your gallery folder, your ANIga theme files and your ANIga plugin files. Do not proceed, if you dont know how to do this!
    2. make sure your backup is complete and working! This script will make changes. You cannot go back afterwards.
    3. deactivate ANIga plugin
    4. activate ANIga Updater
    5. click the update button on the ANIga Upddater tab in the admin menu
    6. deactivate the ANIga Updater plugin
    7. delete the ANIga folder (wp-content/plugins/ANIga), the file aniga_gallery.php and your ANIga theme files
    8. install and activate ANIga version 0.50 according to installation notes
    9. You can regenerate your thumbnails (if you want the new shiny ones) on the edit Album page. You also might want to overwrite your album images there..
	10. sidebar functions were renamed - see the sidebar-example.php for the new calling methods.
*/


// table names
$wpdb->aniga_alb			= $wpdb->prefix . 'aniga_albums';
$wpdb->aniga_pic			= $wpdb->prefix . 'aniga_picture';
$wpdb->aniga_rel			= $wpdb->prefix . 'aniga_relations';

$wpdb->aniga_alb_old		= $wpdb->prefix . 'anigal_album';
$wpdb->aniga_pic_old		= $wpdb->prefix . 'anigal_picture';
$wpdb->aniga_cat_old		= $wpdb->prefix . 'anigal_categories';

// adds pages
function aniga_add_pages() {
	
// Add a new top-level menu:
    add_menu_page('Update ANIga', 'ANIga Updater', 10, __FILE__, 'aniga_page');
}
add_action('admin_menu', 'aniga_add_pages');


function aniga_page() { ?>

	<div class="wrap">
    <h2>Update ANIga from 0.31fix to 0.50</h2>
    
    <?php 
	aniga_howto();
	if ($_POST['do'] == 'upgrade') {
		$id = get_option('anigal_base-id');
		$newid = get_option('aniga_base-id');
		if ($newid != '') {
			echo "<br /><br /><hr><br />it seems you already updated ANIga.. nothing to do here!";
		}
		elseif ($id != '') {
			aniga_upgrade($id);
		}
		else {
			echo "<br /><br /><hr><br />it seems you dont have installed ANIga.. nothing to do here!";
		}
	}
	else {
		aniga_button();
	}
	?>
    
    </div>
<?php
}

function aniga_howto() { ?>

    <p>You cant update any Versions prior to 0.31fix!! - use at own risk! - make a backup first!</p>
    How does this work?<br />
    1. make a <strong>backup</strong> of your <strong>database</strong> (all ANIga and Wordpress tables), your <strong>gallery folder</strong>, your <strong>ANIga theme files</strong> and your <strong>ANIga plugin files</strong>.Do not proceed, if you dont know how to do this!<br />
    2. make sure your backup is complete and working! This script will make changes. You cannot go back afterwards.<br />
    3. deactivate ANIga plugin<br />
    4. activate ANIga Updater<br />
    5. click the button below<br />
    6. deactivate the ANIga Updater plugin<br />
    7. delete the ANIga folder (wp-content/plugins/ANIga), the file aniga_gallery.php and your ANIga theme files<br />
    8. install and activate ANIga version 0.50 according to installation notes<br />
    9. You can regenerate your thumbnails (if you want the new shiny ones) on the edit Album page. You also might want to overwrite your album images there..<br />
    10. sidebar functions were renamed - see the sidebar-example.php for the new calling methods.
<?php
}

function aniga_button() { ?>

    <br />
	<br />
	<form action="" method="post" enctype="multipart/form-data" name="updateform">
    
    <input name="do" type="hidden" value="upgrade" />
    
    <input name="submit" type="submit" value="Update ANIga" />
    
    </form>
<?php
}

function aniga_upgrade($id) {

	$baseid = $id;
	$abspath = get_option('anigal_abspath');
	
	global $wpdb;
	
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	
	echo "<br /><hr><br />";
	
	$table_alb = $wpdb->aniga_alb;
	if($wpdb->get_var("show tables like '$table_alb'") != $table_alb) {
	  
	  $sql = "CREATE TABLE `".$table_alb."` (
			`id` BIGINT( 20 ) NOT NULL DEFAULT '0',
			`name` TEXT NOT NULL ,
			`desc` TEXT NOT NULL ,
			`level` VARCHAR( 2 ) NOT NULL DEFAULT '0',
			`order` MEDIUMINT NOT NULL DEFAULT '0',
			`count` MEDIUMINT NOT NULL ,
			PRIMARY KEY  (`id`)
		);";
	
	  dbDelta($sql);
	} 
	$table_rel = $wpdb->aniga_rel;
	if($wpdb->get_var("show tables like '$table_rel'") != $table_rel) {
	  
	  $sql = "CREATE TABLE `".$table_rel."` (
			`id` BIGINT( 20 ) NOT NULL ,
			`parent_id` BIGINT( 20 ) NOT NULL
		);";
	
	  dbDelta($sql);
	}
	$table_pic = $wpdb->aniga_pic;
	if($wpdb->get_var("show tables like '$table_pic'") != $table_pic) {
	  
	  $sql = "CREATE TABLE `".$table_pic."` (
				  `pid` BIGINT( 20 ) NOT NULL auto_increment,
				  `parent_id` BIGINT( 20 ) NOT NULL default '0',
				  `path` VARCHAR( 200 ) NOT NULL default '',
				  `filename` VARCHAR( 200 ) NOT NULL default '',
				  `hits` BIGINT( 20 ) NOT NULL default '0',
				  `width` SMALLINT( 6 ) NOT NULL default '0',
				  `height` SMALLINT( 6 ) NOT NULL default '0',
				  `pic_date` DATETIME NOT NULL,
				  `caption` TEXT NOT NULL,
				  `level` VARCHAR( 2 ) NOT NULL default '0',
			  PRIMARY KEY  (`pid`)
		);";
	
	  dbDelta($sql);
	}
	
	echo "create new table $table_alb<br /> create new table $table_rel<br />create new table $table_pic<br />";
		
	echo "remove obsolete Wordpress options<br />"; 
	
	delete_option('anigal_lang');
	
	echo "update existing Wordpress options<br />";
	
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_abspath' WHERE option_name = 'anigal_abspath'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_base-id' WHERE option_name = 'anigal_base-id'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_caimg_size' WHERE option_name = 'anigal_caimg_size'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_colums' WHERE option_name = 'anigal_colums'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_css' WHERE option_name = 'anigal_css'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_delete' WHERE option_name = 'anigal_delete'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_dirpath' WHERE option_name = 'anigal_dirpath'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_img_mode' WHERE option_name = 'anigal_img_mode'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_norm_size' WHERE option_name = 'anigal_norm_size'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_resize' WHERE option_name = 'anigal_resize'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_resize_qual' WHERE option_name = 'anigal_resize_qual'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_slds_filter' WHERE option_name = 'anigal_slds_filter'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_slds_prefetch' WHERE option_name = 'anigal_slds_prefetch'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_slds_time' WHERE option_name = 'anigal_slds_time'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_thumb_size' WHERE option_name = 'anigal_thumb_size'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_trackback_sent' WHERE option_name = 'anigal_trackback_sent'");
	$update = $wpdb->query("UPDATE $wpdb->options SET option_name = 'aniga_zip_mode' WHERE option_name = 'anigal_zip_mode'");
	
	echo "add new Wordpress options<br />";
	
	add_option('aniga_img_order', 'ASC', 'Picture order mode');
	add_option('aniga_img_sort', 'filename', 'Picture sort mode');
	add_option('aniga_rolemgr', 'no', 'Use Role Manager Plugin');
	add_option('aniga_thumb_csize', '150', 'max Thumbnail size');
	add_option('aniga_thumb_reflection', 'yes', 'create an Apple like reflection on thumbnails');
	add_option('aniga_thumb_reflection_b', 'a4a4a4', 'border for reflection');
	add_option('aniga_thumb_square', 'yes', 'Create square thumbnails');
	
	echo "update postmeta information<br />";
	
	$update = $wpdb->query("UPDATE $wpdb->postmeta SET meta_value = 'gallery-picture.php' WHERE meta_value = 'gallery-category.php'");
	
	echo "update picture data<br />";
	
	$picture_req = "SELECT ".$wpdb->aniga_pic_old.".pid, ".$wpdb->aniga_pic_old.".path, ".$wpdb->aniga_pic_old.".filename, ".$wpdb->aniga_pic_old.".hits, ".$wpdb->aniga_pic_old.".width, ".$wpdb->aniga_pic_old.".height, ".$wpdb->aniga_pic_old.".pic_date, ".$wpdb->aniga_pic_old.".caption, ".$wpdb->aniga_pic_old.".level, ".$wpdb->aniga_alb_old.".wp_id FROM $wpdb->aniga_pic_old, $wpdb->aniga_alb_old WHERE ".$wpdb->aniga_pic_old.".alb_id = ".$wpdb->aniga_alb_old.".alb_id";
	$picture_ret = $wpdb->get_results($picture_req);
	
	foreach ($picture_ret as $picture) {
		$pid = $picture->pid;
		$parent_id = $picture->wp_id;
		$path = $picture->path;
		$filename = $picture->filename;
		$hits = $picture->hits;
		$width = $picture->width;
		$height = $picture->height;
		$pic_date = $picture->pic_date;
		$caption = htmlspecialchars($picture->caption, ENT_QUOTES);
		$level = $picture->level;
		
		$insert = $wpdb->query("INSERT INTO $wpdb->aniga_pic (`pid`, `parent_id`, `path`, `filename`, `hits`, `width`, `height`, `pic_date`, `caption`, `level`) VALUES ('$pid', '$parent_id', '$path', '$filename', '$hits', '$width', '$height', '$pic_date', '$caption', '$level')");
	}
	
	echo "update album data<br />";
	
	$category_req = "SELECT * FROM $wpdb->aniga_cat_old";
	$category_ret = $wpdb->get_results($category_req);
	
	foreach ($category_ret as $category) {
		$id = $category->wp_id;
		$name = htmlspecialchars($category->description, ENT_QUOTES);
		$desc = htmlspecialchars($category->name, ENT_QUOTES);
		$level = $category->level;
		$order = 0;
		$count = 0;
		
		$insert = $wpdb->query("INSERT INTO $wpdb->aniga_alb (`id`, `name`, `desc`, `level`, `order`, `count`) VALUES ('$id', '$name', '$desc', '$level', '$order', '$count')");
	}
	
	$album_req = "SELECT * FROM $wpdb->aniga_alb_old";
	$album_ret = $wpdb->get_results($album_req);
	
	foreach ($album_ret as $album) {
		$id = $album->wp_id;
		$name = htmlspecialchars($album->description, ENT_QUOTES);
		$desc = htmlspecialchars($album->name, ENT_QUOTES);
		$level = $album->level;
		$order = 0;
		$count = 0;
		
		$insert = $wpdb->query("INSERT INTO $wpdb->aniga_alb (`id`, `name`, `desc`, `level`, `order`, `count`) VALUES ('$id', '$name', '$desc', '$level', '$order', '$count')");
	}
	
	echo "update relations data<br />";
	
	foreach ($category_ret as $category) {
		$id = $category->wp_id;
		$parent_id = $baseid;
		
		$insert = $wpdb->query("INSERT INTO $wpdb->aniga_rel (`id`, `parent_id`) VALUES ('$id', '$parent_id')");
	}
	
	foreach ($album_ret as $album) {
		$id = $album->wp_id;
		$parent_id = $album->cat_wp_id;
		
		$insert = $wpdb->query("INSERT INTO $wpdb->aniga_rel (`id`, `parent_id`) VALUES ('$id', '$parent_id')");
	}
	
	echo 'rename Album/Category images in folder "00-gfx"<br />';
	
	$path = $abspath."00-gfx/";

	foreach ($category_ret as $category) {
		$cat_id = $category->cat_id;
		$id = $category->wp_id;
		if (is_file($path."cat_".$cat_id.".jpg")) rename($path."cat_".$cat_id.".jpg", $path."catalbum_".$id.".jpg");
	}
	
	foreach ($album_ret as $album) {
		$alb_id = $album->alb_id;
		$id = $album->wp_id;
		if (is_file($path."album_".$alb_id.".jpg")) rename($path."album_".$alb_id.".jpg", $path."albalbum_".$id.".jpg");
	}
	
	foreach ($category_ret as $category) {
		$cat_id = $category->cat_id;
		$id = $category->wp_id;
		if (is_file($path."catalbum_".$id.".jpg")) rename($path."catalbum_".$id.".jpg", $path."album_".$id.".jpg");
	}
	
	foreach ($album_ret as $album) {
		$alb_id = $album->alb_id;
		$id = $album->wp_id;
		if (is_file($path."albalbum_".$id.".jpg")) rename($path."albalbum_".$id.".jpg", $path."album_".$id.".jpg");
	}
	
	echo 'drop old ANIga tables<br />';

	$drop = $wpdb->query("DROP TABLE $wpdb->aniga_alb_old");
	$drop = $wpdb->query("DROP TABLE $wpdb->aniga_pic_old");
	$drop = $wpdb->query("DROP TABLE $wpdb->aniga_cat_old");
	
	echo "<br /><br /><br /><strong>Update complete. Proceed with step 6</strong>";
}
?>