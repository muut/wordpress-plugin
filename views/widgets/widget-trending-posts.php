<?php
/**
 * The markup for the frontend of the Trending Posts widget
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within the widget() method of the Muut_Widget_Trending_Posts class (which extends WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 *
 */


?>
<div id="<?php echo $this->id; ?>-wrapper" class="muut_widget_wrapper muut_widget_trending_posts_wrapper">
	<ul id="<?php echo $this->id; ?>-content" class="muut_trending_posts">
		<?php
		foreach( $trending_posts as $trending_post ) {
			$comment_count = $trending_post->comment_count;
			$like_count = get_post_meta( $trending_post->ID, 'muut_thread_likes', true );
			$like_count = $like_count > 0 ? $like_count : '0';
			$muut_path = get_post_meta( $trending_post->ID, 'muut_path', true );
		?>
		<li class="muut_trending_post_item" data-wp-post-id="<?php echo $trending_post->ID; ?>" data-muut-post-path="<?php echo $muut_path; ?>">
			<span class="trending-posts-post-title"><a href="<?php echo get_permalink( $trending_post ); ?>"><?php echo $trending_post->post_title; ?></a></span>
			<span class="trending-posts-post-meta">
				<span class="muut_post_comment_count"><?php echo $comment_count; ?></span> <?php _e( 'comments', 'muut' ); ?> | <span class="muut_post_like_count"><?php echo $like_count; ?></span> <?php _e( 'likes', 'muut' ); ?>
			</span>
		</li>
		<?php
		}
		?>
	</ul>
</div>