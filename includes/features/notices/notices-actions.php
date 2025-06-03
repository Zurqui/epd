<?php
/**
 * Notices Actions
 *
 * @package     EPD Premium
 * @subpackage  Functions/Features/Notices
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Process a new or updated notice action.
 *
 * @since   1.4
 * @return	void
 */
function epdp_add_or_update_notice_action()   {

    if ( ! isset( $_POST['epd_action'] ) )  {
        return;
    }

    $action = $_POST['epd_action'];

    if ( 'add_notice' == $action || 'edit_notice' == $action )  {

        if ( ! wp_verify_nonce( $_POST['epd_nonce'], 'add_notice' ) && ! wp_verify_nonce( $_POST['epd_nonce'], 'edit_notice' ) ) {
            wp_die( __( 'Nonce verification failed', 'epd-premium' ), __( 'Error', 'epd-premium' ), array( 'response' => 401 ) );
        }

        if ( ! current_user_can( 'manage_sites' ) ) {
            wp_die( __( 'You do not have permission to manage notices', 'epd-premium' ), __( 'Error', 'epd-premium' ), array( 'response' => 401 ) );
        }

		$notice_id = $_POST['notice_id'];
		$data      = array(
            'active'     => isset( $_POST['notice_active'] ) ? '1' : '0',
            'name'       => sanitize_text_field( $_POST['notice_name'] ),
            'slug'       => ! empty( $_POST['notice_slug'] ) ? sanitize_text_field( $_POST['notice_slug'] ) : 'epdp-notice-' . substr( md5( rand() ), 4, 10 ),
            'display'    => sanitize_text_field( $_POST['notice_display'] ),
            'timer'      => absint( $_POST['notice_timer'] ),
			'border'     => sanitize_text_field( $_POST['notice_border'] ),
			'background' => sanitize_text_field( $_POST['notice_background'] ),
			'text'       => sanitize_text_field( $_POST['notice_text_color'] ),
			'notice'     => $_POST['notice_text']
        );

		/**
		 * Allow plugins to filter the data array.
		 *
		 * @since	1.4
		 * @param	array	$data	Array of data being saved
		 */
		$data    = apply_filters( 'epd_notice_data_array', $data, $notice_id );
        $notices = epdp_get_notices();

		if ( array_key_exists( $notice_id, $notices ) )   {
			$data['displayed'] = ! empty( $notices[ $notice_id ]['displayed'] ) ? $notices[ $notice_id ]['displayed'] : 0;

			$notices[ $notice_id ] = $data;
            $message = 'notice-updated';
		} else  {
            $data['displayed'] = 0;
            $notices[ $notice_id ] = $data;
            $message = 'notice-added';
        }

		update_site_option( 'epdp_notices', $notices );

		wp_safe_redirect( add_query_arg( array(
            'page'        => 'epd-settings',
            'tab'         => 'features',
            'section'     => 'notice',
            'epd-message' => $message
        ), network_admin_url( 'settings.php' ) ) );
        exit;
	}
} // epdp_add_or_update_notice_action
add_action( 'init', 'epdp_add_or_update_notice_action' );

/**
 * Process the deletion of a notice.
 *
 * @since   1.4
 * @return	void
 */
function epdp_delete_notice_action()   {

    if ( ! isset( $_GET['epd_action'] ) )  {
        return;
    }

    if ( 'delete_notice' != $_GET['epd_action'] )  {
        return;
    }

    if ( ! wp_verify_nonce( $_GET['epd_nonce'], 'delete_notice' ) ) {
        wp_die(
			__( 'Nonce verification failed', 'epd-premium' ),
			__( 'Error', 'epd-premium' ),
			array(
				'response' => 401
			)
		);
    }

    if ( ! current_user_can( 'manage_sites' ) ) {
        wp_die(
			__( 'You do not have permission to manage notices', 'epd-premium' ),
			__( 'Error', 'epd-premium' ),
			array(
				'response' => 401
			)
		);
    }

    $notice_id = $_GET['notice_id'];
    $notices   = epdp_get_notices();

    unset( $notices[ $notice_id ] );
    update_site_option( 'epdp_notices', $notices );

    wp_safe_redirect( add_query_arg( array(
		'page'        => 'epd-settings',
		'tab'         => 'features',
		'section'     => 'notice',
		'epd-message' => 'notice-deleted'
	), network_admin_url( 'settings.php' ) ) );
	exit;

} // epdp_delete_notice_action
add_action( 'init', 'epdp_delete_notice_action' );

/**
 * Adds the notice schedules to new sites.
 *
 * @since	1.4
 * @param	int		$site_id	Site ID
 * @return	void
 */
function epdp_add_notice_schedules_action( $site_id )	{
	$notices = epdp_get_notices();

	switch_to_blog( $site_id );

	foreach( $notices as $notice )	{
        if ( ! empty( $notice['active'] ) )   {
            set_transient( $notice['slug'], 'anything', $notice['timer'] );
            epdp_add_notice_for_site( $site_id, $notice['slug'] );
        }
	}

	restore_current_blog();
} // epdp_add_notice_schedules_action
add_action( 'epd_registration', 'epdp_add_notice_schedules_action' );
