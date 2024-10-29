	<div id="sidebar">
		<ul>
			<?php 	/* Widgetized sidebar, if you have the plugin installed. */
					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
			<li>
				<?php include (TEMPLATEPATH . '/searchform.php'); ?>
			</li>

			<!-- Author information is disabled per default. Uncomment and fill in your details if you want to use it.
			<li><h2>Author</h2>
			<p>A little something about you, the author. Nothing lengthy, just an overview.</p>
			</li>
			-->

			<?php if ( is_404() || is_category() || is_day() || is_month() ||
						is_year() || is_search() || is_paged() ) {
			?> <li>

			<?php /* If this is a 404 page */ if (is_404()) { ?>
			<?php /* If this is a category archive */ } elseif (is_category()) { ?>
			<p>You are currently browsing the archives for the <?php single_cat_title(''); ?> category.</p>

			<?php /* If this is a yearly archive */ } elseif (is_day()) { ?>
			<p>You are currently browsing the <a href="<?php bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> blog archives
			for the day <?php the_time('l, F jS, Y'); ?>.</p>

			<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
			<p>You are currently browsing the <a href="<?php bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> blog archives
			for <?php the_time('F, Y'); ?>.</p>

			<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
			<p>You are currently browsing the <a href="<?php bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> blog archives
			for the year <?php the_time('Y'); ?>.</p>

			<?php /* If this is a monthly archive */ } elseif (is_search()) { ?>
			<p>You have searched the <a href="<?php echo bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> blog archives
			for <strong>'<?php the_search_query(); ?>'</strong>. If you are unable to find anything in these search results, you can try one of these links.</p>

			<?php /* If this is a monthly archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
			<p>You are currently browsing the <a href="<?php echo bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> blog archives.</p>

			<?php } ?>

			</li> <?php }?>
            
            
<? // #################### ANIga gallery changes ####################### ?>
<? // ################################################################## ?>

			<li><h2>Random Gallery Pictures</h2>
			<?	// this function displays random pictures with a random comment (if there is one)
				// if you dont want to show comments below a picture use this function instead:
				// aniga_show_rand_img_nc($before = '<p align="center">', $after = '</p>', $limit = 3); ?>
				<?php aniga_show_rand_img($before = '<p align="center">', $after = '</p>', $limit = 3); ?>
			</li>
			
			<li><h2>Latest Comments</h2>
			<?	// this function displays recent comments. It is almost the same as the recent_comment plugin (http://mtdewvirus.com/), but if you have a comment on a picture the recent_comment plugin will link to the album instead to the picture.  ?>
				<ul>
					<?php aniga_latest_comments($no_comments = 5, $comment_lenth = 5, $before = '<li>', $after = '</li>', $show_pass_post = false); ?>
				</ul>
			</li>

			<li><h2>Latest Pictures</h2>
			<?	// this function displays the filename of the most recent pictures  ?>
            	<ul>
					<?php aniga_show_last_pic($before = '<li>', $after = '</li>', $limit = 3); ?>
                </ul>
			</li>
			
			<li><h2>Latest Galleries</h2>
			<?	// this function displays the name of the most recent albums ?>
            	<ul>
					<?php aniga_show_last_gal($before = '<li>', $after = '</li>', $limit = 3); ?>
				</ul>
			</li>

			<?	// you should either exclude the photography ID from wp_list_pages or limit the depth to 2, otherwise wp_list_pages will display all albums (which might be a lot..) ?>
            <?php wp_list_pages('title_li=<h2>Pages</h2>&depth=2' ); ?>

<? // ################################################################## ?>

			<li><h2>Archives</h2>
				<ul>
				<?php wp_get_archives('type=monthly'); ?>
				</ul>
			</li>

			<?php wp_list_categories('show_count=1&title_li=<h2>Categories</h2>'); ?>

			<?php /* If this is the frontpage */ if ( is_home() || is_page() ) { ?>
				<?php wp_list_bookmarks(); ?>

				<li><h2>Meta</h2>
				<ul>
					<?php wp_register(); ?>
					<li><?php wp_loginout(); ?></li>
					<li><a href="http://validator.w3.org/check/referer" title="This page validates as XHTML 1.0 Transitional">Valid <abbr title="eXtensible HyperText Markup Language">XHTML</abbr></a></li>
					<li><a href="http://gmpg.org/xfn/"><abbr title="XHTML Friends Network">XFN</abbr></a></li>
					<li><a href="http://wordpress.org/" title="Powered by WordPress, state-of-the-art semantic personal publishing platform.">WordPress</a></li>
					<?php wp_meta(); ?>
				</ul>
				</li>
			<?php } ?>

			<?php endif; ?>
		</ul>
	</div>

