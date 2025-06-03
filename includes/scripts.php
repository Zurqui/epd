<?php
/**
 * Scripts
 *
 * @package     EPD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Load front end scripts.
 *
 * @since	1.2
 * @return	void
 */
function epdp_load_scripts()	{
	$assets_dir  = trailingslashit( EPD_PREMIUM_URL . '/assets' );
	$js_dir      = trailingslashit( $assets_dir . 'js' );
	$suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$demo_ref    = isset( $_REQUEST['demo_ref'] ) ? $_REQUEST['demo_ref'] : false;

	// Load AJAX scripts
	wp_register_script( 
		'epdp-ajax',
		$js_dir . 'epdp-ajax' . $suffix . '.js',
		array( 'jquery' ),
		EPD_PREMIUM_VERSION
	);
	wp_enqueue_script( 'epdp-ajax' );

	wp_localize_script( 'epdp-ajax', 'epdp_vars', apply_filters( 'epdp_ajax_script_vars', array(
		'demo_ref'          => $demo_ref,
		'is_register_page'  => epd_is_register_page(),
		'registration_page' => epd_get_registration_page_url()
	) ) );

} // epdp_load_scripts
add_action( 'wp_enqueue_scripts', 'epdp_load_scripts' );

/**
 * Load Admin Styles
 *
 * Enqueues the required admin styles.
 *
 * @since	1.0
 * @param	string	$hook	Page hook
 * @return	void
 */
function epdp_load_admin_styles( $hook ) {

	$assets_dir  = trailingslashit( EPD_PREMIUM_URL . '/assets' );
	$css_dir     = trailingslashit( $assets_dir . 'css' );
	$suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? ''        : '.min';
	$ui_style    = 'classic' == get_user_option( 'admin_color' ) ? 'classic' : 'fresh';

	if ( 'post.php' == $hook || 'post-new.php' == $hook )	{
		if ( isset( $_GET['post'] ) && 'epd_demo' == get_post_type( $_GET['post'] ) )	{
			$ui_style = 'humanity';
		}
	}

	wp_enqueue_style( 'wp-color-picker' );

	wp_register_style(
		'epdp-admin',
		$css_dir . 'epdp-admin' . $suffix . '.css',
		array(),
		EPD_PREMIUM_VERSION
	);
	wp_enqueue_style( 'epdp-admin' );

	wp_register_style(
		'jquery-chosen-css',
		$css_dir . 'chosen' . $suffix . '.css',
		array(),
		EPD_PREMIUM_VERSION
	);
	wp_enqueue_style( 'jquery-chosen-css' );

} // epdp_load_admin_styles
add_action( 'admin_enqueue_scripts', 'epdp_load_admin_styles' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since	1.1
 * @global	$post
 * @param	string	$hook	Page hook
 * @return	void
 */
function epdp_load_admin_scripts( $hook ) {
	global $wp_version, $post;

	$assets_dir  = trailingslashit( EPD_PREMIUM_URL . '/assets' );
	$js_dir      = trailingslashit( $assets_dir . 'js' );
	$suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_script( 'wp-color-picker' );

	wp_register_script(
		'epdp-admin-scripts',
		$js_dir . 'admin-scripts' . $suffix . '.js',
		array( 'jquery' ),
		EPD_PREMIUM_VERSION,
		false
	);
	wp_enqueue_script( 'epdp-admin-scripts' );

    $hide_save_settings = false;

    if ( 'settings_page_epd-settings' == $hook && isset( $_GET['tab'] ) && 'features' == $_GET['tab'] && isset( $_GET['section'] ) && 'notice' == $_GET['section'] )	{
        $hide_save_settings = true;
    }

    wp_localize_script( 'epdp-admin-scripts', 'epdp_admin_vars', array(
        'hide_save_settings' => $hide_save_settings,
		'reveal_phrase'      => __( 'Reveal Secret Key', 'epd-premium' ),
        'please_wait'        => __( 'Please wait...', 'epd-premium' ),
        'regenerate_key'     => __( 'Regenerate Secret Key', 'epd-premium' ),
        'done'               => __( 'Done!', 'epd-premium' ),
        'phrase_desc'        => __( 'Copy this phrase into the site where you have the EPD Remote plugin installed to enable demo site creation via the REST API.', 'epd-premium' )
    ) );

	wp_register_script(
		'jquery-chosen',
		$js_dir . 'chosen.jquery' . $suffix . '.js',
		array( 'jquery' ),
		EPD_PREMIUM_VERSION
	);
	wp_enqueue_script( 'jquery-chosen' );
} // epdp_load_admin_scripts
add_action( 'admin_enqueue_scripts', 'epdp_load_admin_scripts' );
