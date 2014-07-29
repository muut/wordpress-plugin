<?php
/**
 * Display the text that a given widget can only be active if Webhooks are activated.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within an instance of one of the Muut widgets (descendants of WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 */

?>
<script type="text/javascript">
	jQuery(function($) {
		var id_base = "<?php echo $this->id_base; ?>";
		$('#widgets-right').find('input.id_base[value="' + id_base + '"]').closest('.widget-inside').find('input.widget-control-save').hide();
	});
</script>
<p class="muut_widget_error">
<?php printf( __( 'This widget uses webhooks and requires a Muut Developer subscription. %sEnable webhooks%s', 'muut' ), '<br /><a href="' . admin_url( 'admin.php?page=muut' ) . '">', '</a>' ); ?>
</p>
