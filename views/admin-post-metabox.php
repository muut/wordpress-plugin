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
$last_active_tab = get_post_meta( get_the_ID(), 'muut_last_active_tab', true );

$post_type_object = get_post_type_object( get_post_type() );

?>
<div id="muut_metabox_tabs">
	<div class="wp-tab-bar">
		<ul id="muut_metabox_tabs_list" class="category-tabs">
			<?php
			$first_tab = true;
			$active_tab = $last_active_tab;
			foreach( $tabs as $slug => $tab ) {
				$class = '';
				$active_value = '0';
				if ( !Muut_Admin_Post_Editor::instance()->isTabEnabled( $slug, $post->ID ) ) {
					$class .= 'disabled ';
				} else {
					$class .= 'enabled ';
					$active_tab = $tab['name'];
					$active_value = '1';
				}
				if ( ( $last_active_tab && $tab['name'] == $last_active_tab ) || ( !$last_active_tab && $first_tab === true ) ) {
					$class .= ' tabs';
					$first_tab = $slug;
					if ( $last_active_tab === '' ) {
						$active_value = '1';
						$active_tab = $tab['name'];
					}
				}
				echo '<li class="' . $class . '" id="muut_tab-' . $tab['name'] . '" data-muut_tab="' . $tab['name'] . '"><a href="#muut_tab_content-' . $tab['name'] . '" class="muut_metabox_tab">' . $tab['label'] . '</a><input type="hidden" class="muut_tab_last_active" name="muut_tab_last_active_' . $tab['name'] . '" value="' . $active_value . '" /></li>';
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
				echo '<div id="muut_tab_content-' . $tab['name'] . '" class="' . $class . '">'; ?>
				<?php
				include ( $tab['template_location'] );
				echo '</div>';
			}
		?>
	</div>
</div>
<div id="muut_tabs_disable_dialog" class="hidden">
	<?php printf( __( 'Enabling this will disable other types of Muut embeds on this %s.', 'muut' ), $post_type_object->labels->singular_name ); ?>
</div>