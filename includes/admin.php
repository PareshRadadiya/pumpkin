<?php
namespace Pumkin\Admin;

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'admin_enqueue_scripts', $n( 'pumkin_enqueue_scripts' ) );
	add_action( 'admin_menu', $n( 'pumkin_setting_page' ) );
	add_action( 'admin_init', $n( 'pumkin_save_changes' ) );
	add_action( 'wp_ajax_pumkin_get_all_posts', $n( 'pumkin_get_all_posts_handler' ) );
}

/**
 * Handles the enqueueing of scripts and style.
 *
 * @since 0.1.0
 */
function pumkin_enqueue_scripts() {
	 global $pagenow;

	if ( $pagenow !== 'options-general.php' || ! isset( $_GET['page'] ) || 'pumkin-setting-admin' !== $_GET['page'] ) {
		return;
	}

	wp_enqueue_script(
		'pumpki-admin-script',
		PUMKIN_URL . 'asset/js/admin.js',
		[ 'jquery' ],
		PUMKIN_VERSION,
		true
	);

	wp_localize_script(
		'pumpki-admin-script',
		'ajaxVars',
		array(
			'get_all_posts_nonce' => wp_create_nonce( 'get-all-posts-nonce' ),
		)
	);

	wp_enqueue_style(
		'pumkin-admin-style',
		PUMKIN_URL . 'asset/css/admin.css',
		[],
		PUMKIN_VERSION
	);
}

/**
 * Set up the hooks for the Custom Pumkin Settings admin page.
 *
 * @since 0.1.0
 */
function pumkin_setting_page() {
	add_options_page(
		'Pumkin Settings',
		'Pumkin Settings',
		'manage_options',
		'pumkin-setting-admin',
		__NAMESPACE__ . '\\pumkin_settings_page_content'
	);
}

/**
 * Callback for Custom Pumkin Settings admin page content.
 *
 * @since 0.1.0
 */
function pumkin_settings_page_content() {
	$pumkin_selected_posttypes = (array) get_option( 'pumkin_checkbox_posttype' );
	$pumkin_multiselected_post = (array) get_option( 'pumkin_multiselected_post' );
	$pumkin_multiselected_post_alerts = (array) get_option( 'pumkin_multiselected_post_alerts' );
?>
	<div class="wrap">
		<h1>Pumkin Settings</h1>
			<div class="pumkin-settings-content">
					<form method="POST" class="admin-form">
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">
										<label for="pumkin_adding_text_for_alert_frontend" id="pumkin_adding_text_for_alert_frontend">Add Text For Frontend Alert</label>
									</th>
									<td>
										<input type="text" name="pumkin_frontend_alert_text_title" id="pumkin_frontend_alert_text_title" value="<?php echo esc_attr( get_option( 'pumkin_frontend_alert_text_title' ) ); ?>" class="pumkin_frontend_alert_text_title" />
									</td>
								</tr>
								<tr>
									<th scope="row">Select Post Type</th>
									<td> 
									<?php
										$pumkin_all_post_types = get_post_types(
											[
												'public'  => true,
												'publicly_queryable' => true,
												'show_ui' => true,
											],
											'object'
										);

										foreach ( $pumkin_all_post_types as $pumkin_post ) {
											$pumkin_posttype_label = $pumkin_post->label;
											$pumkin_posttype_name  = $pumkin_post->name;
											$checked               = in_array( $pumkin_posttype_name, $pumkin_selected_posttypes, true ) ? ' checked="checked" ' : '';
											$class_name            = in_array( $pumkin_posttype_name, $pumkin_selected_posttypes, true ) ? 'pumpkin-multiselect-posts-selected' : 'pumpkin-multiselect-posts';
											$post_alert_text       = isset( $pumkin_multiselected_post_alerts[$pumkin_posttype_name] ) ? $pumkin_multiselected_post_alerts[$pumkin_posttype_name] : '';
											?>

											<div class="pumkin-checkbox-post-type">
												<input type="checkbox" data-id="<?php echo esc_attr( $pumkin_post->name ); ?>" class="pumkin-get-post-types" id="<?php echo $pumkin_posttype_name; ?>" name="pumkin_checkbox_posttype[]" value="<?php echo esc_attr( $pumkin_posttype_name ); ?>" <?php echo $checked; ?>>
												<label for="<?php echo esc_attr( $pumkin_posttype_name ); ?>" class="pumkin-checkbox-title"> <?php echo $pumkin_posttype_label; ?></label>
											</div>

											<div class="multiselect-posts-<?php echo esc_attr( $pumkin_posttype_name ); ?>">
												<input type="text" name="multiselected_posts[<?php echo esc_attr( $pumkin_posttype_name ); ?>][]" value="<?php echo esc_attr( $post_alert_text ); ?>" />
												<select name="multiselected_posts[]" class="pumkin-multiselect-post <?php echo esc_attr( $class_name ); ?>" multiple>
												<?php
												if ( in_array( $pumkin_posttype_name, $pumkin_selected_posttypes, true ) ) {
													$pumkin_selcted_posttype_post = get_posts(
														[
															'numberposts' => 10,
															'post_type'   => $pumkin_post->name,
														]
													);
													foreach ( $pumkin_selcted_posttype_post as $pumkin_post ) {
														?>
															<?php $selected = in_array( $pumkin_post->ID, $pumkin_multiselected_post, true ) ? ' selected="selected" ' : ''; ?>
														<option value="<?php echo esc_attr( $pumkin_post->ID ); ?>" name="multiselected_post" <?php echo $selected; ?>>
																<?php echo esc_attr( $pumkin_post->post_title ); ?>
														</option>
														<?php
													}
												}
												?>
												</select>
											</div>
									</div> <?php } ?>
							</fieldset>
					  </td>
				</tr>
			</tbody>
		</table>
			<button type="submit" class="button button-primary" name="pumkin_save_changes" >Save Changes</button>
			<?php wp_nonce_field( 'pumkin_save_changes_nounce', 'pumkin_save_changes_nounce' ); ?>
	 </form>
</div> <?
}

/**
 * Custom pumkin admin settings save changes.
 *
 * @since 0.1.0
 */
function pumkin_save_changes() {
	if ( ! isset( $_POST['pumkin_save_changes_nounce'] ) || ! wp_verify_nonce( $_POST['pumkin_save_changes_nounce'], 'pumkin_save_changes_nounce' ) ) {
		return;
	}

	if ( isset( $_POST['pumkin_save_changes'] ) ) {
		if ( ! empty( $_POST['pumkin_frontend_alert_text_title'] ) ) {
			update_option( 'pumkin_frontend_alert_text_title', sanitize_text_field( $_POST['pumkin_frontend_alert_text_title'] ) );
		} else {
			delete_option( 'pumkin_frontend_alert_text_title' );
		}

		if ( ! empty( $_POST['pumkin_checkbox_posttype'] ) ) {
			update_option( 'pumkin_checkbox_posttype', array_map( 'sanitize_text_field', $_POST['pumkin_checkbox_posttype'] ) );
		} else {
			delete_option( 'pumkin_checkbox_posttype' );
		}

		if ( ! empty( $_POST['multiselected_posts'] ) ) {
			update_option( 'pumkin_multiselected_post', array_map( 'absint', $_POST['multiselected_posts'] ) );
		} else {
			delete_option( 'pumkin_multiselected_post' );
		}
	}
}

/**
 * Ajax handler for sending a selected pumkin posttype send that all posts .
 *
 * @since 0.1.0
 */
function pumkin_get_all_posts_handler() {
	check_ajax_referer( 'get-all-posts-nonce' );

	$type = sanitize_text_field( $_GET['post_types_name'] );

	$all_post = get_posts([
		'numberposts' => 10,
		'post_type'   => $type,
	]);

	$data = wp_list_pluck( $all_post, 'post_title', 'ID' );
	wp_send_json( $data );
}
