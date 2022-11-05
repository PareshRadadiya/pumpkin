<?php
namespace Pumkin\Frontend;

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'wp_footer', $n( 'pumkin_frontend_alert' ) );
}

/**
 * Selected pumkin post frontend alert.
 *
 * @since 0.1.0
 */
function pumkin_frontend_alert() {
	global $post;

	if ( ! is_single() ) {
		return;
	}

	$post_id                   = $post->ID;
	$post_type                 = $post->post_type;
	$pumkin_alert_title        = get_option( 'pumkin_frontend_alert_text_title' );
	$pumkin_selected_posttypes = (array) get_option( 'pumkin_checkbox_posttype' );
	$pumkin_multiselected_post = (array) get_option( 'pumkin_multiselected_post' );

	if ( ! in_array( $post_type, $pumkin_selected_posttypes ) ) {
		return;
	}

	if ( ! in_array( $post_id, $pumkin_multiselected_post ) ) {
		return;
	} ?>
	<script type='text/javascript'>alert('<?php echo esc_html( $pumkin_alert_title ); ?>');</script>
	<?php
}
