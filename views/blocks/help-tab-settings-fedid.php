<?php
/**
 * The content for the Muut settings contextual SSO/Federated Identities help tab.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
?>
<p>
	<?php printf( __( 'Federated Identities (previously called Single Sign-On, or SSO) lets your users use WordPress registration and login instead of Muut\'s. For this to work you need to %supgrade%s your forum to support Federated Identities with the Small or Medium subscription and then enable input your API keys in the Signed Setup section.', 'muut' ), '<a class="muut_upgrade_community_link" href="' . muut()->getUpgradeUrl() . '" target="_blank">', '</a>' ); ?>
</p>
<p>
	<?php _e( 'Once enabled, Federated Identities will be used on all the forum and commenting instances created by this plugin.', 'muut' ); ?>
</p>