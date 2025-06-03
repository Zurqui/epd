<?php	
/**
 * Admin site functions.
 * 
 * @since		1.2.9
 * @package		EPD Premium
 * @subpackage	Functions
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Ensure that the clone master site cannot be deleted by removing the 
 * delete option from the hover menu on the edit screen.
 * 
 * @since	1.0
 * @param   array   $actions    Array of possible actions
 * @param	int     $site_id		The ID of the current site
 * @return	array   The filtered array of actions in the hover menu
 */
function epdp_sites_remove_delete_row_action( $actions, $site_id )	{
	if ( epdp_is_clone_master( $site_id ) )	{
        if ( isset( $actions['delete'] ) )  {
            unset( $actions['delete'] );
        }
	}

	return $actions;
} // epdp_sites_remove_delete_row_action
add_filter( 'manage_sites_action_links', 'epdp_sites_remove_delete_row_action', 10, 2 );

/**
 * Ensure that the clone master site cannot be deleted by removing the 
 * bulk action checkbox.
 *
 * @since	1.0
 * @return	void
 */
function epdp_sites_remove_clone_master_checkbox_action()	{
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
        $('input#blog_<?php echo epdp_get_clone_master(); ?>').prop('disabled', true).hide();
	});
	</script>
	<?php
} // epdp_sites_remove_clone_master_checkbox_action
add_action( 'admin_footer-sites.php', 'epdp_sites_remove_clone_master_checkbox_action' );
