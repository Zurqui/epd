<?php
/**
 * Demo Functions
 *
 * @package     EPD Premium
 * @subpackage  Functions
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove clone blogs from front end sites listing.
 *
 * @since   1.3
 * @param   array   $sites  Array of user sites
 * @return  array   Array of user sites
 */
function epdp_exclude_clone_sites_from_front( $sites )  {
    $clone_ids = epdp_get_clone_masters();

    if ( ! empty( $clone_ids ) && ! is_admin() )    {
		foreach( $clone_ids as $clone_id )	{
			if ( array_key_exists( $clone_id, $sites ) )	{
				unset( $sites[ $clone_id ] );
			}
		}
    }
    
    return $sites;
} // epdp_exclude_clone_sites_from_front
add_filter( 'get_blogs_of_user', 'epdp_exclude_clone_sites_from_front', 100 );

/**
 * Instantiate the EPDP_Site class for sites that are deployed from Demos.
 *
 * The EPDP_Sites class controls features of the site.
 *
 * @since	1.2
 * @return	void
 */
function epdp_manage_template_site()    {
    $site_id  = get_current_blog_id();
    $template = epdp_get_site_demo_template_id( $site_id );
    $managed  = false;

    if ( ! empty( $template ) )  {
        switch_to_blog( get_network()->blog_id );
        if ( 'epd_demo' == get_post_type( $template ) ) {
            $managed = true;
        }
        restore_current_blog();
    }

    if ( $managed ) {
        new EPDP_Site( $site_id );
    }
} // epdp_manage_template_site
add_action( 'wp_loaded', 'epdp_manage_template_site' );

/**
 * Set the registration action for demo sites that need activating.
 *
 * @since	1.4.1
 * @param	string	$action		The action to be taken
 * @param	int		$site_id	The site ID registered
 * @return	string	The action to be taken
 */
function epdp_set_demo_registration_action_on_activate( $action, $site_id )	{
	$demo_id = epdp_get_site_demo_template_id( $site_id );

	if ( ! empty( $demo_id ) && 'epd_demo' == get_post_type( $demo_id ) )	{
		$action = epdp_get_demo_registration_action( $demo_id );
	}

	return $action;
} // epdp_set_demo_registration_action_on_activate
add_filter( 'epd_after_user_registration_action', 'epdp_set_demo_registration_action_on_activate', 900, 2 );

/**
 * Set the redirection page.
 *
 * @since	1.4.1
 * @param	int		$url		The URL of the page to redirect to
 * @param   int     $site_id    The site ID
 * @return	int		The URL of the page to redirect to
 */
function epdp_set_demo_redirection_page_on_activate( $url, $site_id )	{
	$demo_id  = epdp_get_site_demo_template_id( $site_id );

	if ( ! empty( $demo_id ) && 'epd_demo' == get_post_type( $demo_id ) )	{
		$redirect = epdp_get_demo_registration_action( $demo_id );
		$url      = ! empty( $redirect ) ? get_permalink( $redirect ) : $url;
	}

	return $url;
} // epdp_set_demo_redirection_page_on_activate
add_filter( 'epd_after_registration_redirect_url', 'epdp_set_demo_redirection_page_on_activate', 900, 2 );

/**
 * Edit a sites expiration date/time.
 *
 * @since   1.2
 * @return  void
 */
function epdp_edit_expiration_date_action() {
    if ( ! isset( $_REQUEST['epd_action'] ) || 'edit_site' != $_REQUEST['epd_action'] )	{
		return;
	}

	if ( ! isset( $_REQUEST['epd_nonce'] ) || ! wp_verify_nonce( $_REQUEST['epd_nonce'], 'edit_site' ) )	{
		return;
	}

    if ( empty( absint( $_REQUEST['site_id'] ) ) )    {
        return;
    }

    $site_id = absint( $_REQUEST['site_id'] );
    $confirm = 'success';

    if ( ! empty( $_REQUEST['epd_demo_expires'] ) ) {
        $date = strtotime( $_REQUEST['epd_demo_expires'] );
        if ( false === $date )  {
            $date    = epd_get_site_expiration_timestamp( $site_id );
            $confirm = 'error';
        }
    } else  {
        $date = 0;
    }

    do_action( 'epd_before_site_expiry_edit', $site_id );

    update_site_meta( $site_id, 'epd_site_expires', $date );

    do_action( 'epd_before_site_expiry_edit', $site_id );

    $url = network_admin_url( 'site-settings.php' );
    $url = add_query_arg( array(
        'page'        => 'epd-edit-site',
        'id'          => $site_id,
        'epd-message' => "edit-{$confirm}",
        ), $url );

    wp_safe_redirect( $url );
    exit;

} // epdp_edit_expiration_date_action
add_action( 'admin_init', 'epdp_edit_expiration_date_action' );

/**
 * Exclude clone masters from auto deletion.
 *
 * @since	1.3
 * @param	array	$excludes	Array of site ID's to exclude
 * @return	array	Array of site ID's to exclude
 */
function epdp_exclude_clone_masters_from_delete( $excludes )	{
	$clone_masters = epdp_get_clone_masters();

	if ( ! empty( $clone_masters ) )	{
		foreach( $clone_masters as $clone_master )	{
			$excludes[] = $clone_master;
		}
	}

	return $excludes;
} // epdp_exclude_clone_masters_from_delete
add_filter( 'epd_delete_expired_sites_exclusions', 'epdp_exclude_clone_masters_from_delete' );
