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
			$time_since = time() - $comment['timestamp'];
			if ( $time_since < 60 ) {
				$list_time = 'just now';
			} elseif ( $time_since < ( 60 * 60 ) ) {
				$list_time = floor( $time_since / 60 ) . 'm';
			} elseif ( $time_since < ( 60 * 60 * 24 ) ) {
				$list_time = floor( $time_since / ( 60 * 60 ) ) . 'h';
			} elseif ( $time_since < ( 60 * 60 * 24 * 7 ) ) {
				$list_time = floor( $time_since / ( 60 * 60 * 24 ) ) . 'd';
			} else {
				$list_time = floor( $time_since / ( 60 * 60 * 24 * 7 ) ) . 'w';
			}
			$user = $comment['user'];
			$user_link_path = Muut_Post_Utility::getForumPageId() && Muut_Post_Utility::getForumPageId() != get_the_ID() ? get_permalink( Muut_Post_Utility::getForumPageId() ) . '#!/' . $user->path . '"' : false;
			echo '<li class="muut_recentcomments">';
				muut_get_user_facelink_avatar( substr( $user->path, 1 ), $user->displayname, false, $user_link_path, $user->img, true );
				echo '<span class="recent-comments-post-title"><a href="' . get_permalink( $comment['post_id'] ) . '">' . get_the_title( $comment['post_id'] ) . '</a></span>';
				echo '<div class="muut-post-time-since">' . $list_time . '</div>';
			echo '</li>';
		}
		?>
	</ul>
</div>
