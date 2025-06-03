<?php
/**
 * Post Types
 *
 * @package     EPD Premium
 * @subpackage  Functions/Posts
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Supported post types.
 *
 * Supported post types can have the demo button output.
 *
 * @since	1.2
 * @return	array	Array of supported post types
 */
function epdp_supported_post_types()	{
	$post_types = array( 'epd_demo' );
	$post_types = apply_filters( 'epdp_supported_post_types', $post_types );

	return $post_types;
} // epdp_supported_post_types

/**
 * Whether or not the given post type is supported.
 *
 * @since	1.2
 * @param	string	$post_type	The post type to check
 * @return	bool	True|False
 */
function epdp_is_post_type_supported( $post_type )	{
	return in_array( $post_type, epdp_supported_post_types() );
} // epdp_is_post_type_supported

/**
 * Registers and sets up the Demos custom post type
 *
 * @since   1.1
 * @return  void
 */
function epdp_setup_epdp_post_types() {
    if ( ! is_main_site() ) {
        return;
    }

	$archives = defined( 'EPDP_DISABLE_ARCHIVE' ) && EPDP_DISABLE_ARCHIVE ? false : true;
	$slug     = defined( 'EPDP_SLUG' ) ? EPDP_SLUG : 'demos';
	$rewrite  = defined( 'EPDP_DISABLE_REWRITE' ) && EPDP_DISABLE_REWRITE ? false : array(
        'slug'       => $slug,
        'with_front' => false,
    );

	$labels = apply_filters( 'epdp_demo_labels', array(
		'name'                  => _x( '%2$s', 'demo post type name', 'epd-premium' ),
		'singular_name'         => _x( '%1$s', 'singular demo post type name', 'epd-premium' ),
		'add_new'               => __( 'Add New', 'epd-premium' ),
		'add_new_item'          => __( 'Add New %1$s', 'epd-premium' ),
		'edit_item'             => __( 'Edit %1$s', 'epd-premium' ),
		'new_item'              => __( 'New %1$s', 'epd-premium' ),
		'all_items'             => __( '%2$s', 'epd-premium' ),
		'view_item'             => __( 'View %1$s', 'epd-premium' ),
		'search_items'          => __( 'Search %2$s', 'epd-premium' ),
		'not_found'             => __( 'No %2$s found', 'epd-premium' ),
		'not_found_in_trash'    => __( 'No %2$s found in Trash', 'epd-premium' ),
		'parent_item_colon'     => '',
		'menu_name'             => _x( '%2$s', 'demo post type menu name', 'epd-premium' ),
		'featured_image'        => __( '%1$s Image', 'epd-premium' ),
		'set_featured_image'    => __( 'Set %1$s Image', 'epd-premium' ),
		'remove_featured_image' => __( 'Remove %1$s Image', 'epd-premium' ),
		'use_featured_image'    => __( 'Use as %1$s Image', 'epd-premium' ),
		'attributes'            => __( '%1$s Attributes', 'epd-premium' ),
		'filter_items_list'     => __( 'Filter %2$s list', 'epd-premium' ),
		'items_list_navigation' => __( '%2$s list navigation', 'epd-premium' ),
		'items_list'            => __( '%2$s list', 'epd-premium' ),
	) );

	foreach ( $labels as $key => $value ) {
		$labels[ $key ] = sprintf( $value, epdp_get_label_singular(), epdp_get_label_plural() );
	}

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'menu_icon'          => 'dashicons-feedback',
		'rewrite'            => $rewrite,
		'map_meta_cap'       => true,
		'has_archive'        => $archives,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'epdp_demo_supports', array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author', 'custom-fields' ) ),
        'show_in_rest'       => true,
        'rest_base'          => 'demos'
	);
	register_post_type( 'epd_demo', apply_filters( 'epdp_demo_post_type_args', $args ) );
} // epdp_setup_epdp_post_types
add_action( 'init', 'epdp_setup_epdp_post_types', 1 );

/**
 * Get default labels.
 *
 * @since   1.1
 * @return  array   Default labels
 */
function epdp_get_default_labels() {
	$defaults = array(
		'singular' => __( 'Demo', 'epd-premium' ),
		'plural'   => __( 'Demos', 'epd-premium' ),
	);

	return apply_filters( 'epdp_default_demos_name', $defaults );
} // epdp_get_default_labels

/**
 * Get singular label.
 *
 * @since   1.1
 * @param   bool    $lowercase  Optional. Default false.
 * @return  string  Singular label.
 */
function epdp_get_label_singular( $lowercase = false ) {
	$defaults = epdp_get_default_labels();

	return $lowercase ? strtolower( $defaults['singular'] ) : $defaults['singular'];
} // epdp_get_label_singular

/**
 * Get plural label.
 *
 * @since   1.1
 * @param   bool    $lowercase  Optional. Default false.
 * @return  string  Plural label.
 */
function epdp_get_label_plural( $lowercase = false ) {
	$defaults = epdp_get_default_labels();

	return $lowercase ? strtolower( $defaults['plural'] ) : $defaults['plural'];
} // epdp_get_label_plural

/**
 * Change default "Enter title here" input.
 *
 * @since   1.1
 * @param   string  $title  Default title placeholder text.
 * @return  string  $title  New placeholder text.
 */
function epdp_change_default_title( $title ) {
	$screen = get_current_screen();

	if ( 'epd_demo' === $screen->post_type ) {
		$label = epdp_get_label_singular( true );
		$title = sprintf( __( 'Enter %s name here', 'epd-premium' ), $label );
	}

	return $title;
} // epdp_change_default_title
add_filter( 'enter_title_here', 'epdp_change_default_title' );

/**
 * Registers the custom taxonomies for the demos custom post type
 *
 * @since   1.0
 * @return  void
*/
function epdp_setup_demo_taxonomies() {
	$slug = defined( 'EPDP_SLUG' ) ? EPDP_SLUG : 'demos';

	/** Categories */
	$category_labels = array(
		'name'              => sprintf( _x( '%s Categories', 'taxonomy general name', 'epd-premium' ), epdp_get_label_singular() ),
		'singular_name'     => sprintf( _x( '%s Category', 'taxonomy singular name', 'epd-premium' ), epdp_get_label_singular() ),
		'search_items'      => sprintf( __( 'Search %s Categories', 'epd-premium' ), epdp_get_label_singular() ),
		'all_items'         => sprintf( __( 'All %s Categories', 'epd-premium' ), epdp_get_label_singular() ),
		'parent_item'       => sprintf( __( 'Parent %s Category', 'epd-premium' ), epdp_get_label_singular() ),
		'parent_item_colon' => sprintf( __( 'Parent %s Category:', 'epd-premium' ), epdp_get_label_singular() ),
		'edit_item'         => sprintf( __( 'Edit %s Category', 'epd-premium' ), epdp_get_label_singular() ),
		'update_item'       => sprintf( __( 'Update %s Category', 'epd-premium' ), epdp_get_label_singular() ),
		'add_new_item'      => sprintf( __( 'Add New %s Category', 'epd-premium' ), epdp_get_label_singular() ),
		'new_item_name'     => sprintf( __( 'New %s Category Name', 'epd-premium' ), epdp_get_label_singular() ),
		'menu_name'         => __( 'Categories', 'epd-premium' ),
	);

	$category_args = apply_filters( 'epdp_demo_category_args', array(
			'hierarchical' => true,
			'labels'       => apply_filters( 'epdp_demo_category_labels', $category_labels ),
			'show_ui'      => true,
			'query_var'    => 'demo_category',
			'rewrite'      => array( 'slug' => $slug . '/category', 'with_front' => false, 'hierarchical' => true )
		)
	);
	register_taxonomy( 'demo_category', array( 'epd_demo' ), $category_args );
	register_taxonomy_for_object_type( 'demo_category', 'epd_demo' );

	/** Tags */
	$tag_labels = array(
		'name'                  => sprintf( _x( '%s Tags', 'taxonomy general name', 'epd-premium' ), epdp_get_label_singular() ),
		'singular_name'         => sprintf( _x( '%s Tag', 'taxonomy singular name', 'epd-premium' ), epdp_get_label_singular() ),
		'search_items'          => sprintf( __( 'Search %s Tags', 'epd-premium' ), epdp_get_label_singular() ),
		'all_items'             => sprintf( __( 'All %s Tags', 'epd-premium' ), epdp_get_label_singular() ),
		'parent_item'           => sprintf( __( 'Parent %s Tag', 'epd-premium' ), epdp_get_label_singular() ),
		'parent_item_colon'     => sprintf( __( 'Parent %s Tag:', 'epd-premium' ), epdp_get_label_singular() ),
		'edit_item'             => sprintf( __( 'Edit %s Tag', 'epd-premium' ), epdp_get_label_singular() ),
		'update_item'           => sprintf( __( 'Update %s Tag', 'epd-premium' ), epdp_get_label_singular() ),
		'add_new_item'          => sprintf( __( 'Add New %s Tag', 'epd-premium' ), epdp_get_label_singular() ),
		'new_item_name'         => sprintf( __( 'New %s Tag Name', 'epd-premium' ), epdp_get_label_singular() ),
		'menu_name'             => __( 'Tags', 'epd-premium' ),
		'choose_from_most_used' => sprintf( __( 'Choose from most used %s tags', 'epd-premium' ), epdp_get_label_singular() ),
	);

	$tag_args = apply_filters( 'epdp_demo_tag_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'epdp_demo_tag_labels', $tag_labels ),
			'show_ui'      => true,
			'query_var'    => 'demo_tag',
			'rewrite'      => array( 'slug' => $slug . '/tag', 'with_front' => false, 'hierarchical' => true )
		)
	);

	register_taxonomy( 'demo_tag', array( 'epd_demo' ), $tag_args );
	register_taxonomy_for_object_type( 'demo_tag', 'epd_demo' );
} // epdp_setup_demo_taxonomies
add_action( 'init', 'epdp_setup_demo_taxonomies', 0 );

/**
 * Get the singular and plural labels for a download taxonomy
 *
 * @since   1.1
 * @param   string  $taxonomy   The Taxonomy to get labels for
 * @return  array   Associative array of labels (name = plural)
 */
function epdp_get_taxonomy_labels( $taxonomy = 'demo_category' ) {
	$allowed_taxonomies = apply_filters( 'epdp_allowed_demo_taxonomies', array(
		'demo_category',
		'demo_tag',
	) );

	if ( ! in_array( $taxonomy, $allowed_taxonomies, true ) ) {
		return false;
	}

	$labels   = array();
	$taxonomy = get_taxonomy( $taxonomy );
	$taxonomy = get_taxonomy( $taxonomy );

	if ( false !== $taxonomy ) {
		$singular  = $taxonomy->labels->singular_name;
		$name      = $taxonomy->labels->name;
		$menu_name = $taxonomy->labels->menu_name;

		$labels = array(
			'name'          => $name,
			'singular_name' => $singular,
			'menu_name'     => $menu_name,
		);
	}

	return apply_filters( 'epdp_get_taxonomy_labels', $labels, $taxonomy );
} // epdp_get_taxonomy_labels
