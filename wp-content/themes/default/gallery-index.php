<?php
/*
Template Name: gallery-index
*/

// this is an adjusted copy from the default template file page.php
// if you want to adjust this template file for your own layout, you need to paste the marked parts 
// into a copy of your page.php template file between your <div class="post" ..> .. </div> tags and 
// save it as gallery-index.php in your theme folder.
// dont forget the Template-Name!

?>

<?php get_header(); ?>

	<div id="content" class="widecolumn">

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		

<div class="post" id="post-<?php the_ID(); ?>">
	
<?php
// --------------------------------------------------------- ANIga ---- //
		// load gallery functions
			aniga_start_gallery();
		// load new index classes
			$aniga = new aniga_db();
			$aniga_do = new aniga_index();
		// print index heading
			$aniga_do->heading();
		// print index loop
			$aniga_do->loop();
		// print index meta information
			$aniga_do->meta();
		// print most x viewed pictures
			$aniga->top(3);
// --------------------------------------------------------- ANIga ---- //
?>

</div>

	<?php endwhile; else: ?>

		<p>Sorry, no posts matched your criteria.</p>

<?php endif; ?>

	</div>

<?php get_footer(); ?>