<?php
/**
 * The markup for the frontend of the Popular Posts widget
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within the widget() method of the Muut_Widget_Popular_Posts class (which extends WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 *
 */


?>
<div id="<?php echo $this->id; ?>-wrapper" class="muut_widget_wrapper muut_widget_popular_posts_wrapper">
	<ul id="<?php echo $this->id; ?>-content" class="muut_popular_posts">
		<?php
		foreach( $popular_posts as $popular_post ) {
			$meta_comments_text = sprintf( __( '%d comments', 'muut' ), $popular_post->comment_count );
			$meta_likes_text = sprintf( __( '%d likes', 'muut' ), get_post_meta( $popular_post->ID, 'muut_thread_likes', true ) );
		?>
		<li class="muut_popular_post_item" data-wp-post-id="<?php echo $popular_post->ID; ?>">
			<span class="popular-posts-post-title"><a href="<?php echo get_permalink( $popular_post ); ?>"><?php echo $popular_post->post_title; ?></a></span>
			<span class="popular-posts-post-meta">
				<span class="muut_post_comment_count"><?php echo $meta_comments_text; ?></span> | <span class="muut_post_like_count"><?php echo $meta_likes_text; ?></span>
			</span>
		</li>
		<?php
		}
		?>
	</ul>
</div>