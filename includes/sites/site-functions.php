<?php
/**
 * Site Functions
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
 * Whether or not to hide the appearance menu.
 *
 * @since	1.2
 * @param	int     $site_id    The site ID
 * @return	bool	True to hide, or false
 */
function epdp_get_hide_appearance_menu( $site_id = null )	{
    $hide = get_site_meta( $site_id, 'epd_hide_appearance_menu', true );
    $hide = ! empty( $hide ) ? true : false;

	return $hide;
} // epdp_get_hide_appearance_menu

/**
 * Whether or not to hide the plugins menu.
 *
 * @since	1.2
 * @param	int     $site_id    The site ID
 * @return	bool	True to hide, or false
 */
function epdp_get_hide_plugins_menu( $site_id = null )	{
    $hide = get_site_meta( $site_id, 'epd_hide_plugins_menu', true );
    $hide = ! empty( $hide ) ? true : false;

    return $hide;
} // epdp_get_hide_plugins_menu

/**
 * Whether or not to hide the discourage search setting.
 *
 * @since	1.2
 * @param	int     	$site_id    The site ID
 * @return	bool|string	Custom welcome text or false not to display
 */
function epdp_get_hide_discourage_search( $site_id = null )	{
    $hide = get_site_meta( $site_id, 'epd_disable_visibility', true );
    $hide = ! empty( $hide ) ? true : false;

    return $hide;
} // epdp_get_hide_discourage_search

/**
 * Whether or not to hide the default welcome panel.
 *
 * @since	1.2
 * @param	int     $site_id    The site ID
 * @return	bool	True to hide, or false
 */
function epdp_get_hide_default_welcome( $site_id = null )	{
    $hide = get_site_meta( $site_id, 'epd_disable_default_welcome', true );
    $hide = ! empty( $hide ) ? true : false;

    return $hide;
} // epdp_get_hide_default_welcome

/**
 * Whether or not to add a custom welcome panel.
 *
 * @since	1.2
 * @param	int     	$site_id    The site ID
 * @return	bool|string	Custom welcome text or false not to display
 */
function epdp_get_custom_welcome( $site_id = null )	{
    $message = get_site_meta( $site_id, 'epd_custom_welcome', true );
    $message = ! empty( $message ) ? $message : false;

    return $message;
} // epdp_get_custom_welcome
