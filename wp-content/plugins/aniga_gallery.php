<?php
/*
Plugin Name: ANIga gallery
Plugin URI: http://animalbeach.net/aniga
Description: Picture Gallery for WP 2.3 integrated in the WP post system with comments. Including Gallery management and Picture upload function.
Version: 0.50
Author: Michael Naab
Author URI: http://animalbeach.net
*/

/*
#####  CONTENT / HELP   ##################################################
##########################################################################


#### this version REQUIRES Wordpress 2.3 ####


# TODO
==============
	+ I am open for your feedback!!
	
	
# CHANGELOG
==============
	+ v0.50		06.10.2007	code rewritten, data structure rewritten, new featrues: nested albums, square thumbnails, better upload/resize code
	+ v0.30		11.08.2006	new features: support for gif/png, mass management for pictures, Level based permissions, Picture Captions, Bulk upload,
							w3 validation, JS pop-up or lightbox support, multi language support, theme templates without much code and some smaller enhancements
							fixed bug: update permalink structure when updating Cat/Album
	+ v0.26		28.07.2006	fixed bug: insert into db.
	+ v0.25		29.06.2006	update version tracking, send trackback added, delete pictures 
							from filesystem, zip upload disabled for 'safe mode' on
	+ v0.20		29.06.2006	javascript slideshow added
							(script from http://www.barelyfitz.com/homepages/patrick.fitzgerald/)
	+ v0.18		27.06.2006	bug fixed: table prefix comments db.
	+ v0.17		26.06.2006	bug fixed: reenable plugin creates 'photography' again
	+ v0.16		25.06.2006	image zip upload added
	+ v0.13		23.06.2006	create album/category images automatically; change thumbnail-columns by an option;
							image and the prev/next images stay fixed for optimal viewing
	+ v0.12		23.06.2006	error message added if submitted Picture directoy is not valid.
	+ v0.11		23.06.2006	Fixed Bug with permalink set to default
	+ v0.1		22.06.2006	Initial Release


# INSTALLATION
==============
	see readme.txt

# UPDATE
==============
	see readme.txt	
	
		
+++ SOME THINGS MIGHT NOT WORK PROPERLY - USE AT OWN RISK - IF YOU LIKE THIS PLUGIN, PLEASE GIVE ME FEEEDBACK ABOUT BUGS AND SUGGESTIONS! +++
	
	
# HOW TO USE
==============

	visit http://animalbeach.net/ANIga/ for usage information
	or http://forum.animalbeach.net/ for support


#####  COPYRIGHT   #######################################################
##########################################################################
Copyright (c) 2006 Michael Naab, http://www.animalbeach.net

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish and/or distribute copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

//########################################################################
//########################################################################
*/

// some configs for gallery
//########################################################################
// User Levels
$aniga_level = array(0 => __('Everyone', 'aniga'), 1 => __('Userlevel', 'aniga') . ' >= 1', 2 => __('Userlevel', 'aniga') . ' >= 2', 3 => __('Userlevel', 'aniga') . ' >= 3', 4 => __('Userlevel', 'aniga') . ' >= 4', 5 => __('Userlevel', 'aniga') . ' >= 5', 6 => __('Userlevel', 'aniga') . ' >= 6', 7 => __('Userlevel', 'aniga') . ' >= 7', 8 => __('Userlevel', 'aniga') . ' >= 8', 9 => __('Userlevel', 'aniga') . ' >= 9', 10 => __('Administrator', 'aniga'));

// table names
$wpdb->aniga_alb			= $wpdb->prefix . 'aniga_albums';
$wpdb->aniga_pic			= $wpdb->prefix . 'aniga_picture';
$wpdb->aniga_rel			= $wpdb->prefix . 'aniga_relations';


//function to create the tables on plugin activation
//########################################################################
function aniga_install () {
   global $wpdb;

   require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

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
   
	$column_name = 'comment_pid_ID';
	$create_ddl = "ALTER TABLE `".$wpdb->comments."` ADD `".$column_name."` VARCHAR( 20 ) NOT NULL DEFAULT '';";
	maybe_add_column($wpdb->comments, $column_name, $create_ddl);
 
if (get_option('aniga_base-id') == '') {

	$post_author = 1;
	$post_content = 'Photo gallery';
	$post_title = 'photography';
	$post_date = gmdate('Y-m-d H:i:s');
	$post_date_gmt = gmdate('Y-m-d H:i:s');
	$post_category = 1;
	$post_status = 'publish';
	$comment_status = 'closed';
	$ping_status = 'closed';
	$post_parent = 0;
	$post_type = 'page';
	$page_template = 'gallery-index.php';
	
	$post_data = compact('post_content','post_title','post_date','post_date_gmt','post_author','post_category', 'post_status', 'comment_status', 'ping_status', 'post_status', 'post_parent', 'post_type', 'page_template');

	$post_data = add_magic_quotes($post_data);

	$post_ID = wp_insert_post($post_data);
	
	add_option('aniga_base-id', $post_ID, 'base wp ID for ANIga');	
	}
	
	$urlpath = get_bloginfo('url');
	add_option('aniga_dirpath', $urlpath.'/wp-content/gallery/', 'dir path');
	$abspath = str_replace("\\", "/", ABSPATH);
	add_option('aniga_abspath', $abspath.'wp-content/gallery/', 'absolute path');
	
	add_option('aniga_thumb_size', '100', 'max Thumbnail size');
	add_option('aniga_thumb_csize', '150', 'max Thumbnail size');
	add_option('aniga_thumb_square', 'yes', 'Create square thumbnails');
	add_option('aniga_caimg_size', '100', 'max Cat/Alb size');
	add_option('aniga_norm_size', '400', 'max normal Image size');
	add_option('aniga_resize', 'yes', 'resize Images');
	add_option('aniga_resize_qual', '100', 'resize Quality');
	add_option('aniga_thumb_reflection', 'yes', 'create an Apple like reflection on thumbnails');
	add_option('aniga_thumb_reflection_b', 'a4a4a4', 'border for reflection');
	
	add_option('aniga_rolemgr', 'no', 'Use Role Manager Plugin');
	add_option('aniga_colums', '3', 'colums in thumbnail page');
	add_option('aniga_css', 'yes', 'external CSS style');
	add_option('aniga_slds_prefetch', '12', 'ANIga slideshow prefetch');
	add_option('aniga_slds_time', '4000', 'ANIga slideshow time');
	add_option('aniga_slds_filter', 'no', 'ANIga slideshow filter');
	add_option('aniga_delete', 'yes', 'ANIga delete pictures from filesystem');
	add_option('aniga_trackback_sent', 'no', 'ANIga trackback sent');
	add_option('aniga_img_mode', 'link', 'ANIga Picture link mode');	
	add_option('aniga_img_sort', 'filename', 'Picture sort mode');
	add_option('aniga_img_order', 'ASC', 'Picture order mode');
	
	
	
if (ini_get('safe_mode') || ini_get('safe_mode') == 'on') $zip_mode = "none";
else {
	if (function_exists('zip_open')) {
	   $zip_mode = "zip";
	} else {
		if (function_exists('exec')) {
		   $zip_mode = "mzip";
		} else {
		   $zip_mode = "mzipx";
		}
	}
}
	add_option('aniga_zip_mode', $zip_mode, 'zip mode');

}
add_action('activate_aniga_gallery.php','aniga_install');

load_plugin_textdomain('aniga', 'wp-content/plugins/ANIga/language');

// load functions if needed
//########################################################################
function aniga_start_gallery()
{
require_once("ANIga/functions.php");
}

//adds the pid to comments for pictures
//########################################################################
function aniga_comment_add_pid($comment_ID)
{
global $wpdb;

$ppid = $_GET['pid'];

if ($ppid != '')

	$dopid = $wpdb->query("UPDATE $wpdb->comments SET comment_pid_ID = '$ppid' WHERE comment_ID = '$comment_ID' LIMIT 1");

return $comment_ID;
}
add_action('comment_post','aniga_comment_add_pid');
add_action('trackback_post','aniga_comment_add_pid');
add_action('pingback_post','aniga_comment_add_pid');

// adds the gallery-css link to the HEAD and the javascript code
// to slideshow pages
//########################################################################
function aniga_add_css()
{
?>
<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_directory'); ?>/gallery-style.css" media="screen" />
<?php
}
if (get_option('aniga_css') == 'yes') {
add_action('wp_head','aniga_add_css');
}
function aniga_add_css_admin()
{
?>
<link rel="stylesheet" type="text/css" href="<?php bloginfo('wpurl'); ?>/wp-content/plugins/ANIga/admin-style.css" media="screen" />
<?php
}
add_action('admin_head','aniga_add_css_admin');

function aniga_add_slds_js() {
	include ("ANIga/slideshow.php");
}
if ($_GET['slideshow'] == 'start') {
add_action('wp_head', 'aniga_add_slds_js');
}



//adds the gallery managment menus.
//########################################################################
// adds pages and roles
function aniga_add_pages() {
	
	if (get_option('aniga_rolemgr') == 'yes') {
		$role_mng = 'gallery_manage';
		//$role_cat = 'gallery_category';
		$role_alb = 'gallery_album';
		$role_ins = 'gallery_insert';
		$role_opt = 'gallery_options';
	}	
	else $role_mng = $role_alb = $role_ins = $role_opt = 10;
	
// Add a new top-level menu (Photography):
    add_menu_page(__('Manage ANIga gallery', 'aniga'), 'ANIga', $role_mng, 'ANIga/admin_manage.php');
// Add a submenu to the top-level menu (New Relations):
    add_submenu_page('ANIga/admin_manage.php', __('Add a New Album - ANIga gallery', 'aniga'), __('New Album', 'aniga'), $role_alb, 'ANIga/admin_rel.php');
// Add a submenu to the top-level menu (Insert Pictures):
    add_submenu_page('ANIga/admin_manage.php', __('Insert Pictures into Album - ANIga gallery', 'aniga'), __('Insert Pictures', 'aniga'), $role_ins, 'ANIga/admin_insert.php');
// Add a submenu to the top-level menu (Options):
    add_submenu_page('ANIga/admin_manage.php', __('Change Options for ANIga gallery', 'aniga'), __('Options', 'aniga'), $role_opt, 'ANIga/admin_option.php');
}

// Insert the gallery managment menus and file upload menus into the plugin hook list for 'admin_menu'
add_action('admin_menu', 'aniga_add_pages');

// start the page cookie function
//########################################################################
function aniga_set_page_cookie() // set a cookie for thumbnail-number
{
	$expire = time() + 30000000;
	if (!empty($_GET["set_page"])) {
		setcookie("gal_page" . COOKIEHASH,
							stripslashes($_GET["set_page"]),
							$expire,
							COOKIEPATH
							);
		
		$redirect = preg_replace('/set_page=/', 'gal_set_page=', $_SERVER['REQUEST_URI']);

		if (function_exists('wp_redirect'))
			wp_redirect($redirect);
		else
			header("Location: ". $redirect);

		exit;	
		}
}
aniga_set_page_cookie();


// get permalinks for pictures
//########################################################################
function aniga_get_permalink($id = false, $go_id = 0, $do = 'pid', $nav = true) // get a permalink to a picture
{
	global $post, $wp_rewrite;
	
	//rewrite of "get_page_link($id = false)"
	// can be used everywhere to display a specific picture or page
	
	if ( !$id )
		$id = $post->ID;

	$pagestruct = $wp_rewrite->get_page_permastruct();
	
	if ( !$nav ) $do_nav = '';
	else $do_nav = '#picture_nav';

	if ( '' != $pagestruct ) {

		$link = get_page_uri($id);
		$link = str_replace('%pagename%', $link, $pagestruct);
		$link = get_settings('home') . "/".$link."/?".$do."=".$go_id.$do_nav;
	} else {
		$link = get_settings('home') . "/?page_id=".$id."&".$do."=".$go_id.$do_nav; //maybe the '&' will get screwed up?
	}

	return apply_filters('page_link', $link, $id);
}


function aniga_get_userinfo() {
	global $userdata;
	get_currentuserinfo();
	if ($userdata->user_level == '') $user = 0;
	else $user = $userdata->user_level;
	return $user;
}

//RANDOM IMAGE FUNCTION
//########################################################################
function aniga_show_rand_img($before = '<p align="center">', $after = '</p>', $limit = 3) {
	//show random images on the sidebar with one random comment (if there is one for this picture)
	
	global $wpdb;
	
	$i=1;
	$level = aniga_get_userinfo();
	$rand_img_req = "SELECT pid, path, filename, parent_id FROM $wpdb->aniga_pic WHERE level <= $level ORDER BY RAND() LIMIT $limit";
	$rand_img_ret = $wpdb->get_results($rand_img_req);
	
	if ($rand_img_ret) {
	foreach ($rand_img_ret as $rand_img) {
	
		$rand_com_req = "SELECT comment_ID, comment_content FROM $wpdb->comments WHERE comment_pid_ID=$rand_img->pid ORDER BY RAND() LIMIT 1";
		$rand_com_ret = $wpdb->get_results($rand_com_req);
		if ($wpdb->num_rows == 1 ) {
			foreach ($rand_com_ret as $rand_com) {
				$com_lenth = 7;
				$com_trans = array("<br />" => " ");
				$com_content_br = strtr($rand_com->comment_content, $com_trans);
				$com_content = strip_tags($com_content_br);
				$com_content = stripslashes($com_content);
				$com_words=split(" ",$com_content); 
				$com_excerpt_long = join(" ",array_slice($com_words,0,$com_lenth));
				$com_excerpt = wordwrap($com_excerpt_long, 25, "\n", 1);
				if ($com_excerpt == "")
					$com_excerpt = "[...]";
				else $com_excerpt = '"' . $com_excerpt . '"';
			}
		}
		//else $com_excerpt = __('no comments', 'aniga');	//if you want "no comments" below picture
		else $com_excerpt ='';								//if you dont want any text below picture if therre is no commment..
		echo $before; ?>
		   
	<a href="<?php echo aniga_get_permalink($rand_img->parent_id, $rand_img->pid, 'pid'); ?>" title="<?php _e('view this picture', 'aniga'); ?>"><img src="<?php echo $rand_img->path . 'thumb_' . $rand_img->filename; ?>" class="gallery_thumb_border" alt="<?php _e('Random Picture', 'aniga')?>" /></a>
	
	<?php 
	/* with reflection: <div style="position: relative; z-index: 1; top: -15px;text-align: center;"><?php echo $com_excerpt; ?></div>
	
	without: <br /><?php echo $com_excerpt; ?>*/ ?>
    
    <div style="position: relative; z-index: 1; top: -20px;text-align: center; overflow:hidden;"><?php echo $com_excerpt; ?></div>
    
    <?php
	echo $after; ?>
	<?php 
		$i++;
		} 
	}
}

function aniga_show_rand_img_nc($before = '<p align="center">', $after = '</p>', $limit = 3) {
	global $wpdb;
	
	$i=1;
	$level = aniga_get_userinfo();
	$rand_img_req = "SELECT pid, path, filename, parent_id FROM $wpdb->aniga_pic WHERE level <= $level ORDER BY RAND() LIMIT $limit";
	$rand_img_ret = $wpdb->get_results($rand_img_req);
	
	if ($rand_img_ret) {
	foreach ($rand_img_ret as $rand_img) {
	
	 echo $before; ?>
		<a href="<?php echo aniga_get_permalink($rand_img->parent_id, $rand_img->pid, 'pid'); ?>" title="<?php _e('view this picture', 'aniga'); ?>"><img src="<?php echo $rand_img->path . 'thumb_' . $rand_img->filename; ?>" class="gallery_thumb_border" alt="<?php _e('Random Picture', 'aniga')?>" /></a>
                
		<?php
        echo $after;
		$i++;
		} 
	}
}

//LATEST COMMENT FUNCTIONS
//########################################################################
function aniga_latest_comments($no_comments = 5, $comment_lenth = 5, $before = '<li>', $after = '</li>', $show_pass_post = false)
{
	// based on the recent_comments plugin.
    global $wpdb;
	
// this query does not show comments for pictures wirh higher Level, but is really slow, when there are thousends of pics :(	
/*	$level = aniga_get_userinfo();
	
    $request = "SELECT DISTINCT $wpdb->posts.ID, $wpdb->comments.comment_ID, $wpdb->comments.comment_content, $wpdb->comments.comment_author, $wpdb->comments.comment_author_url, $wpdb->posts.post_title, $wpdb->comments.comment_pid_ID ";
	$request .=	"FROM $wpdb->comments, $wpdb->posts, $wpdb->anigal_pic ";
	$request .= "WHERE $wpdb->posts.ID = $wpdb->comments.comment_post_ID ";
	$request .=	"AND $wpdb->posts.post_status IN ('publish','static') ";
	if(!$show_pass_post)
	$request .= "AND $wpdb->posts.post_password = '' ";
	$request .= "AND ($wpdb->comments.comment_pid_ID = '' OR $wpdb->comments.comment_pid_ID IS NULL OR ($wpdb->comments.comment_pid_ID = $wpdb->anigal_pic.pid AND $wpdb->anigal_pic.level <= $level)) ";
	$request .=	"AND $wpdb->comments.comment_approved = '1' ";
	$request .= "ORDER BY $wpdb->comments.comment_ID DESC LIMIT $no_comments";*/

    $request = "SELECT ID, comment_ID, comment_content, comment_author, comment_author_url, post_title, comment_pid_ID FROM $wpdb->comments LEFT JOIN $wpdb->posts ON $wpdb->posts.ID=$wpdb->comments.comment_post_ID WHERE post_status IN ('publish','static') ";
	if(!$show_pass_post) $request .= "AND post_password ='' ";
	$request .= "AND comment_approved = '1' ORDER BY comment_ID DESC LIMIT $no_comments";
	$comments = $wpdb->get_results($request);
	
	$comments = $wpdb->get_results($request);
	
	if ($comments) {
	
	foreach ($comments as $comment) {
	
		$comment_author = stripslashes($comment->comment_author);
		if ($comment_author == "")
			$comment_author = "anonymous";
			
		$trans = array("<br />" => " ");
		$comment_content_br = strtr($comment->comment_content, $trans);
		$comment_content = strip_tags($comment_content_br);
		$comment_content = stripslashes($comment_content);
		$words=split(" ",$comment_content); 
		$comment_excerpt_long = join(" ",array_slice($words,0,$comment_lenth));
		$comment_excerpt = wordwrap($comment_excerpt_long, 50, "\n", 1);
		if ($comment_excerpt == "")
			$comment_excerpt = "[...]";			
		$comment_excerpt = convert_smilies($comment_excerpt);
				
		if (!empty($comment->comment_pid_ID)) $permalink = aniga_get_permalink($comment->ID, $comment->comment_pid_ID, 'pid', false);			
		else $permalink = get_permalink($comment->ID);
		
		$permalink = $permalink."#comment-".$comment->comment_ID;
		
		$url = $comment->comment_author_url;
				
		echo $before;
		
		if (empty($url)) {	
			echo $comment_author;
		}
		else {
		
?>
		<a href="<?php echo $url; ?>"><?php echo $comment_author; ?></a>
<?php		
		}	
?>
		: <a href="<?php echo $permalink; ?>" title="<?php printf(__('View the entire comment by %s', 'aniga'), $comment_author); ?>"><?php echo $comment_excerpt; ?></a>
<?php					
		echo $after;
		} //end foreach
	} //end if(comments)
} //end function

//SHOW LATEST PICTURES FUNCTION
//########################################################################
function aniga_show_last_pic($before = '<li>', $after = '</li>', $limit = 3) {

	global $wpdb;
	$level = aniga_get_userinfo();
	$last_pic_req = "SELECT pid, parent_id, filename FROM $wpdb->aniga_pic WHERE level <= $level ORDER BY pic_date DESC LIMIT $limit";
	$last_pic_ret = $wpdb->get_results($last_pic_req);
	if ($last_pic_ret) {
		foreach ($last_pic_ret as $last_pic) {
			echo $before;
			?>
			<a href="<?php echo aniga_get_permalink($last_pic->parent_id, $last_pic->pid, $do = 'pid'); ?>"><?php echo $last_pic->filename; ?></a>
			<?php
			echo $after;
		}
	}
}

//SHOW LATEST GALLERIES FUNCTION
//########################################################################
function aniga_show_last_gal($before = '<li>', $after = '</li>', $limit = 3) {
	
	global $wpdb;
	$level = aniga_get_userinfo();
	$last_gal_req = "SELECT id, name FROM $wpdb->aniga_alb WHERE level <= $level ORDER BY id DESC LIMIT 3";
	$last_gal_ret = $wpdb->get_results($last_gal_req);
	if ($last_gal_ret) {
		foreach ($last_gal_ret as $last_gal) {
			echo $before;
			?>
			<a href="<?php echo get_permalink($last_gal->id); ?>"><?php echo $last_gal->name; ?></a>
			<?php
			echo $after;
		}
	}
}

?>