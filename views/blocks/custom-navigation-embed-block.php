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

if ( !isset( $path ) ) {
	$path = Muut_Forum_Page_Utility::getRemoteForumPath( get_the_ID() );
}
?>
<!-- Muut placeholder tag -->
<div class="muut">

	<!-- Muut API -->
	<a class="muut-url" href="/i/<?php echo muut()->getRemoteForumName() . '/' . $path; ?>"><?php echo get_the_title(); ?></a>


	<!-- Custom HTML -->
	<?php
	$category_headers = Muut_Forum_Category_Utility::getForumCategoryHeaders();
	foreach( $category_headers as $header_id => $header_array ) { ?>
		<div class="m-h3"><?php echo Muut_Forum_Category_Utility::getCategoryHeaderTitle( $header_id ); ?></div>
		<?php foreach ( $header_array as $category_post ) {
			$class = '';
			if ( !Muut_Forum_Category_Utility::isAllpostsCategory( $category_post->ID ) ) {
				$class .= 'non-category ';
			}
			?>
			<a href="#!/<?php echo $category_post->post_name; ?>" class="<?php echo $class; ?>"><?php echo $category_post->post_title; ?></a>
		<?php }
	} ?>
</div>