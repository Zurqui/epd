<?php
/**
 * Easy Digital Downloads Integration
 *
 * @package     EPD Premium
 * @subpackage  Integrations/Functions
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Add support for the download post type.
 *
 * @since	1.2
 * @param	array	$post_types	Array of supported post types
 * @return	array	Array of supported post types
 */
function epdp_edd_add_download_post_type_support( $post_types )	{
	$post_types[] = 'download';

	return $post_types;
} // epdp_edd_add_download_post_type_support
add_filter( 'epdp_supported_post_types', 'epdp_edd_add_download_post_type_support' );

/**
 * Get the demo for a download.
 *
 * @since	1.2
 * @param	int		$download_id	The download ID
 * @return	int		The demo ID
 */
function epdp_edd_get_download_demo( $download_id )	{
	$demo_id = get_post_meta( $download_id, '_epdp_demo', true );
	$demo_id = '' != $demo_id ? absint( $demo_id ) : false;

	return $demo_id;
} // epdp_edd_get_download_demo

/**
 * Get the text for a download demo button.
 *
 * @since	1.2
 * @param	int		$download_id	The download ID
 * @return	string	The button text
 */
function epdp_edd_get_download_demo_text( $download_id )	{
	$text = get_post_meta( $download_id, '_epdp_button_text', true );
	$text = '' != $text ? $text : epd_get_option( 'button_text' );

	return $text;
} // epdp_edd_get_download_demo_text

/**
 * Get the position for a download demo button.
 *
 * @since	1.2
 * @param	int		$download_id	The download ID
 * @return	string	The button position
 */
function epdp_edd_get_download_demo_placement( $download_id )	{
	$placement = get_post_meta( $download_id, '_epdp_button_placement', true );
	$placement = '' != $placement ? $placement : epd_get_option( 'button_placement' );

	return $placement;
} // epdp_edd_get_download_demo_placement

/**
 * Before Download Content
 *
 * Adds an action to the beginning of download post content that can be hooked to
 * by other functions.
 *
 * @since   1.1
 * @global  $post
 * @param   string  $content    The the_content field of the download object
 * @return  string  The content with any additional data attached
 */
function epdp_before_download_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'epdp_before_download_content', $post->ID );
		$content = ob_get_clean() . $content;
	}

	return $content;
} // epdp_before_download_content
add_filter( 'the_content', 'epdp_before_download_content' );

/**
 * After Download Content
 *
 * Adds an action to the end of download post content that can be hooked to by
 * other functions.
 *
 * @since   1.1
 * @global  $post
 * @param   string  $content    The the_content field of the download object
 * @return  string  The content with any additional data attached
 */
function epdp_after_download_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'epdp_after_download_content', $post->ID );
		$content .= ob_get_clean();
	}

	return $content;
} // epdp_after_download_content
add_filter( 'the_content', 'epdp_after_download_content' );

/**
 * Adds the Register for Demo button before download content.
 *
 * @since   1.2
 * @param   int     $download_id    Download post ID
 * @return  void
 */
function epdp_before_download_button( $download_id )  {
	$placement = epdp_demo_get_button_placement( $download_id );

	if ( 'before_post' == $placement || 'both' == $placement )	{
		$demo_id = epdp_edd_get_download_demo( $download_id );

		if ( $demo_id )	{
			$args = array(
				'demo_id' => $demo_id,
				'text'    => epdp_edd_get_download_demo_text( $download_id )
			);

			echo epdp_output_demo_button( $args );
		}
	}
} // epdp_before_download_button
add_action( 'epdp_before_download_content', 'epdp_before_download_button' );

/**
 * Adds the Register for Demo button after download content.
 *
 * @since   1.2
 * @param   int     $download_id    Download post ID
 * @return  void
 */
function epdp_after_download_button( $download_id )  {
    $placement = epdp_demo_get_button_placement( $download_id );

	if ( 'after_post' == $placement || 'both' == $placement )	{
		$demo_id = epdp_edd_get_download_demo( $download_id );

		if ( $demo_id )	{
			$args = array(
				'demo_id' => $demo_id,
				'text'    => epdp_edd_get_download_demo_text( $download_id )
			);

			echo epdp_output_demo_button( $args );
		}
	}
} // epdp_after_download_button
add_action( 'epdp_after_download_content', 'epdp_after_download_button' );

/**
 * Default fields and values to display.
 *
 * @since	1.2
 * @return	array	Array of fields => values
 */
function epd_edd_metabox_fields()	{
	$fields = array(
		'_epdp_demo'             => 0,
        '_epdp_button_text'      => epd_get_option( 'button_text' ),
        '_epdp_button_placement' => epd_get_option( 'button_placement' )
	);

	$fields = apply_filters( 'epd_edd_metabox_fields', $fields );

	return $fields;
} // epd_edd_metabox_fields
/**
 * Add the demo button metabox for the download post type.
 *
 * @since	1.2
 * @param	object	$post	The WP_Post object.
 * @return	void
 */
function epdp_demo_add_edd_meta_box( $post )	{
	add_meta_box(
        'epdp-edd-demo-metabox-button',
        __( 'Easy Plugin Demo Button', 'epd-premium' ),
        'epdp_edd_demo_metabox_button_options_callback',
        'download',
        'side'
    );
} // epdp_demo_add_edd_meta_box
add_action( 'add_meta_boxes_download', 'epdp_demo_add_edd_meta_box' );

/**
 * Callback for the button options metabox.
 *
 * @since   1.2
 * @param	object  $post   WP_Post object
 * @return  void
 */
function epdp_edd_demo_metabox_button_options_callback( $post ) {
	$demo_id          = epdp_edd_get_download_demo( $post->ID );
	$button_text      = epdp_edd_get_download_demo_text( $post->ID );
	$button_placement = epdp_edd_get_download_demo_placement( $post->ID );
    ob_start();

    wp_nonce_field( 'epd_edd_meta_save', 'epd_edd_meta_box_nonce' ); ?>

	<div class="epdp-button-options">
		<div class="epdp-side-option-row">
			<span class="metabox-option-label">
				<?php _e( 'Demo:', 'epd-premium' ); ?>
			</span>
			<div class="epdp-option">
				<select id="epdp-edd-demo" name="_epdp_demo" class="epdp_select_chosen">
					<option value="0"<?php selected( false, $demo_id ); ?>>
						<?php _e( 'Select a Demo', 'epd-premium' ); ?>
					</option>
					<?php foreach( epdp_get_demos() as $demo ) : ?>
						<?php printf( 
							'<option value="%s"%s>%s</option>',
							$demo->ID,
							selected( $demo->ID, $demo_id, false ),
							get_the_title( $demo )
						); ?>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="epdp-side-option-row">
			<span class="metabox-option-label">
				<?php _e( 'Button Text:', 'epd-premium' ); ?>
			</span>
			<div class="epdp-option">
				<input type="text" id="epdp-button-text" name="_epdp_button_text" class="epdp_input" value="<?php echo $button_text; ?>">
			</div>
		</div>
		<div class="epdp-side-option-row">
			<span class="metabox-option-label">
				<?php _e( 'Placement:', 'epd-premium' ); ?>
			</span>
			<div class="epdp-option">
				<select id="epdp-button-placement" name="_epdp_button_placement" class="epdp_select_chosen">
					<?php foreach( epdp_get_button_placement_setting_options() as $value => $label ) : ?>
						<?php printf( 
							'<option value="%s"%s>%s</option>',
							$value,
							selected( $button_placement, $value, false ),
							$label
						); ?>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
    </div>

	<?php echo ob_get_clean();
} // epdp_edd_demo_metabox_button_options_callback

/**
 * Save the EPD Demo custom posts
 *
 * @since	1.1
 * @param	int		$post_id		The ID of the post being saved.
 * @param	object	$post			The WP_Post object of the post being saved.
 * @param	bool	$update			Whether an existing post if being updated or not.
 * @return	void
 */
function epd_edd_download_post_save( $post_id, $post, $update )	{	

	if ( ! isset( $_POST['epd_edd_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['epd_edd_meta_box_nonce'], 'epd_edd_meta_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	{
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

    remove_action( 'save_post_download', 'epd_edd_download_post_save', 10, 3 );

	// The default fields that get saved
	$fields = epd_edd_metabox_fields();
	$saving = array();

	foreach ( $fields as $field => $default )	{
		$posted_value = '';

		if ( ! empty( $_POST[ $field ] ) ) {
            $posted_value = $_POST[ $field ];
		} else	{
            $posted_value = $default;
		}

		$new_value = apply_filters( 'epd_edd_metabox_save_' . $field, $posted_value );

		$saving[ $field ] = $new_value;
	}

	foreach( $saving as $meta_key => $meta_value )	{
		if ( '' != $meta_value )	{
			update_post_meta( $post_id, $meta_key, $meta_value );
		} else	{
			delete_post_meta( $post_id, $meta_key );
		}
	}

	add_action( 'save_post_download', 'epd_edd_download_post_save', 10, 3 );
} // epd_demo_post_save
add_action( 'save_post_download', 'epd_edd_download_post_save', 10, 3 );
