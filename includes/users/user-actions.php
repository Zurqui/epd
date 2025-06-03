<?php
/**
 * User Actions
 *
 * @package     EPD Premium
 * @subpackage  Functions/Users
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Hook into post creation to add users.
 *
 * @since	1.0
 * @param	int		$blog_id		The ID of the new blog
 * @param	int		$post_id		ID of the post created
 * @param	string	$post_type		The post type
 * @param	int		$old_post_id	ID of the old post
 * @return	void
 */
function epdp_add_users_to_blog_action( $blog_id, $post_id, $post_type, $old_post_id )	{
    $roles = get_editable_roles();

    foreach( $roles as $role_name => $role_data )   {
        if ( 'super_admin' == $role_name )  {
            continue;
        }

        $user_ids = epdp_get_users_for_role( $role_name );

        if ( ! empty( $user_ids ) )    {
            foreach( $user_ids as $user_id )    {
                add_user_to_blog( $blog_id, $user_id, $role_name );
            }
        }
    }

} // epdp_add_users_to_blog_action
add_action( 'epdp_create_default_blog_post', 'epdp_add_users_to_blog_action', 9, 4 );
