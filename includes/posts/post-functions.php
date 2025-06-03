<?php
/**
 * Post Functions
 *
 * @package     EPD Premium
 * @subpackage  Functions/Posts
 * @copyright   Copyright (c) 2019, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve posts or pages that need creating on new blog.
 *
 * @since	1.1
 * @return	string|array	Array of post or page ID's to replicateor 'all' if all posts
 */
function epdp_posts_to_create( $type = 'post' )	{
	$option    = 'replicate_all_' . $type;
    $post_ids  = epd_get_option( $option, false );

    if ( $post_ids )  {
        $post_ids = 'all';
    } else  {
        $post_ids = epd_get_option( $option, array() );
    }

	return $post_ids;
} // epdp_posts_to_create

/**
 * Duplicate all posts from the primary blog of the given post type for the new site.
 *
 * Include terms and taxonomies.
 *
 * @since   1.0
 * @param   int     $blog_id    The new blog ID
 * @param   string  $post_type  The post type we're duplicating
 */
function epdp_create_blog_posts_for_post_type( $blog_id, $post_type )   {
    $done  = 0;

    switch_to_blog( get_network()->blog_id );

    $old_posts = get_posts( array(
        'post_type'      => $post_type,
        'posts_per_page' => epd_max_number_of_posts_to_create(),
        'post_status'    => 'any',
        'orderby'        => 'ID',
        'order'          => 'ASC'
    ) );

    do_action( 'epdp_before_create_default_blog_posts', $blog_id, $post_type, $old_posts );

	restore_current_blog();

    foreach( $old_posts as $old_post )  {
        switch_to_blog( $blog_id );
        $args = array(
            'comment_status' => $old_post->comment_status,
            'ping_status'    => $old_post->ping_status,
            'post_author'    => epd_set_author_for_post( $old_post ),
            'post_content'   => $old_post->post_content,
            'post_excerpt'   => $old_post->post_excerpt,
            'post_name'      => $old_post->post_name,
            'post_parent'    => $old_post->post_parent,
            'post_password'  => $old_post->post_password,
            'post_status'    => $old_post->post_status,
            'post_title'     => $old_post->post_title,
            'post_type'      => $old_post->post_type,
            'to_ping'        => $old_post->to_ping,
            'menu_order'     => $old_post->menu_order
        );

        do_action( 'epdp_before_creating_default_blog_post', $blog_id, $post_type, $old_post->ID );

        $new_post_id = wp_insert_post( $args, true );

        if ( ! is_wp_error( $new_post_id ) )	{
            $done++;
        } else	{
            error_log( $new_post_id->get_error_message() );
        }

        do_action( 'epdp_create_default_blog_post', $blog_id, $new_post_id, $post_type, $old_post->ID );
        restore_current_blog();
    }

	return $done;
} // epdp_create_blog_posts_for_post_type

/**
 * Hook into post creation to replicate post meta.
 *
 * @since	1.0
 * @param	int		$blog_id		The ID of the new blog
 * @param	int		$post_id		ID of the post created
 * @param	string	$post_type		The post type
 * @param	int		$old_post_id	ID of the old post
 * @return	void
 */
function epdp_create_replica_post_meta_action( $blog_id, $post_id, $post_type, $old_post_id )	{
	 epd_create_replica_post_meta( $blog_id, $post_id, $old_post_id );
} // epd_create_replica_post_meta_action
add_action( 'epdp_create_default_blog_post', 'epd_create_replica_post_meta_action', 10, 4 );

/**
 * Hook into post creation to replicate taxonomies.
 *
 * @since	1.0
 * @param	int		$blog_id		The ID of the new blog
 * @param	int		$post_id		ID of the post created
 * @param	string	$post_type		The post type
 * @param	int		$old_post_id	ID of the old post
 * @return	void
 */
function epdp_create_replica_post_taxonomies_action( $blog_id, $post_id, $post_type, $old_post_id )	{

	$key = "replicate_tax_{$post_type}";

	if ( ! epd_get_option( $key, false ) )	{
		return;
	}

	switch_to_blog( get_network()->blog_id );

	$taxonomies = get_object_taxonomies( $post_type );
	$terms      = array();

	foreach( $taxonomies as $taxonomy ) {
		$terms[ $taxonomy ] = wp_get_object_terms( $old_post_id, $taxonomy, array( 'fields' => 'slugs' ) );
	}

	restore_current_blog();

	if ( ! empty( $terms ) )    {
        switch_to_blog( $blog_id );
		foreach( $terms as $taxonomy => $post_terms )    {
            if ( empty( $post_terms ) ) {
                continue;
            }

			wp_set_object_terms( $post_id, $post_terms, $taxonomy, false );
		}
        restore_current_blog();
	}

} // epdp_create_replica_post_taxonomies_action
add_action( 'epdp_create_default_blog_post', 'epdp_create_replica_post_taxonomies_action', 15, 4 );

/**
 * Hook into post creation to process comments.
 *
 * @since	1.0
 * @param	int		$blog_id		The ID of the new blog
 * @param	int		$post_id		ID of the post created
 * @param	string	$post_type		The post type
 * @param	int		$old_post_id	ID of the old post
 * @return	void
 */
function epdp_create_replica_post_comments_action( $blog_id, $post_id, $post_type, $old_post_id )	{
	$key = "replicate_comments_{$post_type}";

	if ( ! epd_get_option( $key, false ) )	{
		return;
	}

	switch_to_blog( get_network()->blog_id );
	$comments = get_comments( array(
		'post_id' => $old_post_id,
		'orderby' => 'comment_date_gmt',
		'order'   => 'ASC'
	) );
	restore_current_blog();

	foreach( $comments as $comment )	{
		$comment_meta = get_comment_meta( $comment->ID );
		switch_to_blog( $blog_id );

		$comment_args = array(
			'comment_agent'        => $comment->comment_agent,
			'comment_approved'     => $comment->comment_approved,
			'comment_author'       => $comment->comment_author,
			'comment_author_email' => $comment->comment_author_email,
			'comment_author_IP'    => $comment->comment_author_IP,
			'comment_author_url'   => $comment->comment_author_url,
			'comment_content'      => $comment->comment_content,
			'comment_post_ID'      => $post_id
		);

		$comment_id = wp_insert_comment( $comment_args );

		if ( ! empty( $comment_meta ) )	{
			foreach ( $comment_meta as $key => $values )	{
				foreach ( $values as $value )	{
					add_comment_meta( $comment_id, $key, $value );
				}
			}
		}

		restore_current_blog();
	}

} // epdp_create_replica_post_comments_action
add_action( 'epdp_create_default_blog_post', 'epdp_create_replica_post_comments_action', 20, 4 );

/**
 * Hook into post creation to process attachments.
 *
 * @since	1.0
 * @param	int		$blog_id		The ID of the new blog
 * @param	int		$post_id		ID of the post created
 * @param	string	$post_type		The post type
 * @param	int		$old_post_id	ID of the old post
 * @return	void
 */
function epdp_create_replica_post_attachments_action( $blog_id, $post_id, $post_type, $old_post_id )	{
	$key = "replicate_media_{$post_type}";

	if ( ! epd_get_option( $key, false ) )	{
		return;
	}

	switch_to_blog( get_network()->blog_id );

	$attachments = get_attached_media( '', $old_post_id );

	if ( empty( $attachments ) )	{
		return;
	}

	foreach( $attachments as $attachment )	{
		$attachment_url = wp_get_attachment_image_src( $attachment->ID, 'full' );
		$attachment_url = $attachment_url[0];
	}

	restore_current_blog();
    switch_to_blog( $blog_id );

	$upload_dir      = wp_upload_dir();
    $attachment_data = file_get_contents( $attachment_url );
    $filename        = basename( $attachment_url );

	if ( wp_mkdir_p( $upload_dir['path'] ) )	{
        $file = $upload_dir['path'] . '/' . $filename;
    } else	{
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

	file_put_contents( $file, $attachment_data );
	$wp_filetype = wp_check_filetype( $filename, null );

	$args = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name( $filename ),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

	$attach_id = wp_insert_attachment( $args, $file, $post_id );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	$meta_data = wp_generate_attachment_metadata( $attach_id, $file );
	wp_update_attachment_metadata( $attach_id, $meta_data );

    restore_current_blog();
} // epdp_create_replica_post_attachments_action
add_action( 'epdp_create_default_blog_post', 'epdp_create_replica_post_attachments_action', 25, 4 );

/**
 * Remove the EPD action that creates the posts.
 *
 * We will call this manually later if needed.
 *
 * @since   1.0
 * @param	int		$blog_id	The new blog ID
 * @param	array	$args		Array of arguments used whilst creating the blog
 * @return  void
 */
function epdp_remove_epd_create_new_blog_posts_pages_action( $blog_id, $args )   {
    remove_action( 'epd_create_demo_site', 'epd_create_new_blog_posts_pages_action', 20 );

	$post_types = epd_get_supported_post_types();

	foreach( $post_types as $post_type )	{
		epdp_create_new_blog_posts_pages_action( $blog_id, $post_type );
	}
}
add_action( 'epd_create_demo_site', 'epdp_remove_epd_create_new_blog_posts_pages_action', 19, 2 );

/**
 * Determine which function to call when duplicating posts.
 *
 * @since   1.0
 * @param   int     $blog_id    The new blog ID
 * @param   string  $post_type  The post type we're duplicating
 */
function epdp_create_new_blog_posts_pages_action( $blog_id, $post_type = 'post' )   {
    if ( ! epd_post_type_is_supported( $post_type ) )	{
		return;
	}

    $post_ids = epdp_posts_to_create( $post_type );

    if ( 'all' != $post_ids )    {
        return epd_create_default_blog_posts( $blog_id, $post_type );
    }

    return epdp_create_blog_posts_for_post_type( $blog_id, $post_type );
} // epdp_create_new_blog_posts_pages_action

/**
 * Retrieve the author.
 *
 * @since   1.0
 * @return  string|int  'current', 'blog_owner', or the ID of a user
 */
function epdp_set_author_for_post_action( $author, $post )   {
    $set_author = epd_get_option( 'set_author', 'current' );

    if ( 'current' == $set_author )  {
        $author = $post->post_author;
    } elseif ( 'blog_owner' == $set_author )    {
        $author = get_current_user_id();
    } else  {
        $author = absint( $set_author );
    }

    $author = ! empty( $author ) ? $author : get_current_user_id();

    return $author;
} // epdp_set_author_for_post_action
add_filter( 'epd_set_author_for_post', 'epdp_set_author_for_post_action', 10, 2 );
