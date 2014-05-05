<?php
/**
 * The block is used for the Muut embed markup when custom navigation is being used.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */


if ( !isset( $settings ) ) {
	$settings = '';
}

$id_attr = muut()->getWrapperCssId() ? 'id="' . muut()->getWrapperCssId() . '"' : '';

if ( !isset( $path ) ) {
	$path = Muut_Forum_Page_Utility::getRemoteForumPath( get_the_ID() );
}
?>
<!-- Muut placeholder tag -->
<div <?php echo $id_attr; ?> class="<?php echo muut()->getWrapperCssClass(); ?>" data-url="<?php echo muut()->getContentPathPrefix(); ?>i/<?php echo muut()->getRemoteForumName() . '/' . $path; ?>">

	<!-- Muut API -->
	<?php if ( !muut()->getOption( 'subscription_use_sso' ) ) { ?>
	<a class="muut-url" href="<?php echo muut()->getContentPathPrefix(); ?>i/<?php echo muut()->getRemoteForumName() . '/' . $path; ?>"><?php echo get_the_title(); ?></a>
	<?php } ?>

	<!-- Custom HTML -->
	<?php
	$category_headers = Muut_Forum_Category_Utility::getForumCategoryHeaders();
	do_action( 'muut_forum_custom_navigation_before_headers', $category_headers );
	if ( !empty( $category_headers ) ) {
		foreach( $category_headers as $header_id => $header_array ) { ?>
			<div class="m-h3"><?php echo Muut_Forum_Category_Utility::getCategoryHeaderTitle( $header_id ); ?></div>
			<?php foreach ( $header_array as $category_post ) {
				$class = '';
				if ( !Muut_Forum_Category_Utility::isAllpostsCategory( $category_post->ID ) ) {
					$class .= 'non-category ';
				}
				?>
				<a href="#!/<?php echo Muut_Forum_Category_Utility::getRemotePath( $category_post->ID ); ?>" class="<?php echo $class; ?>"><?php echo $category_post->post_title; ?></a>
			<?php }
		}
	}
	do_action( 'muut_forum_custom_navigation_after_headers', $category_headers );
	?>
</div>