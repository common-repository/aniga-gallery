<?php		
// Display Footer on Admin Pages.
//########################################################################

function aniga_admin_footer() {
	//print admin footer in dashboard
	
	$anigal_curr_vers = "0.50"; // current Version of ANIga gallery ?>
	
	<div class="wrap" align="center">powered by <a href="http://www.animalbeach.net/aniga/"><strong>ANIga gallery</strong></a> -- your version is <strong><?php echo $anigal_curr_vers; ?></strong> -- <?php _e('the latest stable version is', 'aniga'); ?> <strong><?php echo aniga_get_latest_vers(); ?></strong> -- &copy; <a href="mailto:web@animalbeach.net">Michael Naab</a> 2006
	</div>
	<?php
}

function aniga_get_latest_vers() {
	// retrieve latest version of ANIga
	
	include_once(ABSPATH . WPINC . '/class-snoopy.php');
	$no_version = "(could not retrieve latest version)";
	if (class_exists(snoopy)) {
		$client = new Snoopy();
		$client->_fp_timeout = 4;
		if (@$client->fetch('http://www.animalbeach.net/public/ANIga/version.txt') === false) {
			return $no_version;
		}
		$remote = $client->results;
		if (!$remote || strlen($remote) > 50 ) {
			return $no_version;
		} 
		return $remote;
	}
	else return $no_version;
}


// File and Zip functions
//########################################################################

class aniga_file {

	var $fname;
	var $srcfile;
	
	function handle($file, $filename, $path, $rsize, $csize, $move = true, $rename = false, $resize = true, $crop = false) {
		
		$this->fname = $filename;
		$this->srcfile = $file;
		
		//if ($alt_img && $file == '') $file = ABSPATH . 'wp-content/plugins/ANIga/noimage.jpg';
		
		if ($move) $msg .= $this->move_file($file, $path, $this->fname, $rename);
		
		if ($resize) {
		
			$vers = substr(phpversion(), 0, 1);

			include_once('thumbnail.php'.$vers.'.php');
			
			$thumb = new Thumbnail($this->srcfile);
			
			if ($crop) {
				$thumb->resize($csize,$csize);
				$thumb->cropFromCenter($rsize);
			}
			else $thumb->resize($rsize,$rsize);
			
			if (get_option('aniga_thumb_reflection') == 'yes') {
				$border = get_option('aniga_thumb_reflection_b');
				$thumb->createReflection(40,35,75,true,'#'.$border);
			}

			$thumb->save($path.$this->fname,get_option('aniga_resize_qual'));
		}
		
		return $msg;
	}
	
	function move_file($file, $path, $filename, $rename = false) {
		// move uploaded to directory
		
		if ($rename && is_file($path.$filename)) {
			for ($i = 1; is_file($path.$filename); $i++) {
				$filename = $this->rename_file($filename, $i);
			}
		}
		$this->fname = $filename;
		
		if(move_uploaded_file($file, $path.$this->fname)) {
			chmod($path.$this->fname, 0644);
			$this->srcfile = $path.$this->fname;
			return "<p>" . sprintf(__('Picture %s uploaded.', 'aniga'), $this->fname) . "</p>";
		}
		else {
			return "<p>" . sprintf(__('Picture %s could not be uploaded.', 'aniga'), $this->fname) . "</p>";
		}
	}
	
	function rename_file($filename, $nr) {
		// rename files if necessary
		
		$pos = strrpos($filename, ".");
		$name = substr($filename, 0, $pos);
		$ext = substr($filename, $pos, strlen($filename)-$pos);
		if(ereg("[0-9]", substr($name, -1) )) 
			$string = substr($name, 0, strlen($name)-1) . "$nr" . "$ext";
		else
			$string = "$name" . "_$nr" . "$ext";
		return $string;
	}
}					

function okfiletype($filename, $allowed) {
	// check if filetype is ok 
	
	for($i = 0; $i < count($allowed); $i++) {
		if(strtolower($allowed[$i]) == strtolower(substr($filename, -3))  || strtolower($allowed[$i]) == strtolower(substr($filename, -4)))
			return true;	
	}
	return false;
}

function aniga_zip_upload($dir, $file_org, $file_tmp, $db_path) {
	// upload and unzip archives with php zip library
	
	$file = $dir."/".$file_org;
	$msgf = "";
	if(move_uploaded_file($file_tmp, $file)) {	
		$zip = zip_open($file);
		$i = 0;
		if ($zip) {
			while ($zip_entry = zip_read($zip)) {
				if (zip_entry_open($zip, $zip_entry, "r")) {
					$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					$fp = fopen($dir."/".zip_entry_name($zip_entry),"w");
					fwrite($fp,$buf);
					$msgf .= zip_entry_name($zip_entry)."<br>";
					zip_entry_close($zip_entry);
					$i++;
				}
			}
			zip_close($zip);
			
			$msgf .= "<p>" . sprintf(__('The file \'%s\' has been un-zipped to:', 'aniga'), basename($file_org)) . " '$db_path'<p />";
		}
		else $msgf .= "<p>" . __('could not read zip file', 'aniga') . "</p>";
		unlink($file);
	}
	else $msgf .= "<p>" . __('could not move zip file to', 'aniga') . " '$db_path'</p>";
	return $msgf;
}

//GALLERY TEMPLATE CLASSES
//########################################################################

class aniga_index { // print gallery index page

	var $path;
	var $alg = 'gallery_alignleft';
	var $talg = 'gallery_talignleft';
	
	function heading() {
	
		include("templates/index.heading.inc.php");
		
	}
			
	function loop_html($id, $name, $description) { 
	
		global $aniga; 
		
		$meta = $aniga->getchildren_c_all($id, true, true, true); 
		
		include("templates/index.albums.inc.php");

	}
	
	function meta() {
	
		global $aniga; 
		
		$meta = $aniga->getchildren_c_all(get_option('aniga_base-id'), true, true, true); 
		
		include("templates/index.meta.inc.php");
	
	}
	
	function loop() {
		
		global $aniga;
		//$aniga->user = $aniga->user();
		$this->path = get_option('aniga_dirpath').'00-gfx/album_';
		$aniga->getchildren(get_option('aniga_base-id'), ' ', false);
		if ($aniga->child_array) {
			foreach ($aniga->child_array as $i => $val) {
			
				$this->loop_html($i, $val['name'], $val['desc']);
	
				if ('gallery_alignleft' == $this->alg) {
					$this->alg = 'gallery_alignright';
					$this->talg = 'gallery_talignright';
				}
				 else {
					$this->alg = 'gallery_alignleft';
					$this->talg = 'gallery_talignleft';
				}
			}
		}
		else echo '<div class="gallery_error">' . __('uups. No Categories in this Gallery or you are not allowed to view the Categories in this Gallery!<br />Try to login.', 'aniga') . '</div>';
	}
	
}


class aniga_picture { // print thumbnails or picture for an album

	var $pid;
	var $id;
	var $alb_id;
	var $cat_wp_id;
	var $album = array();
	var $page = array();
	var $pic = array();
	var $pic_link;
	var $path;
	var $alg = 'gallery_alignleft';
	var $talg = 'gallery_talignleft';
	var $has_childs = false;
	
	
	function slideshow_js() {
		include ("slideshow.php");
	}
	
	function slideshow() {
		
		global $aniga;
		$album = $this->album;
		$first = $aniga->getfirstpic($album->id, get_option("aniga_img_sort"), get_option("aniga_img_order"));
		
		$this->navthumb();
		
		include("templates/pic.slideshow.inc.php");
	}
	
	function thumbnails() {
	
		global $aniga;
		
		$this->navthumb();	
		$this->calcpage();
		
		$this->childalbum();
		
		if ($this->page['page_max'] >= $this->page['page']) { // show thumbnails if pages are ok ?>
					
			<div class="gallery_pad_thumb">
			
			<?php
			$this->thumbmenu(); ?>
			
			<table border="0" align="center" cellspacing="0" cellpadding="0">
			
			<?php
			$this->thumbpics(); ?>

			</table>
			
			<?php
			$this->thumbpage(); ?>

			</div>
			
			<?php
			$this->thumbmeta();
			
		}
		elseif ($this->has_childs) {
			$this->thumbmeta();
		}
		else echo '<div class="gallery_error">' . __('uups. There are no Pictures in this Album or there is a mixup with the Pages or you are not allowed to view the Pictures in this Album.', 'aniga') . '</div>';
	}
	
	function childalbum() {
	
		global $aniga;
		$album = $this->album;
		$this->path = get_option('aniga_dirpath').'00-gfx/album_';
		$aniga->getchildren($album->id, ' ', false);
		if ($aniga->child_array) {
			$this->has_childs = true;
			foreach ($aniga->child_array as $i => $val) {
			
				if ($i != $album->id) {
					$this->childalbum_html($i, $val['name'], $val['desc']);

					if ('gallery_alignleft' == $this->alg) {
						$this->alg = 'gallery_alignright';
						$this->talg = 'gallery_talignright';
					}
					 else {
						$this->alg = 'gallery_alignleft';
						$this->talg = 'gallery_talignleft';
					}
				}
			}
		}
	}
		
	function childalbum_html($id, $name, $description) {
	
		global $aniga; 
		
		$meta = $aniga->getchildren_c_all($id, true, true, true); 
		
		include("templates/pic.childalbums.inc.php");	
	
	}
	
	function navthumb() {
		
		global $aniga;
		$album = $this->album;
		$aniga->nav_thumb($album->id); // NAVIGATION
		include("templates/pic.navthumb.inc.php");
	}

	function calcpage() {
		
		global $aniga;
		$album = $this->album;
	
		$this->page['page'] = $_GET['page'];
		if (empty($this->page['page']) || $this->page['page'] < 1) $this->page['page'] = 1;
		
		$this->page['max_pic'] = $aniga->c_alb_pic($album->id);
		
		if (empty($_COOKIE["gal_page" . COOKIEHASH])) $this->page['pt'] = 5 * get_option('aniga_colums'); // first value from array!
		else $this->page['pt'] = $_COOKIE["gal_page" . COOKIEHASH];  
		
		$this->page['page_start'] = ($this->page['page'] * $this->page['pt']) - $this->page['pt'];
		$this->page['page_end'] = $this->page['pt'];
		
		$this->page['prev_page'] = $this->page['page'] - 1;
		$this->page['next_page'] = $this->page['page'] + 1;
		
		if (is_int($this->page['max_pic']/$this->page['pt'])) $this->page['page_max'] = $this->page['max_pic']/$this->page['pt'];
		else $this->page['page_max'] = floor($this->page['max_pic']/$this->page['pt']) + 1;
	}
	
	function thumbmenu() {
		
		global $aniga;
		
		include("templates/pic.thumbmenu.inc.php");
	}
	
	function thumbcount() {
		// calc the thumb counts
		
		$cols = get_option('aniga_colums');
		$thumbs = 5 * $cols;
		$thumbs_count = array($thumbs => $thumbs . ' ' . __('thumbnails', 'aniga'), 2*$thumbs => 2*$thumbs . ' ' . __('thumbnails', 'aniga'), 5*$thumbs => 5*$thumbs . ' ' . __('thumbnails', 'aniga'), 10*$thumbs => 10*$thumbs . ' ' . __('thumbnails', 'aniga'), 999 => __('all thumbnails', 'aniga'));
		
		return $thumbs_count;
	}
	
	function thumbselect($id) {
		// print the thumbnail-number select-box ?>
		
		<select name="gallery_pages" onchange="location.href='<?php echo aniga_get_permalink($id, '', $do = 'set_page', false); ?>' + this.options[this.selectedIndex].value;" class="gallery_select">

		<?php 
		foreach ($this->thumbcount() as $k => $v) {
			echo '<option value="' . $k . '"';
			if ($_COOKIE["gal_page" . COOKIEHASH] == $k) { echo ' selected="selected"'; }
			echo '>' . $v . '</option>';
		} ?>
		
		</select>	
	<?php
	}
	
	function thumbpages($page, $prev_page, $next_page, $page_max, $id) {
		// print the thumbnail page navigation
		
		if ($page_max != 1) {
			if ($page != 1) { ?>
			
				<span class="gallery_high">&laquo;</span> <a href="<?php echo aniga_get_permalink($id, $prev_page, 'page'); ?>"><?php _e('previous', 'aniga'); ?></a> 

			<?php	
			}		
			for ($j = 1; $j <= $page_max; $j++) {
				if ($j == $page) { 
					echo $j; 	
				}
				else { ?>  
				
					<a href="<?php echo aniga_get_permalink($id, $j, 'page'); ?>"><?php echo $j; ?></a>  

				<?php
				}
			}	
			if ($page != $page_max) { ?>
			
				<a href="<?php echo aniga_get_permalink($id, $next_page, 'page'); ?>"><?php _e('next', 'aniga'); ?></a> <span class="gallery_high">&raquo;</span>
			<?php	
			}
		}
	}
		
	function thumbpage() {
		
		global $aniga; ?>
		
		<div class="gallery_thumb_pages">
					
			<?php
			$this->thumbpages($this->page['page'], $this->page['prev_page'], $this->page['next_page'], $this->page['page_max'], $this->id); ?>
			
		</div>
		
		<?php
	}
	
	function thumbpics() {
		
		global $aniga;
		$album = $this->album;	
		$col = 1;
		$col_count = get_option('aniga_colums'); 
		$thumbs = $aniga->getthumbs($album->id, $this->page['page_start'], $this->page['page_end']);	
		foreach ($thumbs as $pic) { 
			
			if ($col == 1) : ?>
			
			<tr>
			
			<?php endif; ?>
					
			<td align="center" valign="middle" class="gallery_thumb_td"><a href="<?php echo aniga_get_permalink($this->id, $pic->pid, 'pid'); ?>"><img src="<?php echo $pic->path . 'thumb_' . $pic->filename; ?>" class="gallery_thumb_border" alt ="<?php echo $pic->filename; ?>" /></a>
							
			<?php
			$com = $aniga->c_pic_com($pic->pid);
			switch ($com):
			case 0: ?>
			
				<div class="gallery_thumb_comments">&nbsp;</div>
				
				<?php
				break;
				
			case 1: ?>
			
				<div class="gallery_thumb_comments">1 <?php _e('comment', 'aniga'); ?></div>
				
				<?php
				break;
			
			case ($com > 1): ?>
			
				<div class="gallery_thumb_comments"><?php echo $com; ?> <?php _e('comments', 'aniga'); ?></div>
				
				<?php
				break;
			endswitch; ?>
			
			</td>
						
			<?php
			if ($col == $col_count) { ?>
			
			</tr> 
				
				<?php
				$col = 1;
			}						
			else $col++;	
		} // end foreach
	}
	
	function thumbmeta() {
		
		global $aniga; 
		$album = $this->album;
		
		include("templates/pic.thumbmeta.inc.php");			
	}

	function navpic($cur_page) {
		
		global $aniga;
		$album = $this->album;
		$aniga->nav_thumb($album->id); // NAVIGATION
		
		include("templates/pic.navpic.inc.php");
	}
	
	function picture_link() {
	
		$picture = $this->pic;
		
		$src = '<img name="SLIDESIMG" src="'.$picture->path . 'normal_' . $picture->filename . '" border="0" alt="' . $picture->filename . '" />';
		$after = '</a>';
		
		switch (get_option("aniga_img_mode")):
		case "link":
			$link = '<a href="' . $picture->path . $picture->filename .'" title="' . $picture->caption . ' - ' . __('click for full size', 'aniga') . ' (' . $picture->width . ' x ' . $picture->height .' px)" >';
			break;
		case "js": ?>
			<script type="text/javascript">
			function openpopup(popurl){
			var winpops=window.open(popurl,"","width=<?php echo $picture->width + 40; ?>,height=<?php echo $picture->height + 40; ?>,scrollbars,resizable")
			}
			</script>
			<?php
			$link = "<a href=\"javascript:openpopup('" . $picture->path . $picture->filename . "')\" >";
			break;
		case "lightbox":
			$link = '<a href="' . $picture->path . $picture->filename .'" rel="lightbox" >';
			break;
		case "none":
			$link = '';
			$after = '';
			break;
		endswitch;
		
		$this->pic_link = $link.$src.$after;
	}
	
	function picture_html_pic($current_pic, $hits, $cur_page) {
		
		global $aniga; 
		$picture = $this->pic;
		$this->picture_link(); 
		
		include("templates/pic.picture.inc.php");
	}
	
	function picture_html_caption() {
	
		$picture = $this->pic;
		if ($picture->caption) {
			include("templates/pic.caption.inc.php");
		}
	}
	
	function picture_html_nav($prev_id, $prev_path, $prev_filename, $next_id, $next_path, $next_filename) { 
	
		include("templates/pic.picture_nav.inc.php");
	}
	
	function picture() {
	
		global $aniga;
		$album = $this->album;
		$this->pic = $aniga->getpic($this->pid);
		$picture = $this->pic;
			
		if (empty($this->pid)) { //some checks, to verify that the picture is ok
			echo '<div class="gallery_error">'. __('Picture not found. Sorry...', 'aniga') . '</div>';
		}
		
		elseif ($picture->parent_id != $album->id){
			echo '<div class="gallery_error">'. __('uhmm.. There is a little mix-up. The requested Picture seems to belong elsewhere...', 'aniga') . '</div>';
		}
		else { // picture is ok:
		
			switch ($aniga->checkpic($picture->level)): // check user level
			case 1:
		
			$hits = $aniga->updatehits($picture->hits, $picture->pid); // update picture hits

		// do the picture navigation 	
		// ##############################################################
			$gallery_nav = $aniga->getpics($picture->parent_id, get_option("aniga_img_sort"), get_option("aniga_img_order"));
					
			$nav_i = 1;
			$nav_j = 1;
			unset($current_pic);
			unset($prev_id);
			unset($prev_path);
			unset($prev_filename);
			unset($next_id);
			unset($next_path);
			unset($next_filename);
					
			foreach ($gallery_nav as $gal_nav_array) {
				if ($gal_nav_array->pid  == $this->pid) $current_pic = $nav_i;
		
				$nav_i++;
			}
				
			foreach ($gallery_nav as $gal_nav_pic) {
				if($nav_j == ($current_pic - 1)){
					$prev_id = $gal_nav_pic->pid;
					$prev_path = $gal_nav_pic->path;
					$prev_filename = $gal_nav_pic->filename;
				}
				else if($nav_j == ($current_pic + 1)){
					$next_id = $gal_nav_pic->pid;
					$next_path = $gal_nav_pic->path;
					$next_filename = $gal_nav_pic->filename;
				}
				$nav_j++;
			}
			
			$cur_page = $aniga->getcurrentpage($current_pic);
		// ##############################################################
		
			$this->navpic($cur_page); 
			
			$this->picture_html_pic($current_pic, $hits, $cur_page);
						
			$this->picture_html_nav($prev_id, $prev_path, $prev_filename, $next_id, $next_path, $next_filename);

			$this->picture_html_caption();

			$this->comments_templ("/gallery-comments.php");

			break;
			
		case 2:
			$this->navthumb();
			echo '<div class="gallery_error">'. __('Login to view this Picture!', 'aniga') . '</div>';
			break;
		
		case 3:
			$this->navthumb();
			echo '<div class="gallery_error">'. __('You are not allowed to view this Picture!', 'aniga') . '</div>';
			break;
		endswitch;
		}
	}
	
	function comments_templ( $file = '/gallery-comments.php' ) {
		// copy from comments_template() to show only comments with the right PID
		
		global $wp_query, $withcomments, $post, $wpdb, $id, $comment, $user_login, $user_ID, $user_identity;
	
		if ( ! (is_single() || is_page() || $withcomments) )
			return;
			
		$req = get_settings('require_name_email');
			
		// make backwards compatible to prior versions
		if (function_exists('wp_get_current_commenter')) {
		
			$commenter = wp_get_current_commenter();
			extract($commenter);		
		}
		else {
		
			$comment_author = '';
			if ( isset($_COOKIE['comment_author_'.COOKIEHASH]) ) {
				$comment_author = apply_filters('pre_comment_author_name', $_COOKIE['comment_author_'.COOKIEHASH]);
				$comment_author = stripslashes($comment_author);
				$comment_author = wp_specialchars($comment_author, true);
			}
			$comment_author_email = '';
			if ( isset($_COOKIE['comment_author_email_'.COOKIEHASH]) ) {
				$comment_author_email = apply_filters('pre_comment_author_email', $_COOKIE['comment_author_email_'.COOKIEHASH]);
				$comment_author_email = stripslashes($comment_author_email);
				$comment_author_email = wp_specialchars($comment_author_email, true);		
			}
			$comment_author_url = '';
			if ( isset($_COOKIE['comment_author_url_'.COOKIEHASH]) ) {
				$comment_author_url = apply_filters('pre_comment_author_url', $_COOKIE['comment_author_url_'.COOKIEHASH]);
				$comment_author_url = stripslashes($comment_author_url);
				$comment_author_url = wp_specialchars($comment_author_url, true);		
			}		
		}
	
		// TODO: Use API instead of SELECTs.
		if ( empty($comment_author) ) {
			$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND comment_approved = '1' AND comment_pid_ID = '$this->pid' ORDER BY comment_date");
		} else {
			$author_db = $wpdb->escape($comment_author);
			$email_db  = $wpdb->escape($comment_author_email);
			$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND comment_pid_ID = '$this->pid' AND ( comment_approved = '1' OR ( comment_author = '$author_db' AND comment_author_email = '$email_db' AND comment_approved = '0' ) ) ORDER BY comment_date");
		}
		
		define('COMMENTS_TEMPLATE', true);
		$include = apply_filters('comments_template', TEMPLATEPATH . $file );
		if ( file_exists( $include ) )
			require( $include );
		else
			require( ABSPATH . 'wp-content/themes/default/comments.php');
	}
		
	function show($id) {
	
		global $aniga;
		$aniga->user = $aniga->user();
		$this->pid = $_GET['pid'];
		$this->id = $id;
		$this->album = $aniga->getalbum($this->id);
		$album = $this->album;
		
		$aniga->getparents($id, true);
		$aniga->parent_array = array_reverse($aniga->parent_array);
					
		switch ($aniga->checkalb($album->level)):
		case 1:
			if ($_GET['slideshow'] == 'start' && $_GET['ssid'] == $this->id) { // show slideshow
				
				$this->slideshow();
				
			}
			elseif (empty($this->pid)) { // no pid => show thumbnails
			 
				$this->thumbnails();
				
			}
			else { // there is a pid => show the picture with navigation and comments. 

				$this->picture();
				
			}	
			break;
			
		case 2:
			$this->navthumb();
			echo '<div class="gallery_error">'. __('Login to view this Album!', 'aniga') . '</div>';
			break;
		
		case 3:
			$this->navthumb();
			echo '<div class="gallery_error">'. __('You are not allowed to view this Album!', 'aniga') . '</div>';
			break;
		endswitch;
	}

}


class aniga_db {


	var $user = '0';
	var $sum, $sum_pics, $sum_hits, $sum_com;
	var $allowed_zip = array("zip");
	var $allowed_pic = array("jpg", "jpeg", "gif", "png");
	var $child_array = array();
	var $parent_array = array();

	function user() {
		
		$user = aniga_get_userinfo();
		return $user;
	}
	
	function query($what) {
	
		global $wpdb;
		$level = $this->user;
		switch ($what):
		case "WHERE":
			return "WHERE level <= $level";
		case "AND":
			return "AND level <= $level";
		case "AND.pic":
			return "AND {$wpdb->aniga_pic}.level <= $level";
		case "AND.alb":
			return "AND {$wpdb->aniga_alb}.level <= $level";
		endswitch;
	}
		
	function checkalb($lvl) {
	
		global $wpdb;
		
		$return = 1;
		foreach ($this->parent_array as $i => $val) {
	
			if ($val['lvl'] <= $this->user) $return = max($return, 1);
			elseif ($this->user == '0') $return = max($return, 2);
			elseif ($this->user >> '0') $return = max($return, 3);
		}	
		return $return;
	}
	
	function checkpic($level) {
		
		if ($level <= $this->user) return 1;
		elseif ($this->user == '0') return 2;
		elseif ($this->user >> '0') return 3;	
	}
	
	function checkpath() {
	
		$msg = '';
		if (substr( get_option('aniga_abspath'), -1) != '/') $msg .= "<p>" . __('WARNING: your absolute path must end with \'/\' !', 'aniga') . "</p>";
		else {
			if ( is_dir( get_option('aniga_abspath') ) ) {
				if ( !is_writable( get_option('aniga_abspath') ) ) $msg .= "<p>" . __('WARNING: your Gallery Directory is not writeable!', 'aniga') . "</p>";
				if ( !is_dir( get_option('aniga_abspath')."00-gfx" ) ) $msg .= "<p>" . __('WARNING: your \'00-gfx\' Directory does not exist!', 'aniga') . "</p>";
				elseif ( !is_writable( get_option('aniga_abspath')."00-gfx" ) ) $msg .= "<p>" . __('WARNING: your \'00-gfx\' Directory is not writeable!', 'aniga') . "</p>";
				if ( !is_dir( get_option('aniga_abspath')."00-single" ) ) $msg .= "<p>" . __('WARNING: your \'00-single\' Directory does not exist!', 'aniga') . "</p>";
				elseif ( !is_writable( get_option('aniga_abspath')."00-single" ) ) $msg .= "<p>" . __('WARNING: your \'00-single\' Directory is not writeable!', 'aniga') . "</p>";
			}
			else $msg .= "<p>" . __('WARNING: your Gallery Directory does not exist or your Settings are incorrect!', 'aniga') . "</p>";
		}
		if (substr( get_option('aniga_dirpath'), -1) != '/') $msg .= "<p>" . __('WARNING: your http url must end with \'/\' !', 'aniga') . "</p>";
		return $msg;	
	}
	

//  #### RETURN DATA FUNCTIONS ###  --------------------------------------
				
	function getparents($id, $allparents = true) {
	
		global $wpdb;
		
		$base_id = get_option('aniga_base-id'); //needs tweaking!
		
		$rel_req = "SELECT {$wpdb->aniga_rel}.id, {$wpdb->aniga_rel}.parent_id, {$wpdb->aniga_alb}.name, {$wpdb->aniga_alb}.desc, {$wpdb->aniga_alb}.level, {$wpdb->aniga_alb}.order  FROM $wpdb->aniga_rel, $wpdb->aniga_alb WHERE {$wpdb->aniga_rel}.id = {$wpdb->aniga_alb}.id AND {$wpdb->aniga_rel}.id = $id ";
		$rel_ret = $wpdb->get_results($rel_req);
		
		foreach ($rel_ret as $relation) {
			if ($relation->id != $base_id) {
				$this->parent_array[$relation->id] = array('id' => $relation->id, 'name' => $relation->name, 'lvl' => $relation->level, 'desc' => $relation->desc);
				if ($allparents) $this->getparents($relation->parent_id, true);
			}
		}	
	
	}
	
	function getchildren($parent_id, $counter, $allchildren = true) {
	
		global $wpdb;
		
		$base_id = get_option('aniga_base-id'); //needs tweaking!
		
		$rel_req = "SELECT {$wpdb->aniga_rel}.id, {$wpdb->aniga_rel}.parent_id, {$wpdb->aniga_alb}.name, {$wpdb->aniga_alb}.desc, {$wpdb->aniga_alb}.level, {$wpdb->aniga_alb}.order  FROM $wpdb->aniga_rel, $wpdb->aniga_alb WHERE {$wpdb->aniga_rel}.id = {$wpdb->aniga_alb}.id AND {$wpdb->aniga_rel}.parent_id = $parent_id ".$this->query("AND.alb")." ORDER BY {$wpdb->aniga_alb}.order DESC";
		$rel_ret = $wpdb->get_results($rel_req);
		
		foreach ($rel_ret as $relation) {
			if ($parent_id != $relation->id) $this->child_array[$relation->id] = array('name' => $relation->name, 'lvl' => $relation->level, 'sep' => $counter, 'desc' => $relation->desc, 'order' => $relation->order, 'parent_id' => $relation->parent_id);
			if ($relation->parent_id != $base_id) $this->child_array[$relation->parent_id]['has_child'] = true;
			$new_counter = '--'.$counter;
			if ($allchildren) $this->getchildren($relation->id,$new_counter);
		}	
		
		//return $gal_cat_ret;
	}
	
	function getchildren_c_pic($parent_id) {
	
		global $wpdb;
		
		$this->sum_pics = 0;
		
		$this->getchildren_query($parent_id);
		
		return $this->sum_pics;
	}
	
	function getchildren_c_all($parent_id) {
	
		global $wpdb;
		
		$this->sum_pics = $this->sum = $this->sum_hits = $this->sum_com = 0;
		
		$this->getchildren_query($parent_id, true, true , true);
		
		$all_pics = $this->sum_pics + $this->c_alb_pic($parent_id);
		$all_hits = $this->sum_hits + $this->c_alb_view($parent_id);
		$all_com = $this->sum_com + $this->c_alb_com($parent_id);
		
		$result = array("albums" => $this->sum, "pics" => $this->sum_pics, "all_pics" => $all_pics, "hits" => $this->sum_hits, "all_hits" => $all_hits, "comments" => $this->sum_com, "all_comments" => $all_com);
		
		return $result;
	}
	
	function getchildren_query($parent_id, $albs = false, $hits = false, $com = false) {
	
		global $wpdb;
		
		$rel_req = "SELECT {$wpdb->aniga_rel}.id, {$wpdb->aniga_rel}.parent_id FROM $wpdb->aniga_rel, $wpdb->aniga_alb WHERE {$wpdb->aniga_rel}.id = {$wpdb->aniga_alb}.id AND {$wpdb->aniga_rel}.parent_id = $parent_id ".$this->query("AND.alb")." ORDER BY {$wpdb->aniga_alb}.order DESC";
		$rel_ret = $wpdb->get_results($rel_req);
		
		if ($albs) $this->sum = $this->sum + $wpdb->num_rows;
		
		foreach ($rel_ret as $relation) {
	
			$gal_count_req = "SELECT pid, hits FROM $wpdb->aniga_pic WHERE parent_id = $relation->id ".$this->query("AND");
			$gal_count_ret = $wpdb->get_results($gal_count_req);
			
			$this->sum_pics = $this->sum_pics + $wpdb->num_rows;
			
			if ($hits) {
				foreach ($gal_count_ret as $pic) {
					$this->sum_hits = $this->sum_hits + $pic->hits;
				}
			}
			
			if ($com) {
				$this->sum_com = $this->sum_com + $this->c_alb_com($relation->id);
			}
					
			$this->getchildren_query($relation->id, $albs, $hits, $com);
			
		}	
	}
	
	function getchildren_wo_self($parent_id, $counter, $id) {
	
		global $wpdb;
		
		$rel_req = "SELECT {$wpdb->aniga_rel}.id, {$wpdb->aniga_rel}.parent_id, {$wpdb->aniga_alb}.name, {$wpdb->aniga_alb}.desc, {$wpdb->aniga_alb}.level, {$wpdb->aniga_alb}.order  FROM $wpdb->aniga_rel, $wpdb->aniga_alb WHERE {$wpdb->aniga_rel}.id = {$wpdb->aniga_alb}.id AND {$wpdb->aniga_rel}.parent_id = $parent_id ".$this->query("AND.alb")." ORDER BY {$wpdb->aniga_alb}.order DESC";
		$rel_ret = $wpdb->get_results($rel_req);
		
		foreach ($rel_ret as $relation) {
			if ($relation->id != $id) {
				$this->child_array[$relation->id] = array('name' => $relation->name, 'lvl' => $relation->level, 'sep' => $counter, 'desc' => $relation->desc, 'order' => $relation->order, 'parent_id' => $relation->parent_id);
				$new_counter = '--'.$counter;
				$this->getchildren_wo_self($relation->id,$new_counter, $id);
			}
		}	
		
		//return $gal_cat_ret;
	}
	
	function getparentalbum($id) {
	
		global $wpdb;
		$rel = $wpdb->get_row("SELECT parent_id FROM $wpdb->aniga_rel WHERE id=$id");
		return $rel->parent_id;
		
	}
		
	function getalbum($id) {
		//return specific album-information from db
		
		global $wpdb;
		$album = $wpdb->get_row("SELECT * FROM $wpdb->aniga_alb WHERE id = $id");
		return $album;
	}
	
	function getfirstpic($alb_id, $order, $sort) {
		// return specific picture from db
		
		global $wpdb;
		$picture = $wpdb->get_row("SELECT * FROM $wpdb->aniga_pic WHERE parent_id=$alb_id ".$this->query("AND")." ORDER BY $order $sort LIMIT 1");
		return $picture;
	}
	
	function getpic($pid) {
		 // return specific picture from db
		 
		global $wpdb;
		$picture = $wpdb->get_row("SELECT * FROM $wpdb->aniga_pic WHERE pid=$pid");
		return $picture;
	}
	
	function getthumbs($alb_id, $page_start, $page_end) {
		// return thumbnails from db
		
		global $wpdb;
		$gal_thumbs_req = "SELECT pid, path, filename FROM $wpdb->aniga_pic WHERE parent_id=$alb_id ".$this->query("AND")." ORDER BY filename LIMIT $page_start,$page_end";
		$gal_thumbs = $wpdb->get_results($gal_thumbs_req);
		return $gal_thumbs;
	}
	
	function getpics($alb_id, $order, $sort) {
		// return pictures for an album from db
		
		global $wpdb;
		$gal_nav_req = "SELECT * FROM $wpdb->aniga_pic WHERE parent_id=$alb_id ".$this->query("AND")." ORDER BY $order $sort";
		$gal_nav_ret = $wpdb->get_results($gal_nav_req);
		return $gal_nav_ret;
	}
	
	function getcurrentpage($current_pic) {
		// return current page acc. to cookie
		
		if (empty($_COOKIE["gal_page" . COOKIEHASH])) $pt = 5 * get_option('aniga_colums');
		else $pt = $_COOKIE["gal_page" . COOKIEHASH];  
		if (is_int($current_pic/$pt)) $cur_page = $current_pic/$pt;
		else $cur_page = floor($current_pic/$pt) + 1;
		return $cur_page;
	}
	
	function getupdatedpost($id) {
		// return updated post from db
		
		global $wpdb;
		$upd_post = $wpdb->get_row("SELECT post_title, post_content, comment_count, comment_status FROM $wpdb->posts WHERE ID = $id");
		return $upd_post;
	}
	
	function getpostparent($id) {
		// return parent posts for an ID from db
		
		global $wpdb;
		$check_cat_req = "SELECT * FROM $wpdb->posts WHERE post_parent = $id";
		$check_cat_ret = $wpdb->get_results($check_cat_req);
		$count = $wpdb->num_rows;
		return $count;
	}


//  #### COUNT FUNCTIONS ###  --------------------------------------------
	
	function c_alb_view($alb_id) {
		// return Picture Views per one Album
		
		global $wpdb;
		$sum_view = 0;
		$gal_count_req = "SELECT hits FROM $wpdb->aniga_pic WHERE parent_id=$alb_id ".$this->query("AND");
		$gal_count_ret = $wpdb->get_results($gal_count_req);
		if (!empty($gal_count_ret)){
			foreach ($gal_count_ret as $gal_count) {
			$sum_view = $sum_view + $gal_count->hits;
			}
		}
		return $sum_view;
	}
						
	function c_alb_pic($id) {
		// return Pictures per one Album
		
		global $wpdb;
		$gal_count_req = "SELECT pid FROM $wpdb->aniga_pic WHERE parent_id=$id ".$this->query("AND");
		$gal_count_ret = $wpdb->get_results($gal_count_req);
		return $wpdb->num_rows;	
	}
											
	function c_alb_com($alb_id) {
		// return Comments per one Album
		
		global $wpdb;
		$gal_count_req = "SELECT ".$wpdb->aniga_pic.".pid FROM ".$wpdb->comments.", ".$wpdb->aniga_pic." WHERE ".$wpdb->aniga_pic.".parent_id = $alb_id AND ".$wpdb->aniga_pic.".pid = ".$wpdb->comments.".comment_pid_ID ".$this->query("AND.pic")." AND ".$wpdb->comments.".comment_approved='1'";
		$gal_count_ret = $wpdb->get_results($gal_count_req);
		return $wpdb->num_rows;	
	}
						
	function c_pic_com($pic_id) {
		// return Comments Count for specific Picture
		
		global $wpdb;
		$gal_give_req = "SELECT comment_ID FROM $wpdb->comments WHERE comment_pid_ID=$pic_id AND comment_approved='1'";
		$gal_give_ret = $wpdb->get_results($gal_give_req);
		return $wpdb->num_rows;
	}


//  #### MISC FUNCTIONS ###  ---------------------------------------------
	
	function updatehits($hits, $pid) {
		// update hits for a picture
		
		global $wpdb;
		$hits = $hits + 1;
		$update_hits = $wpdb->query("UPDATE $wpdb->aniga_pic SET hits = '$hits' WHERE pid = $pid");
		return $hits;
	}
	
	function nav_thumb($id) {
		// print navigation for thumbnail and picture page
		
		global $wpdb;
		echo '<div class="gallery_nav_thumb">';
 		if ($base_id = get_option('aniga_base-id')) {
			$gal_title = $wpdb->get_row("SELECT post_title FROM $wpdb->posts WHERE ID=$base_id"); ?>	
			
			&raquo; <a href="<?php echo get_permalink($base_id); ?>"><?php echo $gal_title->post_title; ?></a> 
			
            <?php foreach ($this->parent_array as $i => $val) { 
				if ($val['id'] != $id) {?>
			
            	/ <a href="<?php echo get_permalink($val['id']); ?>"><?php echo $val['name']; ?></a>
			
			<?php } }
		}
		else echo "&raquo;Navigation not ready - check ANIga Options<";
		echo "</div>";
	}
	
	function top($numb) {
		// print most viewed pictures

		global $wpdb;
		$i = 1;
		
		$gal_req = "SELECT pid, path, filename, hits, parent_id FROM $wpdb->aniga_pic ".$this->query("WHERE")." ORDER BY hits DESC LIMIT $numb";
		$gal_ret = $wpdb->get_results($gal_req);
		
		include("templates/misc.top_pics.inc.php");
	}

}

?>