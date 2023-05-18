<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://pbyteshub.in/
 * @since      1.0.0
 *
 * @package    Pb_Duplicate_Post_Page
 * @subpackage Pb_Duplicate_Post_Page/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Pb_Duplicate_Post_Page
 * @subpackage Pb_Duplicate_Post_Page/includes
 * @author     Pbytes Hub <pbytes.hub@gmail.com>
 */
class Pb_Duplicate_Post_Page_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'pb-duplicate-post-page',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
