<?php
/**
 * The markup for the frontend of the Latest Comments widget
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within the widget() method of the Muut_Widget_Latest_Comments class (which extends WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 */

?>
<div id="muut-widget-latest-comments-wrapper" class="muut_widget_wrapper muut_widget_latest_comments_wrapper">
	<ul id="muut-recentcomments">
		<?php
		foreach ( $latest_comments_data as $comment ) {
			if ( is_string( $comment['user'] ) ) {
				$user_obj->displayname = $comment['user'];
				$user_obj->img = '';
				$user_obj->path = $comment['user'];
			} else {
				$user_obj = $comment['user'];
			}
 			echo $this->getRowMarkup( $comment['post_id'], $comment['timestamp'], $comment['user'] );
		}
		?>
	</ul>
</div>
