<?php
/**
 * The Muut settings page.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
$languages = muut()->getLanguages();
$current_language = muut()->getOption( 'language', 'en' );
$error_queue = Muut_Admin_Settings::instance()->getErrorQueue();
$error_values = array();
foreach( $error_queue as $error ) {
	$error_values[$error['name']] = $error['new_value'];
}
$current_values = array(
	'forum_name' => muut()->getForumName(),
	'replace_comments' => muut()->getOption( 'replace_comments', '1' ),
	'override_all_comments' => muut()->getOption( 'override_all_comments', '0' ),
	'enable_proxy_rewrites' => muut()->getOption( 'enable_proxy_rewrites', '1' ),
	'use_custom_s3_bucket' => muut()->getOption( 'use_custom_s3_bucket', '0' ),
	'custom_s3_bucket_name' => muut()->getOption( 'custom_s3_bucket_name', '' ),
	'subscription_use_sso' => muut()->getOption( 'subscription_use_sso', '0' ),
	'subscription_api_key' => muut()->getOption( 'subscription_api_key', '' ),
	'subscription_secret_key' => muut()->getOption( 'subscription_secret_key', '' ),

);

$display_values = wp_parse_args( $error_values, $current_values );
?>

<div class="wrap">
	<h2><?php _e( 'Muut', 'muut' ); ?> <span class="admin-subheader-to-right"><?php _e( 'Forums and commenting re-imagined.', 'muut' ); ?></span></h2>
	<form method="post" id="muut_settings_form">
		<input type="hidden" name="muut_settings_save" value="true" />
		<?php wp_nonce_field( 'muut_settings_save', 'muut_settings_nonce' ); ?>
<?php if ( !muut()->getForumName() ): ?>
		<p><?php printf( __( 'Please enter the name of your Muut forum. If you don\'t have one, please %ssetup now%s!', 'muut' ), '<a href="https://muut.com/setup/" target="_blank">', '</a>' ); ?></p>
		<p><?php _e( 'You can have any number of forums, users, or commenting pages. No traffic limits: 10, 1,000, or 100,000,000 loads a day, for free.', 'muut' ); ?></p>
<?php endif; ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="muut_forum_name"><?php _e( 'Forum Name', 'muut' ); ?><span class="right-justify-float"><?php echo trailingslashit( Muut::MUUTSERVERS ); ?></span></label>
					</th>
					<td>
						<input name="setting[forum_name]" type="text" id="muut_forum_name" value="<?php echo $display_values['forum_name']; ?>" />
					</td>
				</tr>
<?php if ( !muut()->getForumName() ): ?>
			</tbody>
		</table>
	<p class="submit">
		<input type="hidden" name="initial_save" value="true" />
		<input type="submit" name="submit_initial" id="submit" class="button button-primary" value="<?php _e( 'Continue', 'muut' ); ?>">
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
			</tbody>
		</table>
		<h3 class="title"><?php _e( 'Commenting', 'muut' ); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th class="th-full" colspan="2">
						<input name="setting[replace_comments]" type="checkbox" id="muut_replace_comments" value="1" <?php checked( '1', $display_values['replace_comments'] ); ?> />
						<label for="muut_replace_comments"><?php _e( 'Use Muut for post commenting', 'muut' ); ?></label>
					</th>
				</tr>
				<tr class="indented" data-muut_requires="muut_replace_comments" data-muut_require_func="is(':checked')">
					<th class="th-full" colspan="2">
						<input name="setting[override_all_comments]" type="checkbox" id="muut_override_all_comments" value="1" <?php checked( '1', $display_values['override_all_comments'] ); ?> />
						<label for="muut_override_all_comments"><?php _e( 'Use Muut commenting on posts with existing comments (data not deleted)', 'muut' ); ?></label>
					</th>
				</tr>
			</tbody>
		</table>
	<h3 class="title"><?php _e( 'Search Engine Optimization (SEO)', 'muut' ); ?></h3>
	<?php $custom_s3_field_class = muut()->getOption( 'enable_proxy_rewrites' ) ? '' : 'hidden'; ?>
	<table class="form-table">
		<tbody>
		<tr>
			<th class="th-full" colspan="2">
				<input name="setting[enable_proxy_rewrites]" type="checkbox" id="muut_enable_proxy_rewrites" value="1" <?php checked( '1', $display_values['enable_proxy_rewrites'] ); ?> />
				<label for="muut_enable_proxy_rewrites"><?php printf( __( 'Allow search engines to crawl discussions at %s', 'muut' ), '<strong>' . str_replace( array( 'http://', 'https://', ), '', get_site_url() ) . '</strong>.' ); ?></label>
			</th>
		</tr>
		<tr class="<?php echo $custom_s3_field_class; ?> indented" data-muut_requires="muut_enable_proxy_rewrites" data-muut_require_func="is(':checked()')">
			<th class="th-full" colspan="2">
				<input name="setting[use_custom_s3_bucket]" type="checkbox" id="muut_use_custom_s3_bucket" value="1" <?php checked( '1', $display_values['use_custom_s3_bucket'] ); ?> />
				<label for="muut_use_custom_s3_bucket"><?php printf( __( 'Serve from your own S3 Bucket (%sRequires Developer Subscription%s)', 'muut' ), '<a target="_blank" href="https://muut.com/pricing/">', '</a>' ); ?></label>
			</th>
		</tr>
		<tr class="<?php echo $custom_s3_field_class; ?> indented" data-muut_requires="muut_use_custom_s3_bucket" data-muut_require_func="is(':checked()')">
			<th scope="row">
				<label for="muut_custom_s3_bucket_name"><?php _e( 'S3 Bucket Name', 'muut' ); ?></label>
			</th>
			<td>
				<input name="setting[custom_s3_bucket_name]" type="text" id="muut_custom_s3_bucket_name" placeholder="s3.bucket.name" value="<?php echo $display_values['custom_s3_bucket_name']; ?>" />
			</td>
		</tr>
		<?php $show_s3_bucket_input = muut()->getOption( 'use_custom_s3_bucket' ) ? '' : 'hidden'; ?>
		<tr class="<?php echo $show_s3_bucket_input; ?> indented show_slow" id="muut_s3_requirement_paragraph" data-muut_requires="muut_use_custom_s3_bucket" data-muut_require_func="is(':checked()')">
			<td colspan="2">
				<span class="description"><?php _e( 'The bucket name you enter must be the same S3 bucket registered for the forum in the Muut settings', 'muut' ); ?></span>
			</td>
		</tr>
		</tbody>
	</table>
	<h3 class="title"><?php _e( 'Single Sign-on', 'muut' ); ?></h3>
		<?php $sso_field_class = $display_values['subscription_use_sso'] ? '' : 'hidden'; ?>
		<table class="form-table">
			<tbody>
			<tr>
				<th class="th-full" colspan="2">
					<input name="setting[subscription_use_sso]" type="checkbox" id="muut_subscription_use_sso" value="1" <?php checked( '1', $display_values['subscription_use_sso'] ); ?> />
					<label for="muut_subscription_use_sso"><?php _e( 'Enabled', 'muut' ); ?></label>
				</th>
			</tr>
			<tr class="<?php echo $sso_field_class; ?> indented" data-muut_requires="muut_subscription_use_sso" data-muut_require_func="is(':checked()')">
				<th scope="row">
					<label for="muut_subscription_api_key"><?php _e( 'API Key', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[subscription_api_key]" type="text" id="muut_subscription_api_key" value="<?php echo $display_values['subscription_api_key']; ?>" />
				</td>
			</tr>
			<tr class="<?php echo $sso_field_class; ?> indented" data-muut_requires="muut_subscription_use_sso" data-muut_require_func="is(':checked()')">
				<th scope="row">
					<label for="muut_subscription_secret_key"><?php _e( 'Secret Key', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[subscription_secret_key]" type="text" id="muut_subscription_secret_key" value="<?php echo $display_values['subscription_secret_key']; ?>" />
				</td>
			</tr>
			</tbody>
		</table>
		<p class="muut_requires_input_block" data-muut_requires="muut_subscription_use_sso" data-muut_require_func="is(':not(:checked)')"><?php printf( __( 'Upgrade to Muut Developer to use the WordPress authentication system for your forum.%s No logging in twice—WordPress users automatically become Muut users.', 'muut' ), '<br />' ); ?></p>
		<p class="muut_requires_input_block" data-muut_requires="muut_subscription_use_sso" data-muut_require_func="is(':not(:checked)')"><?php printf( __( '%sUpgrade to Developer%s', 'muut' ), '<a target="_blank" href="https://muut.com/pricing/">', '</a>' ); ?></p>
	<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
		<?php endif; ?>
	</form>
</div>