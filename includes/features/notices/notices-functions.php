<?php
/**
 * Notices Functions
 *
 * @package     EPD Premium
 * @subpackage  Functions/Features/Notices
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve all notices.
 *
 * @since   1.4
 * @param   bool    $active True to only retrieve active notices
 * @return  arr     Array of notice notices
 */
function epdp_get_notices( $active = false ) {
    switch_to_blog( get_network()->blog_id );

    $notices = get_site_option( 'epdp_notices', array() );

    if ( $active )  {
        foreach( $notices as $notice_id => $notice )    {
            if ( empty( $notice['active'] ) ) {
                unset( $notices[ $notice_id ] );
            }
        }
    }

    $notices = apply_filters( 'epdp_notices', $notices, $active );

    restore_current_blog();

    return $notices;
} // epdp_get_notices

/**
 * Retrieve a notice.
 *
 * @since	1.4
 * @param	int		$notice_id	Notice ID
 * @return	array	Notice data array
 */
function epdp_get_notice( $notice_id )	{
	$notices = epdp_get_notices();
	$notice  = array_key_exists( $notice_id, $notices ) ? $notices[ $notice_id ] : false;

	return apply_filters( 'epdp_notice', $notice, $notice_id, $notices );
} // epdp_get_notice

/**
 * Set the value of a field (or fields) for a notice.
 *
 * @since   1.4
 * @param	int		$notice_id	Notice ID
 * @param	array	$updates	An array of key => values to update
 * @return	bool
 */
function epdp_update_notice_key( $notice_id, $updates )	{
	$notices = epdp_get_notices();

	if ( empty( $updates ) || ! is_array( $updates ) )	{
		return false;
	}

	foreach( $updates as $key => $value )	{
		$notices[ $notice_id ][ $key ] = $value;
	}

    switch_to_blog( get_network()->blog_id );
	$update = update_site_option( 'epdp_notices', $notices );
    restore_current_blog();

    return $update;
} // epdp_update_notice_key

/**
 * Retrieve a notice field value.
 *
 * @since	1.4
 * @param	int		$notice_id	Notice ID
 * @param   string  $field      The field to retrieve
 * @return	mixed	The value of the notice field
 */
function epdp_get_notice_field( $notice_id, $field = 'name' )	{
    $func = 'epdp_get_notice_' . $field;

    if ( function_exists( $func ) )   {
        return $func( $notice_id );
    } else  {
        return false;
    }
} // epdp_get_notice_field

/**
 * Retrieve an notice name.
 *
 * @since	1.4
 * @param	int      $notice_id Notice ID
 * @param   array    $notices   Optional: Array of all notices
 * @return	string   The name of the notice
 */
function epdp_get_notice_name( $notice_id, $notices = array() )	{
    if ( empty( $notices ) )    {
        $notice = epdp_get_notice( $notice_id );
    } else  {
        $notice = $notices[ $notice_id ];
    }

    $name = __( 'Notice not found', 'epd-premium' );

    if ( $notice && ! empty( $notice['name'] ) )    {
        $name = esc_html( stripslashes( $notice['name'] ) );
    }

	return apply_filters( 'epdp_notice_name', $name, $notice_id );
} // epdp_get_notice_name

/**
 * Retrieve notice display count.
 *
 * @since	1.4
 * @param	int      $notice_id Notice ID
 * @param   array    $notices   Optional: Array of all notices
 * @return	string   The name of the notice
 */
function epdp_get_notice_displayed( $notice_id, $notices = array() )	{
    if ( empty( $notices ) )    {
        $notice = epdp_get_notice( $notice_id );
    } else  {
        $notice = $notices[ $notice_id ];
    }

    $displays = isset( $notice['displayed'] ) ? absint( $notice['displayed'] ) : 0;

    return apply_filters( 'epdp_notice_display_count', $displays, $notice_id );
} // epdp_get_notice_displayed

/**
 * Gets notices for a site.
 *
 * @since	1.4
 * @param	int		$site_id	Site ID
 * @return	void
 */
function epdp_get_site_notices( $site_id )	{
	$notices = get_site_meta( $site_id, 'epdp_site_notices', true );
	$notices = ! empty( $notices )  ? $notices : array();
	$notices = is_array( $notices ) ? $notices : array( $notices );

	return $notices;
} // epdp_get_site_notices

/**
 * Adds a notice for a site.
 *
 * @since	1.4
 * @param	int		$site_id	Site ID
 * @param	string	$slug		Slug of notice to add
 * @return	void
 */
function epdp_add_notice_for_site( $site_id, $slug )	{
	$notices = epdp_get_site_notices( $site_id );

	$notices[] = $slug;
	$notices   = array_unique( $notices );

	update_site_meta( $site_id, 'epdp_site_notices', $notices );
} // epdp_add_notice_for_site

/**
 * Removes a notice from a site.
 *
 * @since	1.4
 * @param	int		$site_id	Site ID
 * @param	string	$slug		Slug of notice to remove
 * @return	void
 */
function epdp_remove_notice_from_site( $site_id, $slug )	{
	$notices = epdp_get_site_notices( $site_id );

	foreach( $notices as $id => $notice )	{
		if ( $slug == $notice )	{
			unset( $notices[ $id ] );
		}
	}

	update_site_meta( $site_id, 'epdp_site_notices', $notices );
} // epdp_remove_notice_from_site

/**
 * Increment notice display count.
 *
 * @since   1.4
 * @param   int     $notice_id   Notice ID
 * @return  int     Updated display count
 */
function epdp_increment_notice_display_count( $notice_id )  {
    $notices = epdp_get_notices();
    $current = epdp_get_notice_displayed( $notice_id, $notices );

    $notices[ $notice_id ]['displayed'] = $current + 1;

    switch_to_blog( get_network()->blog_id );
    update_site_option( 'epdp_notices', $notices );
    restore_current_blog();

    return epdp_get_notice_displayed( $notice_id, $notices );
} // epdp_increment_notice_display_count
