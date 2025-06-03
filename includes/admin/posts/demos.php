<?php
/**
 * Demo Posts
 *
 * @package     EPD Premium
 * @subpackage  Admin/Functions/Posts
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Define the columns that should be displayed for the Demo post lists screen
 *
 * @since	1.2
 * @param	array	$columns	An array of column name ⇒ label. The label is shown as the column header.
 * @return	array	$columns	Filtered array of column name => label to be shown as the column header.
 */
function epdp_set_epd_demo_post_columns( $columns ) {
	$columns = array(
        'cb'        => '<input type="checkbox" />',
		'title'     => __( 'Title', 'epd-premium' ),
        'count'     => __( 'Build Count', 'epd-premium' ),
		'shortcode' => __( 'Shortcode', 'epd-premium' ),
		'author'    => __( 'Author', 'epd-premium' ),
		'date'      => __( 'Date', 'epd-premium' )
    );
	
	return apply_filters( 'epd_demo_post_columns', $columns );
} // epdp_set_epd_demo_post_columns
add_filter( 'manage_epd_demo_posts_columns' , 'epdp_set_epd_demo_post_columns' );

/**
 * Allow sorting by the build column.
 *
 * @since   1.5.2
 * @param   array   $columns    Array of sortable columns
 * @return  array   Array of sortable columns
 */
function epdp_set_demo_sortable_columns( $columns ) {
    $columns['count'] = 'build_count';

    return $columns;
} // epdp_set_demo_sortable_columns
add_filter( 'manage_edit-epd_demo_sortable_columns', 'epdp_set_demo_sortable_columns' );

/**
 * Define the data to be displayed within the EPD Demo post custom columns.
 *
 * @since	1.2
 * @param	string	$column_name	The name of the current column for which data should be displayed.
 * @param	int		$post_id		The ID of the current post for which data is being displayed.
 * @return	string	Column output
 */
function epdp_set_epd_demo_column_data( $column_name, $post_id ) {
	switch( $column_name )	{
        case 'count':
            echo epdp_get_template_build_count( $post_id );
            break;
		case 'shortcode':
			echo '<input type=\'text\' class="epdp_copy_shortcode" value=\'[epd_button demo_id="' . $post_id . '"]\' readonly />';
			break;
	}
} // epdp_set_epd_demo_column_data
add_action( 'manage_epd_demo_posts_custom_column' , 'epdp_set_epd_demo_column_data', 10, 2 );

/**
 * Order demos by build count.
 *
 * @since   1.5.2
 * @param	object	$query		The WP_Query object
 * @return	void
 */
function epdp_order_demos_admin( $query )	{
	if ( ! is_admin() || 'epd_demo' != $query->get( 'post_type' ) )	{
		return;
	}

    if ( ! empty( $_REQUEST['orderby'] ) && 'build_count' == $_REQUEST['orderby'] ) {
        $query->set( 'meta_key', '_epd_build_count' );
        $query->set( 'orderby', 'meta_value_num' );
    }
} // epdp_order_demos_admin
add_action( 'pre_get_posts', 'epdp_order_demos_admin' );

/**
 * Save the EPD Demo custom posts
 *
 * @since	1.1
 * @param	int		$post_id		The ID of the post being saved.
 * @param	object	$post			The WP_Post object of the post being saved.
 * @param	bool	$update			Whether an existing post if being updated or not.
 * @return	void
 */
function epd_demo_post_save( $post_id, $post, $update )	{	

	if ( ! isset( $_POST['epd_demo_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['epd_demo_meta_box_nonce'], 'epd_demo_meta_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	{
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

    remove_action( 'save_post_epd_demo', 'epd_demo_post_save', 10, 3 );

	if ( isset( $_POST['_epd_site_ref'] ) )	{
		add_post_meta( $post_id, '_epd_site_ref', epdp_create_demo_key(), true );
	}

	// The default fields that get saved
	$fields = epd_demo_metabox_fields();
	$saving = array();

	foreach ( $fields as $field => $default )	{
		$posted_value = '';

		if ( ! empty( $_POST[ $field ] ) ) {
            $posted_value = $_POST[ $field ];

            if ( '_epdp_redirect_page' == $field && 'redirect' != $_POST['_epdp_registration_action'] ) {
                $posted_value = $default;
            }

			if ( '_epdp_clone_plugins_action' == $field || '_epdp_clone_themes_action' == $field )	{
				if ( empty( $_POST['_epdp_clone_site'] ) )	{
					$posted_value = $default;
				}
			}

		} else	{
            if ( '_epdp_site_title' == $field ) {
                $posted_value = get_the_title( $post );
            } else  {
                $posted_value = $default;
            }
		}

		$new_value = apply_filters( 'epd_demo_metabox_save_' . $field, $posted_value );

		$saving[ $field ] = $new_value;
	}

	if ( ! empty( $saving['_epdp_hide_appearance_menu'] ) )	{
		$saving['_epdp_available_themes'] = $fields['_epdp_available_themes'];
	}

	foreach( $saving as $meta_key => $meta_value )	{
		if ( '' != $meta_value )	{
			update_post_meta( $post_id, $meta_key, $meta_value );
		} else	{
			delete_post_meta( $post_id, $meta_key );
		}
	}

	add_action( 'save_post_epd_demo', 'epd_demo_post_save', 10, 3 );
} // epd_demo_post_save
add_action( 'save_post_epd_demo', 'epd_demo_post_save', 10, 3 );

/**
 * Sanitize plugins on save
 *
 * @since	1.2
 * @param	array	$plugins	Array of plugins
 * @return	array	Array of plugins
 */
function epdp_sanitize_plugins_save( $plugins = array() ) {
	return array_values( array_unique( $plugins ) );
} // epdp_sanitize_plugins_save
add_filter( 'epd_demo_metabox_save__epdp_plugins', 'epdp_sanitize_plugins_save' );
