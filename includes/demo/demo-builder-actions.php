<?php
/**
 * Demo Builder Actions
 *
 * This is where we hook into the necessary actions to override default
 * build settings and ensure sites are built from the options defined
 * within the demo post.
 *
 * @package     EPDP
 * @subpackage  Actions/Demo Builder
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Add additional hidden fields to the registration form for sites being created from demo templates.
 *
 * So we can identify the ID of the demo template.
 *
 * @since	1.1
 * @param	array	$fields		Array of hidden fields and values for the form
 * @return	array	Array of hidden fields and values for the form
 */
function epdp_add_registration_template_fields( $fields )	{
	if ( isset( $_REQUEST['demo_ref'] ) )	{
		$demo_ref = $_REQUEST['demo_ref'];

		if ( ! empty( $demo_ref ) )	{
			$fields['demo_key'] = $demo_ref;
		}
	}

	return $fields;
} // epdp_add_registration_template_fields
add_filter( 'epd_registration_hidden_fields', 'epdp_add_registration_template_fields' );

/**
 * If using a demo template for default builds, set the fields.
 *
 * We set a low priority to ensure we can identify if a template was set elsewhere.
 *
 * @since	1.3.1
 * @param	array	$fields		Array of hidden fields and values for the form
 * @return	array	Array of hidden fields and values for the form
 */
function epdp_add_default_demo_registration_field( $fields )	{
	if ( ! isset( $fields['demo_key'] ) )	{
		$default = epdp_get_default_demo_template_id();

		if ( ! empty( $default ) )	{
			$key = epdp_get_demo_key( $default );

			if ( ! empty( $key ) )	{
				$fields['demo_key'] = $key;
			}
		}
	}

	return $fields;
} // epdp_add_default_demo_registration_field
add_filter( 'epd_registration_hidden_fields', 'epdp_add_default_demo_registration_field', 999 );

/**
 * Hook into the registration process to kick of demo template builds
 * using the EPDP_Demo_Builder class.
 *
 * @since   2.0
 * @return  void
 */
function epdp_start_template_build()    {
    $demo_key = isset( $_POST['demo_key'] ) ? $_POST['demo_key'] : false;
	$demo_id  = epdp_get_demo_id_from_key( $demo_key );
	$demo_id  = 'epd_demo' == get_post_type( $demo_id ) ? absint( $demo_id ) : false;

    if ( $demo_id ) {
        new EPDP_Demo_Builder( $demo_id );
    }
} // epdp_start_template_build
add_action( 'epd_before_registration', 'epdp_start_template_build' );

/**
 * Initiate the reset of a site based on a demo template.
 *
 * @since   1.2
 * @param   object  $epd_reset  EPD_Site_Reset object
 * @return  void
 */
function epdp_initiate_restore_of_demo_template_action( $epd_reset )    {
    $demo_id = epdp_get_site_demo_template_id( $epd_reset->site_id );
    $demo_id = 'epd_demo' == get_post_type( $demo_id ) ? $demo_id : false;

    if ( $demo_id ) {
        new EPDP_Demo_Builder( $demo_id );
    }
} // epdp_initiate_restore_of_demo_template_action
add_action( 'epd_before_site_reset', 'epdp_initiate_restore_of_demo_template_action' );

/**
 * Adds the demo site meta to the reset meta.
 *
 * @since	1.2
 * @param	array	$meta		Array of defined site meta for the reset
 * @param	int		$site_id	ID of site being reset
 * @return	array	Array of defined site meta for the reset
 */
function epdp_add_demo_template_id_to_reset_meta_action( $meta, $site_id )	{
	$demo_id = epdp_get_site_demo_template_id( $site_id );

	if ( ! empty( $demo_id ) )	{
		$meta['epd_demo_template'] = $demo_id;
	}

	return $meta;
} // epdp_add_demo_template_id_to_reset_meta_action
add_filter( 'epd_define_site_meta', 'epdp_add_demo_template_id_to_reset_meta_action', 10, 2 );
