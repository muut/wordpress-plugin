<?php
/**
 * The post/page editor tab for commenting.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
global $post;
$tab;

$post_type_tabs = Muut_Admin_Post_Editor::instance()->getMetaBoxTabsForCurrentPostType();
unset( $post_type_tabs[$tab['slug']] );

$post_type_object = get_post_type_object( $post->post_type );
$post_type_label = $post_type_object->labels->singular_name;

$meta_name = $tab['meta_name'];
$comments_settings = Muut_Post_Utility::getPostOption( $post->ID, 'commenting_settings' );
$commenting_defaults = muut()->getOption( 'commenting_defaults' );

$type = isset( $comments_settings['type'] ) ? $comments_settings['type'] : $commenting_defaults['type'];
$disable_uploads = isset( $comments_settings['disable_uploads'] ) ? $comments_settings['disable_uploads'] : $commenting_defaults['disable_uploads'];
?>
<p>
	<span class="checkbox_row"><input type="checkbox" name="<?php echo $tab['meta_name']; ?>[enabled-tab]" class="muut_enable_<?php echo $tab['name']; ?>" id="muut_enable_tab-<?php echo $tab['name']; ?>" <?php checked( true, Muut_Post_Utility::isMuutCommentingPost( $post->ID ) ); ?> value="1" /><label for="muut_enable_tab-<?php echo $tab['name']; ?>"><?php echo $tab['enable_text']; ?></label></span>
</p>
<div class="enabled_tab_wrapper">
	<p>
		<span class="muut_metabox_radio"><input type="radio" name="<?php echo $meta_name; ?>[type]" id="muut_comments_type_flat" value="flat" <?php checked( $type, 'flat' ); ?> /><label for="muut_comments_type_flat"><?php _e( 'Flat', 'muut' ); ?></label></span>
		<span class="muut_metabox_radio"><input type="radio" name="<?php echo $meta_name; ?>[type]" id="muut_comments_type_threaded" value="threaded" class="muut_metabox_radio" <?php checked( $type, 'threaded' ); ?> /><label for="muut_comments_type_threaded"><?php _e( 'Threaded', 'muut' ); ?></label></span>
	</p>
	<p>
		<span class="checkbox_row"><input type="checkbox" name="<?php echo $meta_name; ?>[disable_uploads]" id="muut_comments_disable_uploads" value="1" <?php checked( $disable_uploads, '1' ); ?> /><label for="muut_comments_disable_uploads"><?php _e( 'Disable image uploads', 'muut' ); ?></label></span>
	</p>
</div>
<div class="disabled_tab_wrapper">
	<?php do_action( 'muut_disabled_tab_content', $tab['name'] ); ?>
</div>