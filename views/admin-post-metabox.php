<?php
/**
 * The markup for the Muut page editor metabox.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
global $post;

$is_forum = Muut_Post_Utility::isMuutPost( $post->ID );

$forum_show_class = '';
if ( $is_forum != '1' ) {
	$forum_show_class = 'hidden';
}

$remote_path = '/' . rawurldecode( Muut_Post_Utility::getChannelRemotePath( get_the_ID(), true ) );

$tabs = Muut_Admin_Post_Editor::instance()->getMetaBoxTabsForCurrentPostType();
$last_open_tab = get_post_meta( get_the_ID(), 'muut_last_open_tab', true );

?>
<div id="muut_metabox_tabs">
	<div class="wp-tab-bar">
		<ul id="muut_metabox_tabs_list" class="category-tabs">
			<?php
			$first_tab = true;
			$active_tab = $last_open_tab;
			foreach( $tabs as $slug => $tab ) {
				$class = '';
				$active_value = '0';
				if ( !Muut_Admin_Post_Editor::instance()->isTabEnabled( $slug, $post->ID ) ) {
					$class .= 'disabled ';
				} else {
					$class .= 'enabled ';
				}
				if ( ( $last_open_tab && $tab['name'] == $last_open_tab ) || ( !$last_open_tab && $first_tab === true ) ) {
					$class .= ' tabs';
					$first_tab = $slug;
					$active_value = '1';
					$active_tab = $tab['name'];
				}
				echo '<li class="' . $class . '"><a href="#' . $tab['name'] . '">' . $tab['label'] . '</a><input type="hidden" class="muut_tab_last_open" name="muut_last_open_' . $tab['name'] . '" value="' . $active_value . '" /></li>';
			}
			?>
		</ul>
		<?php
			foreach( $tabs as $slug => $tab ) {
				$class = 'wp-tab-panel muut-tab-panel ';
				if ( $tab['name'] != $active_tab ) {
					$class .= 'hidden ';
				}
				if ( !Muut_Admin_Post_Editor::instance()->isTabEnabled( $slug, $post->ID ) ) {
					$class .= 'disabled ';
				} else {
					$class .= 'enabled ';
				}
				echo '<div id="' . $tab['name'] . '" class="' . $class . '">';
				include ( $tab['template_location'] );
				echo '</div>';
			}
		?>
	</div>
</div>