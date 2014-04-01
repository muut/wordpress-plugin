<?php
/**
 * The Muut settings page.
 *
 * @package   Muut
 * @copyright 2014 Moot Inc
 */
$languages = muut()->getLanguages();
$current_language = muut()->getOption( 'language', 'en' );
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
			<tr>
				<th scope="row">
					<label for="muut_replace_comments"><?php _e( 'Use Muut for commenting', 'muut' ); ?></label>
				</th>
				<td>
					<input name="setting[replace_comments]" type="checkbox" id="muut_replace_comments" value="1" <?php checked( '1', muut()->getOption( 'replace_comments', '0' ) ); ?> />
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
</div>