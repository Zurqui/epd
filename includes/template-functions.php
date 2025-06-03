<?php
/**
 * Template Functions
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
 * Returns the path to the EPD templates directory
 *
 * @since	1.0
 * @return 	string
 */
function epdp_get_templates_dir() {
	return EPD_PREMIUM_DIR . '/templates';
} // epdp_get_templates_dir

/**
 * Returns the URL to the EPD templates directory
 *
 * @since	1.0
 * @return	string
 */
function epdp_get_templates_url() {
	return EPD_PLUGIN_URL . '/templates';
} // epdp_get_templates_url

/**
 * Add the EPD Premium template path.
 *
 * @since	1.2
 * @param	array	$file_paths		Array of template paths
 * @return	array	Array of template paths
 */
function epdp_add_template_paths( $file_paths )	{
	$file_paths[90] = epdp_get_templates_dir();

	return $file_paths;
} // epdp_add_template_paths
add_filter( 'epd_template_paths', 'epdp_add_template_paths' );

/**
 * Before Demo Content
 *
 * Adds an action to the beginning of demo post content that can be hooked to
 * by other functions.
 *
 * @since   1.1
 * @global  $post
 * @param   string  $content    The the_content field of the demo object
 * @return  string  The content with any additional data attached
 */
function epdp_before_demo_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'epd_demo' && is_singular( 'epd_demo' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'epdp_before_demo_content', $post->ID );
		$content = ob_get_clean() . $content;
	}

	return $content;
} // epdp_before_demo_content
add_filter( 'the_content', 'epdp_before_demo_content' );

/**
 * After Demo Content
 *
 * Adds an action to the end of demo post content that can be hooked to by
 * other functions.
 *
 * @since   1.1
 * @global  $post
 * @param   string  $content    The the_content field of the demo object
 * @return  string  The content with any additional data attached
 */
function epdp_after_demo_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'epd_demo' && is_singular( 'epd_demo' ) && is_main_query() && ! post_password_required() ) {
		ob_start();
		do_action( 'epdp_after_demo_content', $post->ID );
		$content .= ob_get_clean();
	}

	return $content;
} // epdp_after_demo_content
add_filter( 'the_content', 'epdp_after_demo_content' );

/**
 * Adds the Register for Demo button before demo content.
 *
 * @since   1.2
 * @param   int     $demo_id    Demo post ID
 * @return  void
 */
function epdp_before_demo_button( $demo_id )  {
	$placement = epdp_demo_get_button_placement( $demo_id );

	if ( 'before_post' == $placement || 'both' == $placement )	{
		echo epdp_output_demo_button();
	}
} // epdp_before_demo_button
add_action( 'epdp_before_demo_content', 'epdp_before_demo_button' );

/**
 * Adds the Register for Demo button after demo content.
 *
 * @since   1.2
 * @param   int     $demo_id    Demo post ID
 * @return  void
 */
function epdp_after_demo_button( $demo_id )  {
    $placement = epdp_demo_get_button_placement( $demo_id );

	if ( 'after_post' == $placement || 'both' == $placement )	{
		echo epdp_output_demo_button();
	}
} // epdp_after_demo_button
add_action( 'epdp_after_demo_content', 'epdp_after_demo_button' );
