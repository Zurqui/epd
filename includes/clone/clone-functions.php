<?php
/**
 * Site Functions
 *
 * @package     EPD Premium
 * @subpackage  Functions/Clone
 * @copyright   Copyright (c) 2019, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve the ID of the master clone site.
 *
 * @since   1.0
 * @return  int|bool    The ID of the the master clone site, or false if none
 */
function epdp_get_clone_master()   {
    return intval( epd_get_option( 'clone_site_id', 0 ) );
} // epdp_get_clone_master

/**
 * Whether or not it is the clone master.
 *
 * @since   1.0
 * @param   int     The ID of the site being checked
 * @return  bool
 */
function epdp_is_clone_master( $site_id )   {
    return $site_id == epdp_get_clone_master();
} // epdp_is_clone_master

/**
 * Whether or not we're set to clone sites.
 *
 * @return   bool   True if we are cloning sites
 */
function epdp_clone_new_sites() {
    $clone = ! empty( epdp_get_clone_master() ) ? true : false;
    $clone = apply_filters( 'epdp_clone_new_sites', $clone );

    return (bool) $clone;
} // epdp_clone_new_sites

/**
 * Retrieve all clone sites defined in Demo Templates.
 *
 * @since	1.2
 * @return	array	Array of clone site ID's defined within Demo Templates
 */
function epdp_get_demo_clone_site_ids()	{
	global $wpdb;

	switch_to_blog( get_network()->blog_id );

	$clone_ids = array();
	$where    = "WHERE meta_key = '_epdp_clone_site'";
    $where   .= "AND meta_value > '0'";

	$clones = $wpdb->get_results( 
        "
        SELECT meta_value as clone_id
        FROM $wpdb->postmeta
        $where
        "
    );

	foreach( $clones as $clone )    {
        $clone_ids[] = $clone->clone_id;
    }

	restore_current_blog();

	return array_unique( $clone_ids );
} // epdp_get_demo_clone_site_ids

/**
 * Retrieve all clone sites.
 *
 * This function returns the master clone and any clones set via Demo Templates.
 *
 * @since	1.2
 * @return	array	Array of site IDs used as clones
 */
function epdp_get_clone_masters()	{
	$clone_ids    = epdp_get_demo_clone_site_ids();
	$clone_master = epdp_get_clone_master();

	if ( ! empty( $clone_master ) )	{
		$clone_ids[] = $clone_master;
	}

	return array_unique( $clone_ids );
} // epdp_get_clone_masters

/**
 * Whether or not the given site is a clone site.
 *
 * @since	1.2
 * @param	int		$site_id	The site ID to check
 * @return	bool	True if a clone site, otherwise false
 */
function epdp_is_clone_site( $site_id )	{
	$clone_site = in_array( $site_id, epdp_get_clone_masters() );
	$clone_site = apply_filters( 'epdp_is_clone_site', $clone_site, $site_id );

	return $clone_site;
} // epdp_is_clone_site

/**
 * Clone a new site.
 *
 * @since   1.0
 * @param   object     $site    WP_Site site object for new site
 */
function epdp_clone_new_site( $site, $demo_id = 0 )    {
    $cloner = new EPDP_Site_Cloner( $site->blog_id, $demo_id );

    if ( ! $cloner->cloner_ready() )  {
        return false;
    }

    return $cloner->execute_clone();
} // epdp_clone_new_site

/**
 * If we are cloning sites, reset the required actions.
 *
 * - Remove activate plugins action
 * - Remove new post creation action
 * - Add cloner action
 */
function epdp_define_new_site_actions_action()  {
    if ( epdp_clone_new_sites() )   {
        remove_action( 'wp_initialize_site', 'epd_activate_new_blog_plugins', 11 );
        remove_action( 'epd_create_demo_site', 'epd_create_new_blog_posts_pages_action', 20, 2 );

        add_action( 'wp_initialize_site', 'epdp_clone_new_site', 50 );
    }
} // epdp_define_new_site_actions_action
add_action( 'wp_insert_site', 'epdp_define_new_site_actions_action', 1 );

/**
 * Retrieve custom options for exclusion.
 *
 * @since   1.0
 * @param	int		$site_id	ID of site to retrieve exclusion options for
 * @return  array   Array of option column names to exclude from cloning.
 */
function epdp_get_custom_option_exclusions( $site_id = 0 )    {
    $custom_exclusions = ! empty( $site_id ) ? array() : epd_get_option( 'exclude_options' );

    if ( ! empty( $custom_exclusions ) )   {
        $custom_exclusions = array_map( 'trim', explode( "\n", $custom_exclusions ) );
		$custom_exclusions = array_unique( $custom_exclusions );
		$custom_exclusions = array_map( 'sanitize_text_field', $custom_exclusions );
    } else {
        $custom_exclusions = array();
    }

    return $custom_exclusions;
} // epdp_get_custom_option_exclusions

/**
 * Add values from the exclude options list to the excluded columns list.
 *
 * @since   1.0
 * @param   array   $exclusions Array of columns being excluded
 * @param	int		$site_id	ID of site to get exclusions for
 * @return  array   Array of option columns to exclude during cloning
 */
function epdp_get_excluded_custom_option_columns_action( $exclusions, $site_id = 0 )  {
    $custom_exclusions = epdp_get_custom_option_exclusions( $site_id );

    $exclusions = array_merge( $exclusions, $custom_exclusions );
    $exclusions = array_unique( $exclusions );

    return $exclusions;
} // epdp_get_excluded_custom_option_columns_action
add_filter( 'epdp_replication_options_exclusions', 'epdp_get_excluded_custom_option_columns_action', 10, 2 );
