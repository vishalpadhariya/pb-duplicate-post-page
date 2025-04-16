<?php
/**
 * PB Duplicate Post Page
 *
 * This file contains the PB_Duplicate_Post_Page class, which handles the duplication
 * of posts and pages in the WordPress admin panel.
 *
 * @package Duplicate_Post_Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Class PB_Duplicate_Post_Page
 *
 * Handles the duplication of posts and pages in the admin panel.
 */
class PB_Duplicate_Post_Page {

	/**
	 * Constructor for the PB_Duplicate_Post_Page class.
	 *
	 * Initializes the class and sets up the necessary properties.
	 */
	public function __construct() {
		// Add settings page and actions for duplicating posts.
		add_action( 'admin_init', array( $this, 'pb_register_settings' ) );

		add_action( 'admin_menu', array( $this, 'pb_add_settings_page' ) );
		add_filter( 'post_row_actions', array( $this, 'pb_add_duplicate_link' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'pb_add_duplicate_link' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'pb_handle_duplicate_action' ) );
	}

	/**
	 * Register settings for duplicate posts.
	 *
	 * This method registers the settings for duplicating posts and pages.
	 */
	public function pb_register_settings() {
		register_setting(
			'pb_duplicate_post_settings',
			'pb_duplicate_post_status',
			array(
				'type'              => 'string',
				'description'       => 'Status for duplicated posts',
				'show_in_rest'      => true,
				'default'           => 'draft',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			'pb_duplicate_post_settings',
			'pb_duplicate_post_redirect',
			array(
				'type'              => 'string',
				'description'       => 'Redirect after duplication',
				'show_in_rest'      => true,
				'default'           => 'edit',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			'pb_duplicate_post_settings',
			'pb_duplicate_post_types',
			array(
				'type'              => 'array',
				'description'       => 'Post types that can be duplicated',
				'show_in_rest'      => true,
				'default'           => array( 'post' ),
				'sanitize_callback' => array( $this, 'pb_sanitize_post_types' ),
			)
		);

		register_setting(
			'pb_duplicate_post_settings',
			'pb_duplicate_post_prefix',
			array(
				'type'              => 'string',
				'description'       => 'Prefix for duplicated posts',
				'show_in_rest'      => true,
				'default'           => 'Copy of ',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			'pb_duplicate_post_settings',
			'pb_duplicate_post_suffix',
			array(
				'type'              => 'string',
				'description'       => 'Suffix for duplicated posts',
				'show_in_rest'      => true,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			'pb_duplicate_post_settings',
			'pb_duplicate_post_copy_terms',
			array(
				'type'              => 'boolean',
				'description'       => 'Copy terms from original post',
				'show_in_rest'      => true,
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);

		register_setting(
			'pb_duplicate_post_settings',
			'pb_duplicate_post_copy_meta',
			array(
				'type'              => 'boolean',
				'description'       => 'Copy terms from original post',
				'show_in_rest'      => true,
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);
	}

	/**
	 * Sanitize post types.
	 *
	 * @param array $input Array of post types.
	 *
	 * @return array Sanitized post types.
	 */
	public function pb_sanitize_post_types( $input ) {
		$valid_post_types = get_post_types( array( 'public' => true ), 'names' );
		$sanitized_input  = array();

		foreach ( $input as $post_type ) {
			if ( in_array( $post_type, $valid_post_types, true ) ) {
				$sanitized_input[] = sanitize_text_field( $post_type );
			}
		}

		return $sanitized_input;
	}

	/**
	 * Adds a settings page for duplicate posts.
	 *
	 * This method registers a submenu page under the Spinx Core menu
	 * for managing duplicate post settings.
	 */
	public function pb_add_settings_page() {
		add_menu_page(
			'Duplicate Posts Settings',
			'Duplicate Posts',
			'manage_options',
			'pb-duplicate-post-page-settings',
			array( $this, 'pb_render_duplicate_post_settings_page' ),
			'dashicons-admin-page',
			11
		);
	}

	/**
	 * Render the settings page for duplicate posts.
	 *
	 * This method outputs the HTML content for the duplicate posts settings page.
	 */
	public function pb_render_duplicate_post_settings_page() {
		include_once PBDPP_PATH . '/includes/forms/pb-form-duplicate-post-page.php';
	}

	/**
	 * Add duplicate link to post actions.
	 *
	 * @param array   $actions Array of actions.
	 * @param WP_Post $post Current post object.
	 *
	 * @return array Modified actions.
	 */
	public function pb_add_duplicate_link( $actions, $post ) {
		$post_types = $this->pb_get_setting( 'pb_duplicate_post_types', array() );

		if ( in_array( $post->post_type, $post_types, true ) && current_user_can( 'edit_posts' ) ) {
			$url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'duplicate_post',
						'post'   => $post->ID,
					),
					admin_url( 'admin.php' )
				),
				'duplicate_post_nonce',
				'nonce'
			);

			$actions['duplicate'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Duplicate', 'pb-duplicate-post-page' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Get the option value from the database.
	 *
	 * @param string $option_name The name of the option to retrieve.
	 * @param mixed  $default_value The default value to return if the option is not found.
	 * @return mixed The value of the option, or false if not found.
	 */
	private function pb_get_setting( $option_name, $default_value = false ) {
		// Check if the option exists in the database.
		if ( ! $this->pb_setting_exist( $option_name ) ) {
			return $default_value;
		}
		// Get the option value from the database.
		$value = get_option( $option_name );
		if ( is_array( $value ) ) {
			$value = array_map( 'sanitize_text_field', $value );
		} else {
			$value = sanitize_text_field( $value );
		}
		return $value;
	}

	/**
	 * Check if the option exists in the database.
	 *
	 * @param string $option_name The name of the option to check.
	 * @return bool True if the option exists, false otherwise.
	 */
	private function pb_setting_exist( $option_name ) {
		return get_option( $option_name ) !== false;
	}

	/**
	 * Handle post duplication.
	 */
	public function pb_handle_duplicate_action() {
		if ( isset( $_GET['action'], $_GET['post'], $_GET['nonce'] ) && 'duplicate_post' === $_GET['action'] ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'duplicate_post_nonce' ) ) {
				wp_die( esc_html__( 'Invalid request.', 'pb-duplicate-post-page' ) );
			}

			$post_id = intval( $_GET['post'] );
			$post    = get_post( $post_id );

			if ( $post && current_user_can( 'edit_posts' ) ) {

				$post_title  = $this->pb_get_setting( 'pb_duplicate_post_prefix', 'Copy of' ) . ' ' . $post->post_title;
				$post_title .= ' ' . $this->pb_get_setting( 'pb_duplicate_post_suffix', '' );
				$post_title  = sanitize_text_field( $post_title );

				$new_post = array(
					'post_title'   => $post_title,
					'post_content' => $post->post_content,
					'post_status'  => $this->pb_get_setting( 'pb_duplicate_post_status', 'draft' ),
					'post_type'    => $post->post_type,
					'post_author'  => get_current_user_id(),
				);

				$new_post_id = wp_insert_post( $new_post );

				if ( $new_post_id ) {
					$copy_terms = $this->pb_get_setting( 'pb_duplicate_post_copy_terms', true );
					$copy_meta  = $this->pb_get_setting( 'pb_duplicate_post_copy_meta', true );

					if ( $copy_terms ) {
						$taxonomies = get_object_taxonomies( $post->post_type, 'names' );
						foreach ( $taxonomies as $taxonomy ) {
							$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
							if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
								wp_set_object_terms( $new_post_id, $terms, $taxonomy );
							}
						}
					}

					if ( $copy_meta ) {
						$meta_keys = get_post_custom_keys( $post_id );
						if ( ! empty( $meta_keys ) ) {
							foreach ( $meta_keys as $meta_key ) {
								$meta_value = get_post_meta( $post_id, $meta_key, true );
								update_post_meta( $new_post_id, sanitize_text_field( $meta_key ), sanitize_text_field( $meta_value ) );
							}
						}
					}
				}
				// Redirect after duplication.
				$redirect_to = $this->pb_get_setting( 'pb_duplicate_post_redirect', 'edit' );
				if ( 'edit' === $redirect_to ) {
					wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
				} else {
					wp_safe_redirect( admin_url( 'edit.php?post_type=' . $post->post_type ) );
				}
				exit;
			} else {
				// If the post is not found or the user does not have permission, show an error message.
				wp_die( esc_html__( 'You do not have permission to duplicate this post.', 'pb-duplicate-post-page' ) );
			}
		}
	}
}

// Initialize the class.
new PB_Duplicate_Post_Page();
