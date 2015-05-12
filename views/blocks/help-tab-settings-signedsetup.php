<?php
/**
 * The content for the Muut settings contextual Signed Setup help tab.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
?>
<p>
	<?php _e( 'If you have a Small or Medium subscription and are using Federated Identities (Small) or Secure Embedding (Medium), you need to enable signed embedding by entering your API credentials in this section. You can get the API keys directly from the forum frontend (either on your embed or at muut.com) by clicking the "Settings" link and copying the API Key and Secret Key from the top bar.', 'muut' ); ?>
</p>
<p>
	<?php printf( __( 'If you are using a caching plugin, make sure to check the "Caching" checkbox in this section so that the signed data is dynamically fetched. Note that the cache %smust%s be set to expire within every 24 hours.', 'muut' ), '<b>', '</b>' ); ?>
</p>
<p>
	<?php _e( 'This is not required (and is discouraged) if you are not using one of the premium services dependent on it.', 'muut' ); ?>
</p>
<p>
	<?php printf( __( 'For more information about our premium plans and features, see our %spricing page%s or %supgrade your forum%s now.', 'muut' ), '<a target="_blank" href="https://muut.com/pricing/">', '</a>', '<a class="muut_upgrade_community_link" href="' . muut()->getUpgradeUrl() . '" target="_blank">', '</a>' ); ?>
</p>