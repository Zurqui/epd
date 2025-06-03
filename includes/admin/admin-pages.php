<?php	
/**
 * Admin Pages.
 * 
 * @since		1.2.9
 * @package		EPD Premium
 * @subpackage	Pages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Add the navigation tabs to the site edit screen.
 *
 * @since   1.2
 * @param   array   $tabs   An array of information about the individual link to a page
 * @return  array   An array of information about the individual link to a page
 */
function epdp_edit_site_nav_links( $tabs )  {
    $tabs['epd-edit-site'] = array(
        'label' => __( 'EPD Demo Settings', 'epd-premium' ),
        'url'   => 'site-info.php?page=epd-edit-site',
        'cap'   => 'manage_sites',
    );

    return $tabs;
} // epdp_edit_site_nav_links
add_filter( 'network_edit_site_nav_links', 'epdp_edit_site_nav_links' );

/**
 * Creates the admin submenu page for editing demo sites.
 *
 * @since	1.2
 * @return	void
 */
function epdp_add_edit_site_options_link() {

    if ( ! is_super_admin() )   {
        return;
    }

	global $epd_site_edit_page;

    $epd_site_edit_page = add_submenu_page(
        null,
        __( 'EPD Edit Site', 'epd-premium' ),
        __( 'EPD Edit Site', 'epd-premium' ),
        'manage_sites',
        'epd-edit-site',
        'epdp_edit_site_screen'
    );

} // epdp_add_edit_site_options_link
add_action( 'network_admin_menu', 'epdp_add_edit_site_options_link', 20 );

/**
 * Do not show the reset menu option for clone sites (masters).
 *
 * @since	1.2
 * @param	bool	$show		True to show menu item, or false
 * @param	int		$site_id	The site ID
 * @return	bool	True to show menu item, or false
 */
function epdp_show_reset_demo_menu_item( $show, $site_id )	{
	if ( epdp_is_clone_site( $site_id ) )	{
		$show = false;
	}

	return $show;
} // epdp_show_reset_demo_menu_item
add_filter( 'epd_show_reset_demo_menu_item', 'epdp_show_reset_demo_menu_item', 100, 2 );

/**
 * The site edit page.
 *
 * @since   1.2
 * @return  void
 */
function epdp_edit_site_screen()  {
	$site_id    = isset( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : get_current_blog_id();
    $expiration = epd_get_site_expiration_date( $site_id, 'Y-m-d H:i:s' );
    $details    = get_site( $site_id );
    $can_edit   = ! is_main_site( $site_id ) && ! epdp_is_clone_master( $site_id );

    if ( ! $details ) {
        wp_die( __( 'The requested site does not exist.', 'epd-premium' ) );
    }

    if ( ! can_edit_network( $details->site_id ) ) {
        wp_die( __( 'Sorry, you are not allowed to access this page.', 'epd-premium' ), 403 );
    }

    switch_to_blog( $site_id );
    $site_name = $details->blogname;
    restore_current_blog();

    $title = sprintf( __( 'Edit Site: %s', 'epd-premium' ), esc_html( $site_name ) );

    if ( ! $can_edit )   {
        $text_top = __( 'The primary site and sites defined as clone masters cannot have their expiry dates changed.', 'epd-premium' );
    } elseif ( ! empty( $expiration ) )   {
        $text_top = sprintf(
            __( '%s is currently set to expire on %s. If you want to edit the expiry date, you can use the form below.', 'epd-premium' ),
            $site_name,
            $expiration
        );
    } else  {
        $text_top = sprintf(
            __( '%s is currently set to never expire. If you want to set an expiry date, you can use the form below. Enter the date in the format <code>YYYY-dd-mm H:i:s</code>. i.e. %s', 'epd-premium' ),
            $site_name,
            date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) )
        );
    }

    ?>
	<div class="wrap">
		<h1><?php echo $title; ?></h1>
        <p class="edit-site-actions"><a href="<?php echo esc_url( get_home_url( $site_id, '/' ) ); ?>"><?php _e( 'Visit', 'epd-premium' ); ?></a> | <a href="<?php echo esc_url( get_admin_url( $site_id ) ); ?>"><?php _e( 'Dashboard', 'epd-premium' ); ?></a></p>
        <?php
        network_edit_site_nav(
            array(
                'blog_id'  => $site_id,
                'selected' => 'epd-edit-site'
            )
        );
        ?>
        <p><strong><?php echo $text_top; ?></strong></p>

        <?php if ( $can_edit ) : ?>
            <form method="post" name="epd-edit-demo">
                <?php wp_nonce_field( 'edit_site', 'epd_nonce' ); ?>
                <input type="hidden" name="epd_action" value="edit_site" />
                <input type="hidden" name="site_id" value="<?php echo $site_id; ?>" />

                <p><label for="epd-confirm-edit"><strong>
                <?php
                    _e( 'New Expiry Date:', 'epd-premium' );
                ?>
                    </strong></label>&nbsp;
                    <input type="text" name="epd_demo_expires" id="epd-demo-expires" value="<?php echo $expiration; ?>" />&nbsp;
                    <span class="description"><?php _e( 'Enter 0 to never expire.', 'epd-premium' ); ?></span>
                </p>
                <?php submit_button(
                    __( 'Edit Demo Site', 'epd-premium' ),
                    'primary',
                    'epd-edit-submit',
                    true
                ); ?>
            </form>
        <?php endif; ?>
	</div>
	<?php
} // epdp_edit_site_screen
