<?php
/**
 * PB - Duplicate Post/Page Form Handler
 *
 * @package Duplicate_Post_Page
 * @subpackage Forms
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<section class="pbdpp">
	<div class="wrap">
		<div class="pbdpp-heading">
			<h1>Duplicate Post/Page</h1>
		</div>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'pb_duplicate_post_settings' );
			do_settings_sections( 'sc-duplicate-post-page' );
			?>
			<table class="form-table">
				<tr>
					<th><label for="pb_duplicate_post_status">Duplicate Post Status</label></th>
					<td>
						<select id="pb_duplicate_post_status" name="pb_duplicate_post_status">
							<option value="draft" <?php selected( get_option( 'pb_duplicate_post_status' ), 'draft' ); ?>>Draft</option>
							<option value="publish" <?php selected( get_option( 'pb_duplicate_post_status' ), 'publish' ); ?>>Published</option>
						</select>
						<p class="description">Select the status for duplicated posts/pages.</p>
					</td>
				</tr>
				<tr>
					<th><label for="pb_duplicate_post_redirect">Redirect After Duplication</label></th>
					<td>
						<select id="pb_duplicate_post_redirect" name="pb_duplicate_post_redirect">
							<option value="edit" <?php selected( get_option( 'pb_duplicate_post_redirect' ), 'edit' ); ?>>Edit Post</option>
							<option value="list" <?php selected( get_option( 'pb_duplicate_post_redirect' ), 'list' ); ?>>Post List</option>
						</select>
						<p class="description">Select where to redirect after duplicating a post/page.</p>
					</td>
				</tr>
				<tr>
					<th><label for="pb_duplicate_post_types">Select Post Types</label></th>
					<td>
						<select id="pb_duplicate_post_types" class="pbdpp-multi-select" name="pb_duplicate_post_types[]" multiple>
							<?php
							$post_types = get_post_types( array( 'public' => true ), 'objects' );
							foreach ( $post_types as $posttype => $object ) {
								?>
								<option value="<?php echo esc_attr( $posttype ); ?>" <?php selected( in_array( $posttype, (array) get_option( 'pb_duplicate_post_types', array() ), true ) ); ?>>
									<?php echo esc_html( $object->label ); ?>
								</option>
								<?php
							}
							?>
						</select>
						<p class="description">Select the post types that can be duplicated.</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="pb_duplicate_post_prefix">
							Duplicate Post Prefix
						</label>
					</th>
					<td>
						<input type="text" id="pb_duplicate_post_prefix" name="pb_duplicate_post_prefix" value="<?php echo esc_attr( get_option( 'pb_duplicate_post_prefix', 'Copy of ' ) ); ?>" />
						<p class="description">Enter a prefix for duplicated posts/pages.</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="pb_duplicate_post_suffix">
							Duplicate Post Suffix
						</label>
					</th>
					<td>
						<input type="text" id="pb_duplicate_post_suffix" name="pb_duplicate_post_suffix" value="<?php echo esc_attr( get_option( 'pb_duplicate_post_suffix', '' ) ); ?>" />
						<p class="description">Enter a suffix for duplicated posts/pages.</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="pb_duplicate_post_copy_terms">
							Copy Terms
						</label>
					</th>
					<td>
						<input type="checkbox" id="pb_duplicate_post_copy_terms" name="pb_duplicate_post_copy_terms" value="1" <?php checked( get_option( 'pb_duplicate_post_copy_terms' ), 1 ); ?> />
						<p class="description">Check to copy terms from the original post/page.</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="pb_duplicate_post_copy_meta">
							Copy Meta
						</label>
					</th>
					<td>
						<input type="checkbox" id="pb_duplicate_post_copy_meta" name="pb_duplicate_post_copy_meta" value="1" <?php checked( get_option( 'pb_duplicate_post_copy_meta' ), 1 ); ?>/>
						<p class="description">Check to copy meta fields from the original post/page.</p>
					</td>
				</tr>
				
			</table>
			<?php submit_button( 'Save Settings' ); ?>
		</form>
	</div>
</section>
