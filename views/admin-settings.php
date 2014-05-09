<?php
/**
 * The Muut settings page.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
$languages = muut()->getLanguages();
$current_language = muut()->getOption( 'language', 'en' );

$forum_page_defaults = muut()->getOption( 'forum_page_defaults' );
?>

<div class="wrap">
	<h2><?php _e( 'Muut', 'muut' ); ?></h2>
	<form method="post">
		<input type="hidden" name="muut_settings_save" value="true" />
		<?php wp_nonce_field( 'muut_settings_save', 'muut_settings_nonce' ); ?>
		<h3><?php _e( 'Forums and commenting re-imagined.', 'muut' ); ?></h3>
<?php if ( !muut()->getForumName() ): ?>
		<p><?php printf( __( 'Please enter the name of your Muut forum. If you don\'t have one, please %ssetup now%s!', 'muut' ), '<a href="https://muut.com/setup/" target="_blank">', '</a>' ); ?></p>
		<p><?php _e( 'You can have any number of forums, users, or commenting pages. No traffic limits: 10, 1,000, or 100,000,000 loads a day, for free.', 'muut' ); ?></p>
<?php endif; ?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="muut_forum_name"><?php _e( 'Forum Name', 'muut' ); ?></label>
				</th>
				<td>
					<?php echo trailingslashit( Muut::MUUTSERVERS ); ?><input name="setting[forum_name]" type="text" id="muut_forum_name" value="<?php echo muut()->getForumName(); ?>" />
				</td>
			</tr>
<?php if ( !muut()->getForumName() ): ?>
			</tbody>
		</table>
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Continue', 'muut' ); ?>">
	</p>
<?php else: ?>
			<tr>
				<th scope="row">
					<label for="moot_language"><?php _e( 'Language', 'muut' ); ?></label>
				</th>
				<td>
					<select name="setting[language]" id="moot_language">
						<?php
						foreach ( $languages as $abbr => $text ) {
							echo '<option value="' . $abbr . '"' . selected( $current_language, $abbr, false ) . '>' . $languages[$abbr] . '</option>';
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input name="setting[enable_proxy_rewrites]" type="checkbox" id="muut_enable_proxy_rewrites" value="1" <?php checked( '1', muut()->getOption( 'enable_proxy_rewrites', '1' ) ); ?> />
					<label for="muut_enable_proxy_rewrites"><?php printf( __( 'Allow search engines to crawl discussions at %s', 'muut' ), '<a href="' . get_site_url() . '">' . str_replace( array( 'http://', 'https://', ), '', get_site_url() ) . '</a>.' ); ?></label>
				</td>
			</tr>
<?php if ( Muut_Forum_Page_Utility::getForumPageId() ):
	$forum_page_id = Muut_Forum_Page_Utility::getForumPageId();
?>
			<tr>
				<td colspan="2">
					<p class="description"><?php printf( __( 'Current forum page is %s', 'muut' ), '<a href="' . get_edit_post_link( $forum_page_id ) . '">' . get_the_title( $forum_page_id ) . '</a>.' ); ?></p>
				</td>
			</tr>
	<?php endif; ?>
			</tbody>
		</table>
		<h3 class="title"><?php _e( 'Single Sign-on', 'muut' ); ?></h3>
		<?php $sso_field_class = muut()->getOption( 'subscription_use_sso' ) ? '' : 'hidden'; ?>
		<table class="form-table">
			<tbody>
			<tr>
				<td colspan="2">
					<input name="setting[subscription_use_sso]" type="checkbox" id="muut_subscription_use_sso" value="1" <?php checked( '1', muut()->getOption( 'subscription_use_sso', '0' ) ); ?> />
					<label for="muut_subscription_use_sso"><?php _e( 'Enabled', 'muut' ); ?></label>
				</td>
			</tr>
			<tr class="<?php echo $sso_field_class; ?>" data-muut_requires="muut_subscription_use_sso" data-muut_require_func="is(':checked()')">
				<th scope="row">
					<label for="muut_subscription_api_key"><?php _e( 'API Key', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[subscription_api_key]" type="text" id="muut_subscription_api_key" value="<?php echo muut()->getOption( 'subscription_api_key', '' ); ?>" />
				</td>
			</tr>
			<tr class="<?php echo $sso_field_class; ?>" data-muut_requires="muut_subscription_use_sso" data-muut_require_func="is(':checked()')">
				<th scope="row">
					<label for="muut_subscription_secret_key"><?php _e( 'Secret Key', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[subscription_secret_key]" type="text" id="muut_subscription_secret_key" value="<?php echo muut()->getOption( 'subscription_secret_key', '' ); ?>" />
				</td>
			</tr>
			</tbody>
		</table>
		<p class="muut_requires_input_block" data-muut_requires="muut_subscription_use_sso" data-muut_require_func="is(':not(:checked)')"><?php _e( 'Upgrade to Muut Developer to use the WordPress authentication system for your forum. No logging in twice—WordPress users automatically become Muut users.', 'muut' ); ?></p>
		<p class="muut_requires_input_block" data-muut_requires="muut_subscription_use_sso" data-muut_require_func="is(':not(:checked)')"><?php printf( __( '%sUpgrade to Developer%s', 'muut' ), '<a href="https://muut.com/pricing/">', '</a>' ); ?></p>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
		<?php endif; ?>
	</form>
</div>