<?php
/**
 * The markup for the Muut page editor metabox.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

$is_forum = Muut_Forum_Page_Utility::isForumPage( get_the_ID() );

$forum_show_class = '';
if ( $is_forum != '1' ) {
	$forum_show_class = 'hidden';
}

$remote_path = '/' . rawurldecode( Muut_Forum_Page_Utility::getRemoteForumPath( get_the_ID(), true ) );

$tabs = Muut_Admin_Post_Editor::instance()->getMetaBoxTabs();
?>
<div id="muut_metabox_tabs">
	<div class="wp-tab-bar">
		<ul id="category-tabs" class="category-tabs">
			<?php
			$first_tab = true;

			foreach( $tabs as $slug => $tab ) {
				$class = '';
				if ( $first_tab === true ) {
					$class = 'tabs';
					$first_tab = $slug;
				}
				echo '<li class="' . $class . '"><a href="#' . $tab['name'] . '">' . $tab['label'] . '</a></li>';
			}
			?>
		</ul>
		<?php
			foreach( $tabs as $slug => $tab ) {
				$class = 'wp-tab-panel muut-tab-panel';
				if ( $slug != $first_tab ) {
					$class .= " hidden";
				}
				echo '<div id="' . $tab['name'] . '" class="' . $class . '">';
				include ( $tab['template_location'] );
				echo '</div>';
			}
		?>
	</div>
</div>