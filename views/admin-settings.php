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
	<h2><?php _e( 'Muut Settings', 'muut' ); ?></h2>
	<form method="post">
	<input type="hidden" name="muut_settings_save" value="true" />
	<?php wp_nonce_field( 'muut_settings_save', 'muut_settings_nonce' ); ?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="muut_remote_forum_name"><?php _e( 'Remote Forum Name', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[remote_forum_name]" type="text" id="muut_remote_forum_name" value="<?php echo muut()->getOption( 'remote_forum_name', '' ); ?>" />
				</td>
			</tr>
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
			</tbody>
		</table>
		<h3 class="title"><?php _e( 'Post Commenting', 'muut' ); ?></h3>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="muut_replace_comments"><?php _e( 'Use Muut for commenting', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[replace_comments]" type="checkbox" id="muut_replace_comments" value="1" <?php checked( '1', muut()->getOption( 'replace_comments', '0' ) ); ?> />
				</td>
			</tr>
			<tr data-muut_requires="muut_replace_comments" data-muut_require_func="is(':checked')">
				<th scope="row">
					<label for="muut_override_all_comments"><?php _e( 'Override existing comments', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[override_all_comments]" type="checkbox" id="muut_override_all_comments" value="1" <?php checked( '1', muut()->getOption( 'override_all_comments', '0' ) ); ?> />
				</td>
			</tr>
			</tbody>
		</table>
		<h3 class="title"><?php _e( 'Forum Page Defaults', 'muut' ); ?></h3>
		<p><?php _e( 'These can be changed for individual pages when you create/edit them.', 'muut' ); ?></p>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="muut_is_threaded_default"><?php _e( 'Threaded Posts', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[is_threaded_default]" type="checkbox" id="muut_is_threaded_default" value="1" <?php checked( '1', $forum_page_defaults['is_threaded'] ); ?> />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="muut_show_online_default"><?php _e( 'Show Online Users', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[show_online_default]" type="checkbox" id="muut_show_online_default" value="1" <?php checked( '1', $forum_page_defaults['show_online'] ); ?> />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="muut_allow_uploads_default"><?php _e( 'Allow Image Uploads', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[allow_uploads_default]" type="checkbox" id="muut_allow_uploads_default" value="1" <?php checked( '1', $forum_page_defaults['allow_uploads'] ); ?> />
				</td>
			</tr>
			</tbody>
		</table>
		<h3 class="title"><?php _e( 'Single Sign-On', 'muut' ); ?></h3>
		<p><?php printf( __( 'With Single Sign-On, your users will not have to register separately with Muut but will automatically be able to use their WordPress users. Visit the  %sforum upgrades%s page to purchase the developer package.', 'muut' ), '<a href="https://muut.com/pricing/">', '</a>' ); ?></p>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="muut_subscription_api_key"><?php _e( 'API Key', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[subscription_api_key]" type="text" id="muut_subscription_api_key" value="<?php echo muut()->getOption( 'subscription_api_key', '' ); ?>" />
				</td>
			</tr>
			<tr data-muut_requires="muut_subscription_api_key" data-muut_require_func="val()">
				<th scope="row">
					<label for="muut_subscription_secret_key"><?php _e( 'Secret Key', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[subscription_secret_key]" type="text" id="muut_subscription_secret_key" value="<?php echo muut()->getOption( 'subscription_secret_key', '' ); ?>" />
				</td>
			</tr>
			<tr data-muut_requires="muut_subscription_api_key" data-muut_require_func="val()">
				<th scope="row">
					<label for="muut_subscription_use_sso"><?php _e( 'Use SSO', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[subscription_use_sso]" type="checkbox" id="muut_subscription_use_sso" value="1" <?php checked( '1', muut()->getOption( 'subscription_use_sso', '0' ) ); ?> />
				</td>
			</tr>
			</tbody>
		</table>
		<h3 class="title"><?php _e( 'Advanced Settings', 'muut' ); ?></h3>
		<p><?php printf( __( 'These settings should not have to be altered.', 'muut' ) ); ?></p>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="muut_disable_proxy_rewrites"><?php _e( 'Disable Proxy Rewrites', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[disable_proxy_rewrites]" type="checkbox" id="muut_disable_proxy_rewrites" value="1" <?php checked( '1', muut()->getOption( 'disable_proxy_rewrites', '0' ) ); ?> />
				</td>
			</tr>
			</tbody>
		</table>
		<h3 class="title"><?php __( 'Forum Page Defaults', 'muut' ); ?></h3>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
</div>