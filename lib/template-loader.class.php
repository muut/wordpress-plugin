<?php
/**
 * The template loader class file for the Muut plugin.
 * Responsible for all template loading functionality.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Template_Loader' ) ) {

	/**
	 * Muut Template Loader class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Template_Loader
	{

		/**
		 * @static
		 * @property Muut_Template_Loader The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Template_Loader The instance.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public static function instance() {
			if ( !is_a( self::$instance, __CLASS__ ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * The class constructor.
		 *
		 * @return Muut_Template_Loader
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * Adds the main actions for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addActions() {

		}

		/**
		 * Adds the main filters for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addFilters() {
			add_filter( 'template_include', array( $this, 'getProperTemplate' ) );
		}

		/**
		 *  Gets the proper Muut template, if necessary.
		 *
		 * @param string $template The current template being requested.
		 * @return string The revised template path to load.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getProperTemplate( $template ) {
			if ( is_page() && Muut_Forum_Page_Utility::isForumPage( get_the_ID() ) ) {
				//TODO: Allow for other forum templates to get loaded (not just the one).
				if ( muut()->getOption( 'forum_home_id', false ) == get_the_ID() ) {
					$template = 'forum-muut-ux.php';
					$located = $this->locateTemplate( $template );
					$template = $located != '' ? $located : $template;
				}
			}
			return $template;
		}

		/**
		 * Locates the proper template file, whether it is in the plugin templates directory or a sub-directory of the
		 * active theme.
		 *
		 * @param string $template The template file name (not path).
		 * @return string The path to the template file that should be loaded.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function locateTemplate( $template ) {
			// Other locations can be added by filtering them into this array (in priority order).
			$locations_to_search = apply_filters( 'muut_template_directories', array(
				// Theme directory
				'theme' => get_stylesheet_directory() . '/muut/',
				// Plugin template directory
				'muut' => muut()->getPluginPath() . 'templates/',
			) );

			$template_path = '';

			while ( $template_path == '' && !empty( $locations_to_search ) ){
				$location = array_shift( $locations_to_search );
				if ( file_exists( $location . $template ) ) {
					$template_path = $location . $template;
				}
			}

			return $template_path;
		}
	}

}