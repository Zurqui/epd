<?php
/**
 * Features Settings.
 *
 * @package     EPD
 * @subpackage  Admin/Features/Settings
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Creates the page for adding/editing notices.
 *
 * @since	1.4
 * @return	void
 */
function epdp_add_edit_notices_link() {

    if ( ! is_super_admin() )   {
        return;
    }

	global $epd_notice_edit_page;

    $epd_notice_edit_page = add_submenu_page(
        null,
        __( 'Notices', 'epd-premium' ),
        __( 'Notices', 'epd-premium' ),
        'manage_sites',
        'epdp-edit-notices',
        'epdp_edit_notices_screen'
    );

} // epdp_add_edit_notices_link
add_action( 'network_admin_menu', 'epdp_add_edit_notices_link', 20 );

/**
 * Add the Premium Features tab.
 *
 * @since   1.4
 * @param   array   $tabs   Settings tabs
 * @return  array   Settings tabs
 */
function epdp_add_features_settings_tab_action( $tabs )   {
    $tabs['features'] = __( 'Premium Features', 'epd-premium' );

    return $tabs;
} // epdp_add_features_settings_tab_action
add_filter( 'epd_settings_tabs_before_licenses', 'epdp_add_features_settings_tab_action' );

/**
 * Add the features settings sections.
 *
 * @since	1.4
 * @param	array	$sections	Array of setting tab sections
 * @return	array	Array of setting tab sections
 */
function epdp_add_features_settings_sections_action( $sections )	{
	$sections['features']['rest']   = __( 'REST API', 'epd-premium' );
	$sections['features']['notice'] = __( 'Notices', 'epd-premium' );

	return $sections;
} // epdp_add_features_settings_sections_action
add_filter( 'epd_settings_sections', 'epdp_add_features_settings_sections_action' );

/**
 * Add the premium features settings.
 *
 * @since	1.4
 * @param	array	$settings	Array of settings
 * @return	array	Array of settings
 */
function epdp_register_features_settings( $settings )	{
	$settings['features']['rest'] = array(
		'rest_header' => array(
			'id'      => 'rest_header',
			'name'    => '<h1>' . __( 'REST API', 'epd-premium' ) . '</h1>',
			'type'    => 'header'
		),
		'enable_rest' => array(
			'id'          => 'enable_rest',
			'name'        => __( 'Enable Remote Registration', 'epd-premium' ),
			'type'        => 'checkbox',
			'field_class' => 'epdp_enable_rest',
			'desc'        => __( 'Select this option to enable demo site creation via a remote website using the EPD Remote extension.', 'epd-premium' )
		),
        'remote_phrase' => array(
			'id'          => 'remote_phrase',
			'name'        => __( 'EPD Remote Secret Key', 'epd-premium' ),
			'type'        => 'secret',
			'field_class' => 'epd-button',
			'desc'        => __( 'Enter the secret key which was generated via the EPD Remote plugin.', 'epd-premium' )
		)
	);

    $settings['features']['notice'] = array(
        'notice_header' => array(
			'id'      => 'notice_header',
			'name'    => '<h1>' . __( 'Notices', 'epd-premium' ) . '</h1>',
			'type'    => 'header'
		)
    );

	return $settings;
} // epdp_register_features_settings
add_filter( 'epd_registered_settings', 'epdp_register_features_settings' );

/**
 * Displays the API secret.
 *
 * @since	1.5
 * @return	string	API secret field
 */
function epdp_rest_settings_show_api_secret()	{
    if ( ! is_super_admin() )  {
        return;
    }

	$api_enabled = epd_get_option( 'enable_rest' );
	$disabled    = $api_enabled ? '' : ' disabled';

	ob_start(); ?>

	<table class="form-table" role="presentation">
		<tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'REST API Secret Key', 'epd-premium' ); ?>
                </th>
                <td id="epdp-api-key-display">
                    <?php printf(
                        '<button id="epdp-reveal-secret-button" type="button" class="button epd-button"%s>%s</button>',
                        $disabled,
                        __( 'Reveal Secret Key', 'epd-premium' )
                    ); ?>
                    <p class="description"><?php _e( 'Copy this phrase into the site where you have the EPD Remote plugin installed to enable demo site creation via the REST API.', 'epd-premium' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Generate New Key', 'epd-premium' ); ?>
                </th>
                <td>
                    <?php printf(
                        '<button id="epdp-regenerate-secret" type="button" class="button epd-button"%s>%s</button>',
                        $disabled,
                        __( 'Regenerate Secret Key', 'epd-premium' )
                    ); ?>
                    <p class="description"><?php _e( 'Click here to generate a new secret key if you believe your current key may have been compromised.', 'epd-premium' ); ?></p>
                </td>
            </tr>
		</tbody>
	</table>

	<?php echo ob_get_clean();
} // epdp_rest_settings_show_api_secret
add_action( 'epd_settings_tab_bottom_features_rest', 'epdp_rest_settings_show_api_secret' );

/**
 * Adds the notices screen to the features/notices options page.
 *
 * @since   1.4
 * @return  void
 */
function epdp_notice_settings_page()   {
    $notices = epdp_get_notices();
    $add_url = add_query_arg( array(
        'page'       => 'epdp-edit-notices',
        'epd_action' => 'add_notice'
    ), network_admin_url( 'settings.php' ) );

	$edit_url = add_query_arg( array(
        'page'       => 'epdp-edit-notices',
        'epd_action' => 'edit_notice'
    ), network_admin_url( 'settings.php' ) );

    $del_url = add_query_arg( array(
        'page'       => 'epdp-edit-notices',
        'epd_action' => 'delete_notice'
    ), network_admin_url( 'settings.php' ) );

    ?>

    <script>
        jQuery(document).ready(function ($) {
            $('#submit').hide();
        });
    </script>

    <table id="epdp_notices" class="widefat fixed">
        <thead>
            <tr>
                <th class="epdp-notice-name-col" scope="col"><?php _e( 'Name', 'epd-premium' ); ?></th>
                <th class="epdp-notice-active-col" scope="col"><?php _e( 'Status', 'epd-premium' ); ?></th>
                <th class="epdp-notice-display-col" scope="col"><?php _e( 'Displays On', 'epd-premium' ); ?></th>
				<th class="epdp-notice-timer-col" scope="col"><?php _e( 'Runs After', 'epd-premium' ); ?></th>
				<th class="epdp-notice-displays-col" scope="col"><?php _e( 'Displayed', 'epd-premium' ); ?></th>
                <th scope="epdp-notice-actions-col"><?php _e( 'Actions', 'epd-premium' ); ?></th>
            </tr>
        </thead>
        <?php if ( ! empty( $notices ) ) :
            $i = 1;
            foreach( $notices as $notice_id => $notice ) :
                $notice          = epdp_get_notice( $notice_id );
                $class           = $i % 2 == 0 ? ' class="alternate"' : '';
                $active          = ! empty( $notice['active'] ) ? __( 'Active', 'epd-premium' ) : __( 'Inactive', 'epd-premium' );
				$displayed       = epdp_get_notice_displayed( $notice_id, $notices );
                $active_class    = ! empty( $notice['active'] ) ? 'epdp-active' : 'epdp-expired';
				$edit_url        = add_query_arg( 'notice_id', $notice_id, $edit_url );
                $del_url         = add_query_arg( 'notice_id', $notice_id, $del_url );
                $display_options = epdp_get_notice_display_options_list();
				$actions         = array(
                    sprintf(
                        '<a href="%s" class="epdp-edit-notice" data-key="%d">%s</a>',
                        esc_url( $edit_url ),
                        esc_attr( $notice_id ),
                         __( 'Edit', 'epd-premium' )
                    ),
                    sprintf(
                        '<a href="%s" class="epdp-delete">%s</a>',
                        esc_url( wp_nonce_url( $del_url, 'delete_notice', 'epd_nonce' ) ),
                         __( 'Delete', 'epd-premium' )
                    )
                );
				?>
                <tr<?php echo $class; ?>>
                    <td>
                        <?php printf(
                            '<a href="%s">%s</a>',
                            esc_url( $edit_url ),
                            esc_html( stripslashes( $notice['name'] ) )
                        ); ?>
                    </td>
                    <td>
                    	<span class="<?php echo $active_class; ?>">
							<?php echo $active; ?>
                        </span>
					</td>
                    <td>
                        <?php echo esc_html( stripslashes( $display_options[ $notice['display'] ] ) ); ?>
                    </td>
					<td>
                        <?php echo esc_html( $notice['timer'] . ' ' . _n( 'Second', 'Seconds', $notice['timer'], 'epd-premium' ) ); ?>
                    </td>
					<td>
                        <?php echo esc_html( $displayed . ' ' . _n( 'Time', 'Times', $displayed, 'epd-premium' ) ); ?>
                    </td>
					<td>
                        <?php echo implode( ' &#124; ', $actions ); ?>
                    </td>
				</tr>
				<?php
                $i++;
            endforeach;
        else : ?>
            <tr>
                <td colspan="5" scope="col">
                    <?php printf(
                        __( 'You have not defined any notices. <a href="%s">Add notice</a>.', 'epd-premium' ),
                        esc_url( $add_url )
                    ); ?>
                </td>
            </tr>
        <?php endif; ?>
    </table>
	<p>
        <a href="<?php echo esc_url( $add_url ); ?>" class="button-secondary" id="epdp_add_notice">
            <?php _e( 'Add Notice', 'epd-premium' ); ?>
        </a>
    </p>
    <?php
} // epdp_notice_settings_page
add_action( 'epd_settings_tab_bottom_features_notice', 'epdp_notice_settings_page' );

/**
 * Displays the notice add/edit page.
 *
 * @since	1.4
 * @return  void
 */
function epdp_edit_notices_screen()	{
    $action = isset( $_GET['epd_action'] ) ? sanitize_text_field( $_GET['epd_action'] ) : 'add_notice';

    if ( 'edit_notice' == $action )	{
        $notice_id = absint( $_GET['notice_id'] );
    }

    if ( isset( $notice_id ) && 'edit_notice' == $action )	{
        $add_or_edit = 'edit';
        $title       = __( 'Edit Notice', 'epd-premium' );
        $notice      = epdp_get_notice( $notice_id );
    } else	{
		$notices     = epdp_get_notices();
        $notice_id   = count( $notices ) + 1;
        $add_or_edit = 'add';
        $title       = __( 'Add Notice', 'epd-premium' );
        $notice      = array(
            'active'     => '0',
            'name'       => '',
            'slug'       => '',
			'timer'      => 0,
			'border'     => '#00a0d2',
			'background' => '#ffffff',
			'text'       => '#000000',
            'display'    => 'both',
			'notice'     => ''
        );
    }

    $active      = esc_attr( $notice['active'] );
    $name        = esc_attr( stripslashes( $notice['name'] ) );
    $slug        = esc_attr( stripslashes( $notice['slug'] ) );
    $display     = esc_attr( stripslashes( $notice['display'] ) );
	$timer       = esc_attr( stripslashes( $notice['timer'] ) );
	$border      = esc_attr( stripslashes( $notice['border'] ) );
	$background  = esc_attr( stripslashes( $notice['background'] ) );
	$text        = esc_attr( stripslashes( $notice['text'] ) );
	$notice_text = wp_kses_post( stripslashes( $notice['notice'] ) );

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">
            <?php echo $title; ?> <a href="<?php echo network_admin_url( 'settings.php?page=epd-settings&tab=features&section=notice' ); ?>" class="page-title-action"><?php _e( 'Back', 'epd-premium' ); ?></a>
        </h1>
        <form id="epd-<?php echo $add_or_edit; ?>-notice" action="" method="post" autocomplete="off">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row" valign="top">
                            <label for="epdp-notice-name"><?php _e( 'Notice Name', 'epd-premium' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="epdp-notice-name" name="notice_name" class="regular-text" value="<?php echo $name; ?>">
                            <p class="description"><?php _e( 'Provide a descriptive name for this notice.', 'epd-premium' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">
                            <label for="notice_active"><?php _e( 'Enable Notice', 'epd-premium' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="notice_active" value="1"<?php checked( 1, $active ); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">
                            <label for="epdp-notice-display"><?php _e( 'Display On', 'epd-premium' ); ?></label>
                        </th>
                        <td>
                            <select id="epdp-notice-display" name="notice_display" class="epdp_select_chosen" value="<?php echo $display; ?>">
                                <?php foreach( epdp_get_notice_display_options_list() as $value => $option ) : ?>
                                    <option value="<?php echo $value; ?>"<?php selected( $value, $display ); ?>>
                                        <?php echo $option; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
					<tr>
                        <th scope="row" valign="top">
                            <label for="epdp-notice-timer"><?php _e( 'When to Display', 'epd-premium' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="epdp-notice-timer" name="notice_timer" class="regular-text" value="<?php echo $timer; ?>">
							<p class="description"><?php _e( 'Enter the number of seconds after a site has been provisioned that the notice should be displayed.', 'epd-premium' ); ?></p>
                        </td>
                    </tr>
					<tr>
                        <th scope="row" valign="top">
                            <label for="epdp-notice-border"><?php _e( 'Border Color', 'epd-premium' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="epdp-notice-border" name="notice_border" class="regular-text epdp-color-picker" value="<?php echo $border; ?>" data-default-color="#00a0d2">
                        </td>
                    </tr>
					<tr>
                        <th scope="row" valign="top">
                            <label for="epdp-notice-background"><?php _e( 'Background Color', 'epd-premium' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="epdp-notice-background" name="notice_background" class="regular-text epdp-color-picker" value="<?php echo $background; ?>" data-default-color="#ffffff">
                        </td>
                    </tr>
					<tr>
                        <th scope="row" valign="top">
                            <label for="epdp-notice-text-color"><?php _e( 'Text Color', 'epd-premium' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="epdp-notice-text-color" name="notice_text_color" class="regular-text epdp-color-picker" value="<?php echo $text; ?>" data-default-color="#000000">
                        </td>
                    </tr>
					<tr>
						<th scope="row" valign="top">
                            <label for="epdp-notice-text"><?php _e( 'Notice Text', 'epd-premium' ); ?></label>
                        </th>
						<td>
                            <?php
							wp_editor(
								$notice_text,
								'epdp-notice-text',
								array(
									'textarea_name' => 'notice_text',
									'textarea_rows' => 5
								)
							);
							?>
							<p class="description"><?php printf(
                                __( 'The following tags can be used:<br>%s', 'epd-premium' ),
                                epd_get_emails_tags_list()
                            ); ?></p>
						</td>
					</tr>
                </tbody>
            </table>
            <?php do_action( 'epdp_after_notice_table', $notice_id, $notice ); ?>
            <input type="hidden" name="epd_action" value="<?php echo $add_or_edit; ?>_notice"/>
            <input type="hidden" name="notice_id" value="<?php echo $notice_id; ?>"/>
            <input type="hidden" name="notice_slug" value="<?php echo $slug; ?>"/>
            <?php
            wp_nonce_field( $add_or_edit . '_notice', 'epd_nonce' );
            submit_button(
                __( 'Save Notice', 'epd-premium' ),
                'primary',
                false,
                true,
                array( 'id' => 'epdp-save-notice' )
            );
            ?>
        </form>
    </div>
    <?php
} // epdp_edit_notices_screen

/**
 * Retrieve display options for notices.
 *
 * @since   1.4
 * @return  array   Array of possible display options
 */
function epdp_get_notice_display_options_list() {
    $options = array(
        'both'  => __( 'Admin and Front End', 'epd-premium' ),
        'admin' => __( 'Admin Only', 'epd-premium' ),
        'front' => __( 'Front End Only', 'epd-premium' ),
    );

    $options = apply_filters( 'epdp_notice_display_options', $options );

    return $options;
} // epdp_get_notice_display_options_list
