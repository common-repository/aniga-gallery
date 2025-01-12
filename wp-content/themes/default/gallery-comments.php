<?php 
// this is an adjusted copy from the default template file comments.php
// if you want to adjust this template file for your own layout, you need to make a copy of your comments.php, insert the marked changes at the apropriate points and save it as "gallery-comments.php" in your theme folder.



// Do not delete these lines

// --------------------------------------------------------- ANIga ---- //
//	OLD:
//	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
//	NEW:	
	if ('gallery-comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
// --------------------------------------------------------- ANIga ---- //
		die ('Please do not load this page directly. Thanks!');

        if (!empty($post->post_password)) { // if there's a password
            if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
				?>

				<p class="nocomments">This post is password protected. Enter the password to view comments.<p>

				<?php
				return;
            }
        }

		/* This variable is for alternating comment background */
		$oddcomment = 'alt';
		
// --------------------------------------------------------- ANIga ---- //
//	NEW:		
		global $aniga_do, $aniga, $aniga_lang;
		$picture = $aniga_do->pic;
// --------------------------------------------------------- ANIga ---- //

?>

<!-- You can start editing here. -->

<?php if ($comments) : ?>
	<h3 id="comments">
<?php
// --------------------------------------------------------- ANIga ---- //
//	OLD:
/*	<?php comments_number('No Responses', 'One Response', '% Responses' );?> to &#8220;<?php the_title(); ?>&#8221;
*/
//	NEW:
	$com_count = $aniga->c_pic_com($picture->pid);
	if ($com_count == 1 ) echo 'One Response for this Picture';
	else if ($com_count > 1) echo $com_count . ' Responses for this Picture';
// --------------------------------------------------------- ANIga ---- //
?>
	</h3>

	<ol class="commentlist">

	<?php foreach ($comments as $comment) : ?>

		<li class="<?php echo $oddcomment; ?>" id="comment-<?php comment_ID() ?>">
			<cite><?php comment_author_link() ?></cite> Says:
			<?php if ($comment->comment_approved == '0') : ?>
			<em>Your comment is awaiting moderation.</em>
			<?php endif; ?>
			<br />

			<small class="commentmetadata"><a href="#comment-<?php comment_ID() ?>" title=""><?php comment_date('F jS, Y') ?> at <?php comment_time() ?></a> <?php edit_comment_link('e','',''); ?></small>

			<?php comment_text() ?>

		</li>

	<?php /* Changes every other comment to a different class */
		if ('alt' == $oddcomment) $oddcomment = '';
		else $oddcomment = 'alt';
	?>

	<?php endforeach; /* end for each comment */ ?>

	</ol>

 <?php else : // this is displayed if there are no comments so far ?>

  <?php if ('open' == $post->comment_status) : ?> 
		<!-- If comments are open, but there are no comments. -->

	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->
		<p class="nocomments">Comments are closed.</p>

	<?php endif; ?>
<?php endif; ?>


<?php if ('open' == $post->comment_status) : ?>

<h3 id="respond">Leave a Reply</h3>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>

<?php
// --------------------------------------------------------- ANIga ---- //
//	OLD:
/*	<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>">logged in</a> to post a comment.</p>
*/
//	NEW:
?>
<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo aniga_get_permalink($id, $picture->pid, 'pid'); ?>">logged in</a> to post a comment.</p>
<?php	
// --------------------------------------------------------- ANIga ---- //
?>
<?php else : ?>

<?php
// --------------------------------------------------------- ANIga ---- //
//	OLD:
/*	<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
*/
//	NEW:
?>
<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php?pid=<?php echo $picture->pid; ?>" method="post" id="commentform">
<?php
// --------------------------------------------------------- ANIga ---- //
?>


<?php if ( $user_ID ) : ?>

<p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="Log out of this account">Logout &raquo;</a></p>

<?php else : ?>

<p><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" />
<label for="author"><small>Name <?php if ($req) echo "(required)"; ?></small></label></p>

<p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" />
<label for="email"><small>Mail (will not be published) <?php if ($req) echo "(required)"; ?></small></label></p>

<p><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" />
<label for="url"><small>Website</small></label></p>

<?php endif; ?>

<!--<p><small><strong>XHTML:</strong> You can use these tags: <?php echo allowed_tags(); ?></small></p>-->

<p><textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea></p>

<?php
// --------------------------------------------------------- ANIga ---- //
//	NEW:
?>
<input type="hidden" name="comment_pid_ID" value="<?php echo $picture->pid; ?>" />
<input type="hidden" name="redirect_to" value="<?php echo aniga_get_permalink($id, $picture->pid, 'pid'); ?>" />
<?php	
// --------------------------------------------------------- ANIga ---- //
?>

<p><input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
</p>
<?php do_action('comment_form', $post->ID); ?>

</form>

<?php endif; // If registration required and not logged in ?>

<?php endif; // if you delete this the sky will fall on your head ?>
