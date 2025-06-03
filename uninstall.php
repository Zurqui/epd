<?php

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

/**
 * Uninstall Easy Plugin Demo - Premium.
 *
 * Removes all settings.
 *
 * @package     EPD Premium
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2019, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 *
 */

if ( epd_get_option( 'remove_on_uninstall' ) )	{
    // Delete the Custom Post Types
    $epdp_taxonomies = array( 'demo_category', 'demo_tag' );
    $epdp_post_types = array( 'epd_demo' );

    foreach ( $epdp_post_types as $post_type ) {

        $epdp_taxonomies = array_merge( $epdp_taxonomies, get_object_taxonomies( $post_type ) );
        $items = get_posts( array(
            'post_type'   => $post_type,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields'      => 'ids'
        ) );

        if ( $items ) {
            foreach ( $items as $item )	{
                wp_delete_post( $item, true );
            }
        }
    }

    // Delete Terms & Taxonomies
    foreach ( array_unique( array_filter( $epdp_taxonomies ) ) as $taxonomy )	{
        $terms = $wpdb->get_results( $wpdb->prepare(
            "SELECT t.*, tt.*
            FROM $wpdb->terms
            AS t
            INNER JOIN $wpdb->term_taxonomy
            AS tt
            ON t.term_id = tt.term_id
            WHERE tt.taxonomy IN ('%s')
            ORDER BY t.name ASC", $taxonomy
        ) );

        // Delete Terms.
        if ( $terms ) {
            foreach ( $terms as $term ) {
                $wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
                $wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
                $wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
            }
        }

        // Delete Taxonomies.
        $wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
    }

    // Remove Plugin Options
    $options = array(
        'epd_premium_version',
        'epdp_notices'
    );

    $epd_options = array(
        'clone_site_id',
        'clone_tables',
        'plugins_action',
        'themes_action',
        'exclude_options',
        'set_author',
		'default_demo',
		'button_placement',
		'button_text',
		'mailchimp_api',
		'mailchimp_signup',
		'mailchimp_signup_label',
		'mailchimp_list',
		'mailchimp_double_opt_in'
    );

    foreach( $epd_options as $epd_option )	{
        epd_delete_option( $epd_option );
    }

    foreach( $options as $option )	{
        delete_site_option( $option );
    }
}
