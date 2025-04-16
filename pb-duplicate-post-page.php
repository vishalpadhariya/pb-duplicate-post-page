<?php
/**
 *
 * Duplicate Post Page
 *
 * Allows users to duplicate posts/pages along with their meta data.
 *
 * @link              https://vishalpadhariya.in/
 * @since             1.0.0
 * @package           Duplicate_Post_Page
 *
 * @wordpress-plugin
 * Plugin Name:       PB Duplicate Post / Page
 * Plugin URI:        https://github.com/vishalpadhariya/pb-duplicate-post-page
 * Short Description:     Duplicate any post or page.
 * Description:       Allows users to duplicate posts/pages along with their meta data.
 * Version:           1.0.7
 * Author:            Vishal Padhariya
 * Author URI:        https://vishalpadhariya.in/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pb-duplicate-post-page
 */

// If this file is called directly, abort..
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Plugin Version.
define( 'PBDPP_DUPLICATE_POST_PAGE_VERSION', '1.0.7' );

// Plugin Path.
define( 'PBDPP_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pb-duplicate-post-page-activator.php
 */
function pbdpp_activate_duplicate_post_page() {
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pb-duplicate-post-page-deactivator.php
 */
function pbdpp_deactivate_duplicate_post_page() {
}

register_activation_hook( __FILE__, 'pbdpp_activate_duplicate_post_page' );
register_deactivation_hook( __FILE__, 'pbdpp_deactivate_duplicate_post_page' );

require_once PBDPP_PATH . '/includes/class-pb-duplicate-post-page.php';
