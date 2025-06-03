<?php
/**
 * Zapier Integration Actions
 *
 * @package     EPD Premium
 * @subpackage  Integrations/Functions
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Log new site registrations.
 *
 * @since   1.5.1
 * @param   int     $site_id    ID of newly registered site
 * @param   int     $user_id    ID of user registering site
 * @param	array	$data       Array of arguments for the new site
 * @return  void
 */
function epdp_zapier_log_site_registration( $site_id, $user_id, $data )   {
    switch_to_blog( get_network()->blog_id );

    $user     = get_userdata( $user_id );
    $url      = get_site_url( $site_id );
    $expires  = epd_get_site_expiration_date( $site_id );
    $expires  = empty( $expires ) ? __( 'Never', 'epd-premium' ) : $expires;
    $zap_data = array(
        'user_id'    => $user_id,
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'email'      => $user->user_email,
        'site_url'   => $url,
        'registered' => epd_get_site_registered_time( $site_id ),
        'lifetime'   => epd_get_site_lifetime( $site_id ),
        'expires'    => $expires
    );

    $zap_data = apply_filters( 'epdp_zapier_log_site_registration_data', $zap_data, $site_id, $user, $data );

    $insert = wp_insert_post( array(
        'post_type'   => 'epd_zap',
        'post_status' => 'publish',
        'post_title'  => $url,
        'meta_input'  => array(
            '_epd_zap_type' => 'registered',
            '_epd_zap_data' => $zap_data
        )
    ) );

	if ( $insert && ! is_wp_error( $insert ) )	{
		do_action( 'epdp_zap_demo_registered', $insert, $zap_data );
	}
	
    restore_current_blog();
} // epdp_zapier_log_site_registration
add_action( 'epd_registration', 'epdp_zapier_log_site_registration', 900, 3 );

/**
 * Log site deletions.
 *
 * @since   1.5.1
 * @param   int     $site_id    ID of newly registered site
 * @return  void
 */
function epdp_zapier_log_site_deletion( $site_id )   {
    switch_to_blog( get_network()->blog_id );

    $user_id  = epd_get_site_primary_user_id( $site_id );
    $user     = get_userdata( $user_id );
    $url      = get_site_url( $site_id );
    $expires  = epd_get_site_expiration_date( $site_id );
    $expires  = empty( $expires ) ? __( 'Never', 'epd-premium' ) : $expires;
    $zap_data = array(
        'user_id'    => $user_id,
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'email'      => $user->user_email,
        'site_url'   => $url,
        'registered' => epd_get_site_registered_time( $site_id ),
        'lifetime'   => epd_get_site_lifetime( $site_id ),
        'expires'    => $expires
    );

    $zap_data = apply_filters( 'epdp_zapier_log_site_deletion_data', $zap_data, $site_id, $user );

    $insert = wp_insert_post( array(
        'post_type'   => 'epd_zap',
        'post_status' => 'publish',
        'post_title'  => $url,
        'meta_input'  => array(
            '_epd_zap_type' => 'deleted',
            '_epd_zap_data' => $zap_data
        )
    ) );

	if ( $insert && ! is_wp_error( $insert ) )	{
		do_action( 'epdp_zap_demo_deleted', $insert, $zap_data );
	}

    restore_current_blog();
} // epdp_zapier_log_site_deletion
add_action( 'epd_before_delete_site', 'epdp_zapier_log_site_deletion', 900 );

/**
 * Log site resets.
 *
 * @since   1.5.1
 * @param   object  $reset    EPD_Reset_Site object
 * @return  void
 */
function epdp_zapier_log_site_reset( $reset )   {
    switch_to_blog( get_network()->blog_id );

    $site_id  = $reset->new_site_id;
    $user_id  = epd_get_site_primary_user_id( $site_id );
    $user     = get_userdata( $user_id );
    $url      = get_site_url( $site_id );
    $expires  = epd_get_site_expiration_date( $site_id );
    $expires  = empty( $expires ) ? __( 'Never', 'epd-premium' ) : $expires;
    $zap_data = array(
        'user_id'    => $user_id,
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'email'      => $user->user_email,
        'site_url'   => $url,
        'registered' => epd_get_site_registered_time( $site_id ),
        'lifetime'   => epd_get_site_lifetime( $site_id ),
        'expires'    => $expires
    );

    $zap_data = apply_filters( 'epdp_zapier_log_site_reset_data', $zap_data, $site_id, $user );

    $insert = wp_insert_post( array(
        'post_type'   => 'epd_zap',
        'post_status' => 'publish',
        'post_title'  => $url,
        'meta_input'  => array(
            '_epd_zap_type' => 'reset',
            '_epd_zap_data' => $zap_data
        )
    ) );

	if ( $insert && ! is_wp_error( $insert ) )	{
		do_action( 'epdp_zap_demo_reset', $insert, $zap_data );
	}

    restore_current_blog();
} // epdp_zapier_log_site_reset
add_action( 'epd_site_reset', 'epdp_zapier_log_site_reset', 900 );
