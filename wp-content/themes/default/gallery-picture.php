<?php
/*
Template Name: gallery-picture
*/


// this is an adjusted copy from the default template file page.php
// if you want to adjust this template file for your own layout, you need to paste the marked parts
// into a copy of your page.php template file between your <div class="post" ..> .. </div> tags and 
// save it as gallery-picture.php in your theme folder.
// dont forget the Template-Name!
?>

<?php
// --------------------------------------------------------- ANIga ---- //
		// important: this has to stay before the get_header() tag!
		// load gallery functions
			aniga_start_gallery();
			$aniga = new aniga_db();
		// load new picture classes
			$aniga_do = new aniga_picture();
// --------------------------------------------------------- ANIga ---- //
?>

<?php get_header(); ?>

	<div id="content" class="widecolumn">
				
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	

<div class="post">
	
<?php
// --------------------------------------------------------- ANIga ---- //
		// print slideshow, thumbnails or picture
			$aniga_do->show($id);
// --------------------------------------------------------- ANIga ---- //
?>

</div> <?php //end post ?>

	<?php endwhile; else: ?>
	
		<p>Sorry, no posts matched your criteria.</p>
	
<?php endif; ?>
	
	</div> <?php // end content ?>

<?php get_footer(); ?>
