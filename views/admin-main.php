<?php
/**
 * The main page for the new Muut admin section.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
?>
<div class="wrap muut_admin_wrapper">
	<div class="muut-header-logo"></div>
	<h2><?php _e( 'Welcome to Muut!', 'muut' ); ?></h2>
	<p><?php _e( 'Welcome to Muut forums! Whether you\'re setting up your first forum or have just updated to the new version of the plugin, you\'re going to love what we\'ve done to make implementing Muut in your WordPress website.', 'muut' ); ?></p>
	<h3><?php _e( 'Setup', 'muut' ); ?></h3>
	<p><?php printf( __( 'If you have already registered a Muut forum, you can head straight to the %sMuut Settings%s page and enter its information. Otherwise, you can register your own Muut forum right %shere%s.', 'muut' ), '<a href="' . add_query_arg( array( 'page' => 'muut_settings' ), admin_url( 'admin.php' ) ) . '">', '</a>', '<a href="#">', '</a>' ); ?></p>
	<p><?php printf( __( 'Once you have saved your settings, you can go ahead and %screate a new forum page%s. There are a couple ways you can embed Muut forums in your pages:', 'muut' ), '<a href="' . add_query_arg( array( 'post_type' => 'page' ), admin_url( 'post-new.php' ) ) . '">', '</a>' ); ?></p>
	<ul>
		<li><?php printf( __( '%sOverride default WordPress comments:%s This will use Muut commenting instead of the default WordPress comments for all posts and pages on your website.', 'muut' ), '<span class="bullet-title">', '</span>' ); ?></li>
		<li>
			<p><?php printf( __( '%sFull Embed (recommended):%s By making a page your Forum Home, it will embed the full Muut user interface. Of course, it is still fully customizable using the %sCustom Navigation%s settings, along with full control using CSS. We feel very strongly about allowing you to have a truly customizable look and feel, and so we mad sure that is possible by having the markup embedded directly in your website rather than using an ugly iframe.', 'muut' ), '<span class="bullet-title">', '</span>', '<a href="' . add_query_arg( array( 'page' => 'muut_custom_navigation' ), admin_url( 'admin.php' ) ) . '">', '</a>' ); ?></p>
			<p><?php printf( __( 'Additionally, categories you create for custom navigation can be added in the main WordPress %sMenus%s administration page, noted as "Forum Categories."', 'muut' ), '<a href="' . admin_url( 'nav-menus.php' ) . '">', '</a>' ); ?></p>
			<p><?php printf( __( 'If you have a %sdesigner license%s, you also have access to the Muut designer interface which can be accessed on the frontend when you are logged in as the forum administrator.', 'muut' ), '<a href="//' . Muut::MUUTSERVERS . '/pricing/">', '</a>' ); ?></p>
		</li>
		<li><?php printf( __( '%sStandalone Subforum:%s This will allow you to embed a specific subforum (Muut path) on a given page. It won\'t use Muut\'s full interface, but will allow for a single path to exist on its own page. You can do this by making the page a Forum Page, but without selecting the checkbox to make it the Forum Home. Make sure the "Threaded" checkbox is selected!', 'muut' ), '<span class="bullet-title">', '</span>' ); ?></li>
		<li><?php printf( __( '%sStandalone Comments:%s This is very similar to the Standalone Subforum pages, except that the page represents a single thread. Instead of users posting separate topics, all the posts on the page are considered to be a single thread and will be listed in chronological order, not unlike the comment overrides! Just follow the same process as creating a standalone subforum page, but make sure the "Threaded" checkbox is not selected.', 'muut' ), '<span class="bullet-title">', '</span>' ); ?></li>
	</ul>
</div>