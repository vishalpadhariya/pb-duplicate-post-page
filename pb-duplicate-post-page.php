<?php

/**
 * 
 * @link              https://vishalpadhariya.in/
 * @since             1.0.0
 * @package           Pb_Duplicate_Post_Page
 *
 * @wordpress-plugin
 * Plugin Name:       PB Duplicate
 * Plugin URI:        https://github.com/vishalpadhariya/pb-duplicate-post-page
 * Description:       Duplicate all pages and post on one click event.
 * Version:           1.0.0
 * Author:            Vishal Padhariya
 * Author URI:        https://vishalpadhariya.in/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pb-duplicate-post-page
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PB_DUPLICATE_POST_PAGE_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pb-duplicate-post-page-activator.php
 */
function activate_pb_duplicate_post_page()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-pb-duplicate-post-page-activator.php';
	Pb_Duplicate_Post_Page_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pb-duplicate-post-page-deactivator.php
 */
function deactivate_pb_duplicate_post_page()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-pb-duplicate-post-page-deactivator.php';
	Pb_Duplicate_Post_Page_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_pb_duplicate_post_page');
register_deactivation_hook(__FILE__, 'deactivate_pb_duplicate_post_page');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-pb-duplicate-post-page.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pb_duplicate_post_page()
{

	$plugin = new Pb_Duplicate_Post_Page();
	$plugin->run();
}
run_pb_duplicate_post_page();

/*
 * Function for post duplication. Dups appear as drafts. User is redirected to the edit screen
 */
function pb_duplicate_post_as_draft() {
	global $wpdb;

	if (!(isset($_GET['post']) || isset($_POST['post'])  || (isset($_REQUEST['action']) && 'pb_duplicate_post_as_draft' === $_REQUEST['action']))) {
		wp_die('No post to duplicate has been supplied!');
	}

	if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], basename(__FILE__))) {
		return;
	}

	$post_id = (isset($_GET['post']) ? intval($_GET['post']) : intval($_POST['post']));

	$post = get_post($post_id);

	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;

	if (isset($post) && $post !== null) {
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

		$new_post_id = wp_insert_post(wp_slash($args));

		$taxonomies = get_object_taxonomies($post->post_type);

		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}

		$post_meta_infos = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=%d", $post_id));

		if (count($post_meta_infos) !== 0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			$sql_query_sel = array();

			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;

				if ($meta_key === '_wp_old_slug') {
					continue;
				}

				$meta_value = sanitize_meta($meta_key, $meta_info->meta_value, $post->post_type);

				$sql_query_sel[] = $wpdb->prepare("(%d, %s, %s)", $new_post_id, $meta_key, $meta_value);
			}

			$sql_query .= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}

		wp_redirect(admin_url('post.php'));
		exit;
	} else {
		wp_die('Post creation failed, could not find original post: ' . $post_id);
	}
}
add_action('admin_action_pb_duplicate_post_as_draft', 'pb_duplicate_post_as_draft');

function pb_duplicate_post_link($actions, $post) {
	if (current_user_can('edit_posts')) {
		$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=pb_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce') . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
	}
	return $actions;
}
add_filter('post_row_actions', 'pb_duplicate_post_link', 10, 2);
add_filter('page_row_actions', 'pb_duplicate_post_link', 10, 2);
