<?php
/**
 * Demo Functions
 *
 * @package     EPD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Where should the demo button be placed.
 *
 * @since	1.2
 * @return	string	Placement of button
 */
function epdp_get_button_placement()	{
	$placement = epd_get_option( 'button_placement' );
	$placement = apply_filters( 'epdp_button_placement', $placement );

	return $placement;
} // epdp_get_button_placement

/**
 * Get button text.
 *
 * @since	1.2
 * @return	string	Button text
 */
function epdp_get_button_text()	{
	$text = epd_get_option( 'button_text' );
	$text = apply_filters( 'epdp_button_text', $text );

	return $text;
} // epdp_get_button_text

/**
 * Output the demo button.
 *
 * @since	1.2
 * @param	array	$args	Arguments for button display
 * @return	string	Demo button
 */
function epdp_output_demo_button( $args = array() )	{
	global $post, $epdp_button_options;

	ob_start();

	$epdp_button_options = wp_parse_args( $args, epdp_get_demo_button_options() );
	extract( $epdp_button_options );

	$demo_id = ! empty( $demo_id ) ? absint( $demo_id ) : false;
	
	if ( ! $demo_id )	{
		if ( is_object( $post ) )	{
			$demo_id = $post->ID;
		}
	}

	$epdp_button_options['demo_id']  = $demo_id;
	$epdp_button_options['demo_key'] = epdp_get_demo_key( $demo_id );

    do_action( 'epd_pre_demo_button' );

	$button_template = apply_filters( 'epdp_button_template', 'button' );

	epd_get_template_part( 'buttons/demo', $button_template );

	return apply_filters( 'epdp_demo_button', ob_get_clean() );
} // epdp_output_demo_button

/**
 * Get Demo button options.
 *
 * @since 1.2
 * @return	array	Array of options for the demo button
 */
function epdp_get_demo_button_options() {
	global $post;

	$options = apply_filters( 'epdp_demo_button_defaults', array(
		'button_id'   => 'epdp-launch-demo',
		'class'       => 'epdp-submit',
		'color'       => '',
		'demo_id'     => '',
		'demo_key'    => '',
		'register'    => true,
		'style'       => '',
		'text'        => epdp_get_button_text()
	) );

	return $options;
} // epdp_get_demo_button_options
