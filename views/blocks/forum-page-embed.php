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
?>
<!-- Muut placeholder tag -->
<div <?php echo $id_attr; ?> class="<?php echo muut()->getWrapperCssClass(); ?>" <?php echo $settings; ?>  data-url="<?php echo muut()->getContentPathPrefix(); ?>i/<?php echo muut()->getForumName(); ?>">

	<!-- Muut API -->

	<a class="muut-url" href="<?php echo muut()->getContentPathPrefix(); ?>i/<?php echo muut()->getForumName(); ?>"><?php echo get_the_title(); ?></a>

	<!-- Custom HTML -->
	<?php
	do_action( 'muut_forum_custom_navigation' );
	?>
</div>