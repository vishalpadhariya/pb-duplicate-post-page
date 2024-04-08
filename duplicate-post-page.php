<?php
/**
 *
 * Duplicate Post / Page
 *
 * Allows users to duplicate posts/pages along with their meta data.
 *
 * @link              https://vishalpadhariya.in/
 * @since             1.0.0
 * @package           Duplicate_Post_Page
 *
 * @wordpress-plugin
 * Plugin Name:       Duplicate Post / Page
 * Plugin URI:        https://github.com/vishalpadhariya/pb-duplicate-post-page
 * Short Description:     Duplicate any post or page.
 * Description:       Allows users to duplicate posts/pages along with their meta data.
 * Version:           1.0.3
 * Author:            Vishal Padhariya
 * Author URI:        https://vishalpadhariya.in/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       duplicate-post-page
 * Domain Path:       /languages
 */

// If this file is called directly, abort..
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DUPLICATE_POST_PAGE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pb-duplicate-post-page-activator.php
 */
function activate_duplicate_post_page() {
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pb-duplicate-post-page-deactivator.php
 */
function deactivate_duplicate_post_page() {
}

register_activation_hook( __FILE__, 'activate_duplicate_post_page' );
register_deactivation_hook( __FILE__, 'deactivate_duplicate_post_page' );


/**
 * Disable auto-updates for duplicate-post-page.
 *
 * @param bool   $update Whether to update the plugin.
 * @param object $item   The plugin update object.
 * @return bool Modified value to control auto-updates.
 */
function vp_dpp_disable_auto_update_specific_plugins( $update, $item ) {
	// Array of plugin slugs to exclude from auto-updates.
	$plugins_to_exclude = array(
		'duplicate-post-page/duplicate-post-page.php',
	);

	if ( in_array( $item->slug, $plugins_to_exclude, true ) ) {
		return false; // Disable auto-updates for this plugin.
	}

	return $update;
}
add_filter( 'auto_update_plugin', 'vp_dpp_disable_auto_update_specific_plugins', 10, 2 );

/**
 * Add custom message for plugins with auto-updates disabled.
 *
 * @param array  $plugin_meta Array of plugin meta links.
 * @param string $plugin_file  Plugin file path.
 * @return array Modified array of plugin meta links.
 */
function vp_dpp_update_plugin_row_meta( $plugin_meta, $plugin_file ) {
	// Define the plugin slug or file path for which you want to display the custom message.
	$disabled_plugin = 'duplicate-post-page/duplicate-post-page.php'; // Replace with your plugin's path.

	// Check if the current plugin is the one with auto-updates disabled.
	if ( $plugin_file === $disabled_plugin ) {
		// Add custom message.
		$plugin_meta[] = '<a href="https://vishalpadhariya.in">Contributor</a>';
		$plugin_meta[] = '<span style="color: #dd3d36;">Auto-updates unavailable.</span>';
	}

	return $plugin_meta;
}
add_filter( 'plugin_row_meta', 'vp_dpp_update_plugin_row_meta', 10, 2 );

/**
 * Duplicate a post or page in WordPress.
 *
 * This function creates a duplicate of the specified post or page in WordPress.
 * It copies all the content, metadata, and terms associated with the original post,
 * and assigns a new unique slug and post title with the specified prefix appended.
 */
function vp_duplicate_post_as_draft() {
	global $wpdb;

	if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && 'duplicate_post_as_draft' === $_REQUEST['action'] ) ) ) {
		wp_die( 'No post to duplicate has been supplied!' );
	}

	if ( ! isset( $_GET['duplicate_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['duplicate_nonce'] ) ), basename( __FILE__ ) ) ) {
		return;
	}

	$post_id = ( isset( $_GET['post'] ) ? intval( $_GET['post'] ) : intval( $_POST['post'] ) );

	$post = get_post( $post_id );

	$current_user    = wp_get_current_user();
	$new_post_author = $current_user->ID;

	if ( isset( $post ) && null !== $post ) {

		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order,
		);

		$new_post_id = wp_insert_post( wp_slash( $args ) );

		// Duplicate post meta data.
		$meta_data = get_post_meta( $post_id );
		foreach ( $meta_data as $key => $value ) {
			update_post_meta( $new_post_id, $key, maybe_unserialize( $value[0] ) );
		}

		$taxonomies = get_object_taxonomies( $post->post_type );

		foreach ( $taxonomies as $taxonomy ) {
			$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
			wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
		}

		if ( isset( $_SERVER['HTTP_REFERER'] ) && ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$referral_url = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
			wp_safe_redirect( $referral_url );
		} else {
			wp_safe_redirect( admin_url( '/' ) );
		}

		exit();
	} else {
		wp_die( 'Post creation failed, could not find original post: ' . esc_html( $post_id ) );
	}
}
add_action( 'admin_action_duplicate_post_as_draft', 'vp_duplicate_post_as_draft' );

/**
 * Add 'Duplicate' named link to post or page row actions.
 *
 * @param array   $actions An array of row action links.
 * @param WP_Post $post    The post object.
 * @return array Modified array of row action links.
 */
function vp_duplicate_post_link( $actions, $post ) {
	if ( current_user_can( 'edit_posts' ) ) {
		$actions['duplicate'] = '<a href="' . esc_url( wp_nonce_url( 'admin.php?action=duplicate_post_as_draft&post=' . $post->ID, basename( __FILE__ ), 'duplicate_nonce' ) ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
	}
	return $actions;
}
add_filter( 'post_row_actions', 'vp_duplicate_post_link', 10, 2 );
add_filter( 'page_row_actions', 'vp_duplicate_post_link', 10, 2 );
