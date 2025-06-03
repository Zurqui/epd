<?php

/**
 * Sites
 *
 * @package     EPD
 * @subpackage  Admin/Sites
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove checkbox within sites list from clone masters.
 *
 * @since	1.3
 * @return	void
 */
function epdp_sites_remove_clone_checkboxes()	{
	$clone_ids = epdp_get_clone_masters();

	if ( empty( $clone_ids ) )	{
		return;
	}

	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		<?php foreach( $clone_ids as $clone_id ) : ?>
			$('input#blog_<?php echo $clone_id; ?>').prop('disabled', true).hide();
		<?php endforeach; ?>
	});
	</script>
	<?php
} // epdp_sites_remove_clone_checkboxes
add_action( 'admin_footer-sites.php', 'epdp_sites_remove_clone_checkboxes' );

/**
 * Remove expiration date value from clone masters.
 *
 * @since	1.2
 * @param	string	$output	Column output
 * @param	object	$site	WP_Site object
 * @return	string	Column output
 */
function epdp_sites_expires_column( $output, $site )	{
	$clone_masters  = epdp_get_clone_masters();

	if ( in_array( $site->blog_id, $clone_masters ) )	{
		$output  = '&ndash; ';
		$output .= '<br>';
		$output .= '<span class="description">' . __( 'Clone Master', 'epd-premium' ) . '</span>';
	}

	return $output;
} // epdp_sites_expires_column
add_filter( 'epd_sites_expires_column', 'epdp_sites_expires_column', 10, 2 );

/**
 * Register the 'Expires' column within the sites list table.
 *
 * @since	1.0
 * @param	array	$columns	Array of table columns
 * @return	array	Array of table columns
 */
function epd_sites_demo_template_column( $columns )	{
	$columns['demo_template'] = __( 'Demo Template', 'epd-premium' );

	return $columns;
} // epd_sites_demo_template_column
add_filter( 'wpmu_blogs_columns', 'epd_sites_demo_template_column' );

/**
 * Renders the site demo template within the the sites list table 'Demo Template' column.
 *
 * @since	1.0
 * @param	string	$column_name	Current column name
 * @param	int		$blog_id		ID of the current blog/site
 * @return	string	Output for column
 */
function epd_render_sites_demo_template_column( $column_name, $blog_id )	{
	if ( 'demo_template' == $column_name )	{
		$demo_id = epdp_get_site_demo_template_id( $blog_id );

        if ( ! empty( $demo_id ) )  {
            $demo = sprintf(
                '<a href="%s">%s</a>',
                get_edit_post_link( $demo_id ),
                get_the_title( $demo_id )
            );
        } else  {
            $demo = '&ndash;';
        }

        $demo = apply_filters( 'epd_sites_demo_template_column', $demo, $demo_id, $blog_id );

		echo $demo;
	}
} // epd_render_sites_demo_template_column
add_action( 'manage_sites_custom_column', 'epd_render_sites_demo_template_column', 10, 2 );

/**
 * Remove the delete option from sites defined as clone masters.
 *
 * @since	1.2
 * @param	array	$actions	Array of actions
 * @param	int		$site_id	Site ID
 * @return	array	$actions	Array of actions
 */
function epdp_site_action_links( $actions, $site_id )	{
	$clone_masters  = epdp_get_clone_masters();
	$remove_actions = array( 'deactivate', 'archive', 'spam', 'delete' );

	if ( in_array( $site_id, $clone_masters ) )	{
		foreach( $remove_actions as $action )	{
			if ( isset( $actions[ $action ] ) )	{
				unset( $actions[ $action ] );
			}
		}
	}

	return $actions;
} // epdp_site_action_links
add_filter( 'manage_sites_action_links', 'epdp_site_action_links', 100, 2 );
