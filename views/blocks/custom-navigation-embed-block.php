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
	$channel_headers = Muut_Forum_Channel_Utility::getForumChannelHeaders();
	do_action( 'muut_forum_custom_navigation_before_headers', $channel_headers );
	if ( !empty( $channel_headers ) ) {
		foreach( $channel_headers as $header_id => $header_array ) { ?>
			<div class="m-h3"><?php echo Muut_Forum_Channel_Utility::getChannelHeaderTitle( $header_id ); ?></div>
			<?php foreach ( $header_array as $channel_post ) {
				$class = '';
				if ( !Muut_Forum_Channel_Utility::isAllpostsChannel( $channel_post->ID ) ) {
					$class .= 'unlisted ';
				}
				?>
				<a href="#!/<?php echo Muut_Forum_Channel_Utility::getRemotePath( $channel_post->ID ); ?>" class="<?php echo $class; ?>"><?php echo $channel_post->post_title; ?></a>
			<?php }
		}
	}
	do_action( 'muut_forum_custom_navigation_after_headers', $channel_headers );
	?>
</div>