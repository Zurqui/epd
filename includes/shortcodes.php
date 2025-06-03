<?php
/**
 * Shortcode Functions
 *
 * @package     EPD Premium
 * @subpackage  Functions/Shortcodes
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Register the EPD Button shortcode.
 *
 * @since   1.2
 * @param	array	$atts	Shortcode attributes
 * @return  string  The demo button output
 */
function epdp_add_epd_button_shortcode( $atts )   {
	$supported_atts = apply_filters(
		'epd_button_shortcode_atts',
		epdp_get_demo_button_options()
	);

    $args = shortcode_atts( $supported_atts, $atts, 'epd_button' );

    do_action( 'epd_button' );

    return epdp_output_demo_button( $args );
} // epdp_add_epd_button_shortcode
add_shortcode( 'epd_button', 'epdp_add_epd_button_shortcode' );
