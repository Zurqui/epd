<?php
/**
 * User Functions
 *
 * @package     EPD Premium
 * @subpackage  Functions/Users
 * @copyright   Copyright (c) 2019, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve user ID's of users to be allocated role on new site.
 *
 * @since   1.0
 * @param   string  $role   The name of the role to fetch users for
 * @return  array   Array of user ID's
 */
function epdp_get_users_for_role( $role_name )  {
    $key   = 'blog_' . $role_name;
    $users = epd_get_option( $key, array() );
    $users = array_map( 'absint', $users );

    return $users;
} // epdp_get_users_for_role
