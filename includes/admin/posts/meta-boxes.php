<?php
/**
 * Demo Post Meta Boxes
 *
 * @package     EPD Premium
 * @subpackage  Functions/Meta Boxes
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Default fields and values to display.
 *
 * @since	1.2
 * @return	array	Array of fields => values
 */
function epd_demo_metabox_fields()	{
	$fields = array(
		'_epdp_button_placement'        => 'after_post',
        '_epdp_site_title'              => '',
        '_epdp_site_tag_line'           => '',
        '_epdp_discourage_search'       => false,
        '_epdp_disable_visibility'      => false,
        '_epdp_disable_default_welcome' => false,
        '_epdp_add_custom_welcome'      => false,
		'_epdp_upload_space'            => 0,
		'_epdp_custom_welcome_message'  => '',
        '_epdp_registration_action'     => 'confirm',
        '_epdp_redirect_page'           => '',
		'_epdp_auto_login'              => '0',
        '_epdp_delete_site_after'       => '0',
		'_epdp_clone_site'              => '0',
		'_epdp_clone_plugins_action'    => 'clone',
		'_epdp_clone_themes_action'     => 'clone',
		'_epdp_theme'                   => '',
		'_epdp_hide_appearance_menu'    => false,
		'_epdp_available_themes'        => array(),
		'_epdp_hide_plugins_menu'       => false,
		'_epdp_plugins'                 => array(),
        '_epdp_notices'                 => array()
	);

	$fields = apply_filters( 'epd_demo_metabox_fields', $fields );

	return $fields;
} // epd_demo_metabox_fields

/**
 * Define and add the metaboxes for the epd_demo post type.
 *
 * @since	1.2
 * @param	object	$post	The WP_Post object.
 * @return	void
 */
function epdp_demo_add_meta_boxes( $post )	{
	add_meta_box(
        'epdp-demo-metabox-button',
        __( 'Demo Button', 'epd-premium' ),
        'epdp_demo_metabox_button_options_callback',
        'epd_demo',
        'side'
    );
    add_meta_box(
        'epdp-demo-metabox-options',
        __( 'Site Options', 'epd-premium' ),
        'epdp_demo_metabox_site_options_callback',
        'epd_demo',
        'normal',
        'high',
        array()
    );
	add_meta_box(
        'epdp-demo-metabox-cloning',
        __( 'Cloning', 'epd-premium' ),
        'epdp_demo_metabox_cloning_callback',
        'epd_demo',
        'normal',
        'high',
        array()
    );
    add_meta_box(
        'epdp-demo-metabox-themes-plugins',
        __( 'Themes & Plugins', 'epd-premium' ),
        'epdp_demo_metabox_themes_plugins_callback',
        'epd_demo',
        'normal',
        'high',
        array()
    );
    add_meta_box(
        'epdp-demo-metabox-notices',
        __( 'Notices', 'epd-premium' ),
        'epdp_demo_metabox_notices_callback',
        'epd_demo',
        'normal',
        array()
    );
} // epdp_demo_add_meta_boxes
add_action( 'add_meta_boxes_epd_demo', 'epdp_demo_add_meta_boxes', 100 );

/**
 * Callback for the button options metabox.
 *
 * @since   1.2
 * @param	object  $post   WP_Post object
 * @return  void
 */
function epdp_demo_metabox_button_options_callback( $post ) {
    /*
	 * Output the content for the button options metabox
	 * @since	1.2
	 * @param	object  $post	The WP_Post object
	 */
	do_action( 'epdp_demo_button_options_fields', $post );
} // epdp_demo_metabox_button_options_callback

/**
 * Callback for the site options metabox.
 *
 * @since   1.2
 * @param	object  $post   WP_Post object
 * @return  void
 */
function epdp_demo_metabox_site_options_callback( $post ) {
    wp_nonce_field( 'epd_demo_meta_save', 'epd_demo_meta_box_nonce' );

	$site_ref = epdp_get_demo_key( $post->ID );

	if ( empty( $site_ref ) )	{
		printf(
			'<input type="hidden" name="_epd_site_ref" value="1">',
			$site_ref
		);
	}

    /*
	 * Output the content for the themes/plugins metabox
	 * @since	1.2
	 * @param	object  $post	The WP_Post object
	 */
	do_action( 'epdp_demo_site_options_fields', $post );
} // epdp_demo_metabox_site_options_callback

/**
 * Callback for the Cloning metabox.
 *
 * @since   1.2
 * @param	object  $post   WP_Post object
 * @return  void
 */
function epdp_demo_metabox_cloning_callback( $post ) {
    /*
	 * Output the content for the cloning metabox
	 * @since	1.2
	 * @param	object  $post	The WP_Post object
	 */
	do_action( 'epdp_demo_cloning_fields', $post );
} // epdp_demo_metabox_cloning_callback

/**
 * Callback for the Themes/Plugins metabox.
 *
 * @since   1.2
 * @param	object  $post   WP_Post object
 * @return  void
 */
function epdp_demo_metabox_themes_plugins_callback( $post ) {
    /*
	 * Output the content for the themes/plugins metabox
	 * @since	1.2
	 * @param	object  $post	The WP_Post object
	 */
	do_action( 'epdp_demo_theme_plugin_fields', $post );
} // epdp_demo_metabox_themes_plugins_callback

/**
 * Callback for the notices metabox.
 *
 * @since   1.4
 * @param	object  $post   WP_Post object
 * @return  void
 */
function epdp_demo_metabox_notices_callback( $post ) {
    /*
	 * Output the content for the notices metabox
	 * @since	1.4
	 * @param	object  $post	The WP_Post object
	 */
	do_action( 'epdp_demo_notices_fields', $post );
} // epdp_demo_metabox_notices_callback

/**
 * Output the button options meta box.
 *
 * @since   1.2
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_button_options_metabox( $post )   {
	$button_placement = epdp_demo_get_button_placement( $post->ID );

	ob_start(); ?>

	<div class="epdp-button-options">
		<div class="epdp-side-option-row">
			<span class="metabox-option-label">
				<?php _e( 'Placement:', 'epd-premium' ); ?>
			</span>
			<div class="epdp-option">
				<select id="epdp-button-placement" name="_epdp_button_placement" class="epdp_select_chosen">
					<?php foreach( epdp_get_button_placement_setting_options() as $value => $label ) : ?>
						<?php printf( 
							'<option value="%s"%s>%s</option>',
							$value,
							selected( $button_placement, $value, false ),
							$label
						); ?>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
    </div>

	<?php echo ob_get_clean();
} // epdp_output_button_options_metabox
add_action( 'epdp_demo_button_options_fields', 'epdp_output_button_options_metabox' );

/**
 * Output the site title and tag line options meta box.
 *
 * @since   1.2
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_site_title_tag_options_metabox( $post )   {
    $title_placeholder    = __( 'Enter demo site title here', 'epd-premium' );
    $title_placeholder    = apply_filters( 'epdp_site_title_placeholder', $title_placeholder );
    $title_description    = __( 'This will be the title of all new sites created from this template. If left empty, the name of this demo will be used.', 'epd-premium' );
    $title_description    = apply_filters( 'epdp_site_title_description', $title_description );
    $title                = epdp_get_demo_site_title( $post->ID );

    $tag_line_placeholder = __( 'Enter demo site tag line here', 'epd-premium' );
    $tag_line_placeholder = apply_filters( 'epdp_site_tag_line_placeholder', $tag_line_placeholder );
    $tag_line_description = __( 'This will be the tag line (description) for all new sites created from this template.', 'epd-premium' );
    $tag_line_description = apply_filters( 'epdp_site_tag_line_description', $tag_line_description );
    $tag_line             = epdp_get_demo_site_tag_line( $post->ID );

    ob_start(); ?>

    <div id="epdp_options">
        <div id="epdp_option_fields" class="epdp_meta_table_wrap">
            <div class="widefat epdp_repeatable_table">
                <?php do_action( 'epdp_demo_options_table_head', $post->ID ); ?>

                <div class="epdp-options-header">
                    <span class="epdp-options-title"><?php _e( 'Title & Tag Line', 'epd-premium' ); ?></span>
                </div>
                <div class="epdp-site-options epdp-repeatables-wrap">
					<div class="epdp_repeatable_option_wrapper epdp_repeatable_row">
						<div class="epdp-option-row">
							<div class="epdp-option-item">
								<span class="epdp-repeatable-row-setting-label"><?php _e( 'Enter a title for sites created from this demo', 'epd-premium' ); ?></span>
                                <?php printf(
    								'<input type="text" name="_epdp_site_title" id="site-title" class="epdp_input" value="%s" placeholder="%s">',
                                    $title,
                                    $title_placeholder
                                ); ?>
							</div>
							<div class="epdp-site-tag-line">
								<span class="epdp-repeatable-row-setting-label"><?php _e( 'Enter the tag line for sites created from this demo', 'epd-premium' ); ?></span>
								<?php printf(
    								'<input type="text" name="_epdp_site_tag_line" id="site-tag-line" class="epdp_input" value="%s" placeholder="%s">',
                                    $tag_line,
                                    $tag_line_placeholder
                                ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <?php echo ob_get_clean();
} // epdp_output_site_options_metabox
add_action( 'epdp_demo_site_options_fields', 'epdp_output_site_title_tag_options_metabox' );

/**
 * Output the site action settings meta box.
 *
 * @since   1.2
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_site_settings_options_metabox( $post )   {
    $discourage_search  = epdp_get_demo_discourage_search( $post->ID );
    $disable_visibility = epdp_get_demo_disable_visibility_setting( $post->ID );

    ob_start(); ?>

    <div id="epdp_options">
        <div id="epdp_option_fields" class="epdp_meta_table_wrap">
            <div class="widefat epdp_repeatable_table">
                <?php do_action( 'epdp_demo_settings_table_head', $post->ID ); ?>

                <div class="epdp-options-header">
                    <span class="epdp-options-title"><?php _e( 'Settings', 'epd-premium' ); ?></span>
                </div>
                <div class="epdp-site-options epdp-repeatables-wrap">
					<div class="epdp_repeatable_option_wrapper epdp_repeatable_row">
						<div class="epdp-option-row">
							<div class="epdp-option-item">
                                <?php printf(
                                    '<input type="checkbox" name="_epdp_discourage_search" id="epdp-discourage-search" value="1"%s>',
                                    checked( 1, $discourage_search, false )
                                ); ?> <label for="epdp-discourage-search"><?php _e( 'Discourage search engines?', 'epd-premium' ); ?></label>
							</div>
                            <div class="epdp-option-item epdp-inline">
                                <?php printf(
                                    '<input type="checkbox" name="_epdp_disable_visibility" id="epdp-disable-visibility" value="1"%s>',
                                    checked( 1, $disable_visibility, false )
                                ); ?> <label for="epdp-disable-visibility"><?php _e( 'Disable visibility changes?', 'epd-premium' ); ?></label>
                            </div>
                        </div>
                    </div>
                    <?php do_action( 'epdp_after_settings_repeatable_rows', $post ); ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo ob_get_clean();
} // epdp_output_site_settings_options_metabox
add_action( 'epdp_demo_site_options_fields', 'epdp_output_site_settings_options_metabox' );

/**
 * Output the settings for the welcome panel within the settings meta box.
 *
 * @since   1.2
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_site_settings_welcome_metabox( $post )    {
    $disable_default_welcome = epdp_get_demo_disable_default_welcome_panel_setting( $post->ID );
    $add_custom_welcome      = epdp_get_demo_add_custom_welcome_panel_setting( $post->ID );

    ob_start(); ?>

	<div class="epdp_repeatable_option_wrapper epdp_repeatable_row">
		<div class="epdp-option-row">
			<div class="epdp-option-item">
				<?php printf(
                    '<input type="checkbox" name="_epdp_disable_default_welcome" id="epdp-disable-default-welcome" value="1"%s>',
                    checked( 1, $disable_default_welcome, false )
                ); ?> <label for="epdp-disable-default-welcome"><?php _e( 'Hide default welcome panel?', 'epd-premium' ); ?></label>
			</div>
            <div class="epdp-option-item epdp-inline">
				<?php printf(
                    '<input type="checkbox" name="_epdp_add_custom_welcome" id="epdp-add-custom-welcome" value="1"%s>',
                    checked( 1, $add_custom_welcome, false )
                ); ?> <label for="epdp-add-custom-welcome"><?php _e( 'Add custom welcome panel?', 'epd-premium' ); ?></label>
			</div>
		</div>
	</div>

	<?php echo ob_get_clean();
} // epdp_output_site_settings_welcome_metabox
add_action( 'epdp_after_settings_repeatable_rows', 'epdp_output_site_settings_welcome_metabox' );

/**
 * Output the settings for the custom welcome panel within the settings meta box.
 *
 * @since   1.2
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_site_settings_custom_welcome_metabox( $post )    {
    $custom_welcome = epdp_get_demo_custom_welcome_panel_setting( $post->ID );
	$class          = epdp_get_demo_add_custom_welcome_panel_setting( $post->ID ) ? '' : ' epdp-hidden';

    ob_start(); ?>

	<div id="epdp-welcome-editor" class="epdp_repeatable_option_wrapper epdp_repeatable_row<?php echo $class; ?>">
		<div class="epdp-option-row">
			<div id="" class="epdp-option-item">
				<span class="epdp-repeatable-row-setting-label">
					<label for="epdp-custom-welcome-message">
						<?php _e( 'Custom welcome panel message', 'epd-premium' ); ?>
					</label>
				</span>
				<?php wp_editor(
					$custom_welcome,
					'epdp-custom-welcome-message',
					array(
						'textarea_name' => '_epdp_custom_welcome_message',
						'textarea_rows' => 10,
						'editor_class'  => 'epdp_welcome_editor'
					)
				); ?>
			</div>
		</div>
	</div>

	<?php echo ob_get_clean();
} // epdp_output_site_settings_custom_welcome_metabox
add_action( 'epdp_after_settings_repeatable_rows', 'epdp_output_site_settings_custom_welcome_metabox' );

/**
 * Output the settings for the site upload space within the settings meta box.
 *
 * @since   1.3.6
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_site_settings_upload_space_metabox( $post )    {
    $upload_space = epdp_get_demo_site_upload_space( $post->ID );

    ob_start(); ?>

	<div class="epdp_repeatable_option_wrapper epdp_repeatable_row">
		<div class="epdp-option-row">
			<div class="epdp-option-item">
				<span class="epdp-repeatable-row-setting-label"><?php _e( 'Site upload space', 'epd-premium' ); ?></span>
				<?php printf(
					'<input type="number" name="_epdp_upload_space" id="site-upload-space" class="epdp_input" style="width: 100px !important;" value="%s" min="0" max="99999" step="1"> MB',
					$upload_space
				); ?>
			</div>
		</div>
	</div>

	<?php echo ob_get_clean();
} // epdp_output_site_settings_upload_space_metabox
add_action( 'epdp_after_settings_repeatable_rows', 'epdp_output_site_settings_upload_space_metabox' );

/**
 * Output the site action options meta box.
 *
 * @since   1.2
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_site_action_options_metabox( $post )   {
    $action          = epdp_get_demo_registration_action( $post->ID );
    $redirect_page   = epdp_get_demo_redirect_page( $post->ID );
    $pages           = get_pages();
    $redirect_hidden = 'redirect' != $action ? ' epdp-hidden' : '';

    $options = apply_filters( 'epd_registration_actions', array(
		'confirm'  => __( 'Show Confirmation', 'epd-premium' ),
		'home'     => __( 'Visit Home Page', 'epd-premium' ),
		'admin'    => __( 'Visit Admin', 'epd-premium' ),
		'redirect' => __( 'Redirect to Page', 'epd-premium' )
	) );

    ob_start(); ?>

    <div id="epdp_options">
        <div id="epdp_option_fields" class="epdp_meta_table_wrap">
            <div class="widefat epdp_repeatable_table">
                <?php do_action( 'epdp_demo_options_table_head', $post->ID ); ?>

                <div class="epdp-options-header">
                    <span class="epdp-options-title"><?php _e( 'Actions', 'epd-premium' ); ?></span>
                </div>
                <div class="epdp-site-options epdp-repeatables-wrap">
					<div class="epdp_repeatable_option_wrapper epdp_repeatable_row">
						<div class="epdp-option-row">
							<div class="epdp-option-item">
								<span class="epdp-repeatable-row-setting-label"><?php _e( 'Registration action', 'epd-premium' ); ?></span>
                                <select name="_epdp_registration_action" id="epdp-registration-action" class="epdp_select_chosen">
                                    <?php foreach( $options as $key => $option ) : ?>
                                        <?php printf(
                                            '
                                            <option value="%s"%s>%s</option>',
                                                esc_attr( $key ),
                                                selected( $action, $key, false ),
                                                $option
                                        ); ?>
                                    <?php endforeach; ?>
                                </select>
							</div>
                            <div id="epdp-redirect-page" class="epdp-option-item epdp-inline<?php echo $redirect_hidden; ?>">
                                <span class="epdp-repeatable-row-setting-label"><?php _e( 'Redirect to', 'epd-premium' ); ?></span>
                                <select name="_epdp_redirect_page" id="epdp-demo-redirect-page" class="epdp_select_chosen">
                                    <?php foreach( $pages as $page ) : ?>
                                        <?php printf(
                                            '<option value="%s"%s>%s</option>',
                                            esc_attr( $page->ID ),
                                            selected( $page->ID, $redirect_page, false ),
                                            get_the_title( $page )
                                        ); ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php do_action( 'epdp_after_options_repeatable_rows', $post ); ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo ob_get_clean();
} // epdp_output_site_action_options_metabox
add_action( 'epdp_demo_site_options_fields', 'epdp_output_site_action_options_metabox' );

/**
 * Output the options for auto login within the options meta box.
 *
 * @since   1.5.1
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_auto_login_options_metabox( $post )   {
	$auto_login = epdp_get_demo_auto_login_option( $post->ID );

    ob_start(); ?>

	<div class="epdp_repeatable_option_wrapper epdp_repeatable_row">
		<div class="epdp-option-row">
			<div class="epdp-option-item">
				<?php printf(
                    '<input type="checkbox" name="_epdp_auto_login" id="epdp-auto-login" value="1"%s>',
                    checked( 1, $auto_login, false )
                ); ?> <label for="epdp-auto-login"><?php _e( 'Log user in automatically?', 'epd-premium' ); ?></label>
			</div>
		</div>
	</div>

	<?php echo ob_get_clean();
} // epdp_output_auto_login_options_metabox
add_action( 'epdp_after_options_repeatable_rows', 'epdp_output_auto_login_options_metabox' );

/**
 * Output the options for site expiry within the options meta box.
 *
 * @since   1.2
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_site_expiry_options_metabox( $post )   {
    $lifetime   = epdp_get_demo_site_lifetime( $post->ID );
	$lifetime   = empty( $lifetime ) ? epd_get_default_site_lifetime() : $lifetime;

    ob_start(); ?>

	<div class="epdp_repeatable_option_wrapper epdp_repeatable_row">
		<div class="epdp-option-row">
			<div class="epdp-option-item">
				<span class="epdp-repeatable-row-setting-label"><?php _e( 'Delete sites after', 'epd-premium' ); ?></span>
				<select name="_epdp_delete_site_after" id="delete-site-after" class="epdp_select_chosen">
					<?php foreach( epd_get_lifetime_options() as $interval => $label ) : ?>
			
						<?php printf(
							'<option value="%s"%s>%s</option>',
							$interval,
							selected( $interval, $lifetime, false ),
							$label
						); ?>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>

	<?php echo ob_get_clean();
} // epdp_output_site_expiry_options_metabox
add_action( 'epdp_after_options_repeatable_rows', 'epdp_output_site_expiry_options_metabox' );

/**
 * Output the site cloning options meta box.
 *
 * @since   1.2
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_site_cloning_options_metabox( $post )   {
    $clone          = epdp_get_demo_clone_site( $post->ID );
	$plugins_hidden = empty( $clone ) ? ' epdp-hidden' : '';
	$plugins_option = epdp_get_demo_clone_plugin_action( $post->ID );

    ob_start(); ?>

    <div id="epdp_options">
        <div id="epdp_option_fields" class="epdp_meta_table_wrap">
            <div class="widefat epdp_repeatable_table">
                <?php do_action( 'epdp_demo_cloning_table_head', $post->ID ); ?>

                <div class="epdp-options-header">
                    <span class="epdp-options-title"><?php _e( 'Clone Options', 'epd-premium' ); ?></span>
                </div>
                <div class="epdp-clone-options epdp-repeatables-wrap">
					<div class="epdp_repeatable_option_wrapper epdp_repeatable_row">
						<div class="epdp-option-row">
							<div class="epdp-option-item">
								<span class="epdp-repeatable-row-setting-label"><?php _e( 'Clone from site', 'epd-premium' ); ?></span>
                                <select name="_epdp_clone_site" id="epdp-clone-site" class="epdp_select_chosen">
                                    <?php foreach( epdp_get_site_setting_options() as $id => $name ) : ?>
                                        <?php printf(
                                            '
                                            <option value="%s"%s>%s</option>',
                                                esc_attr( $id ),
                                                selected( $clone, $id, false ),
                                                $name
                                        ); ?>
                                    <?php endforeach; ?>
                                </select>
							</div>
							<div id="epdp-clone-plugins" class="epdp-option-item epdp-inline<?php echo $plugins_hidden; ?>">
                                <span class="epdp-repeatable-row-setting-label"><?php _e( 'Plugins action', 'epd-premium' ); ?></span>
                                <select name="_epdp_clone_plugins_action" id="epdp-clone-plugins-action" class="epdp_select_chosen">
                                    <?php foreach( epdp_get_plugin_setting_options() as $value => $label ) : ?>
                                        <?php printf(
                                            '<option value="%s"%s>%s</option>',
                                            esc_attr( $value ),
                                            selected( $value, $plugins_option, false ),
                                            esc_attr( $label )
                                        ); ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php do_action( 'epdp_after_cloning_repeatable_rows', $post, $clone ); ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo ob_get_clean();
} // epdp_output_site_cloning_options_metabox
add_action( 'epdp_demo_cloning_fields', 'epdp_output_site_cloning_options_metabox' );

/**
 * Ouput the themes cloning action row in the cloning metabox
 *
 * @since	1.2
 * @param	object  $post   WP_Post object
 * @param	string	$clone	Clone option
 * @return  void
 */
function epdp_output_theme_cloning_options_metabox( $post, $clone )	{
	$themes_hidden = empty( $clone ) ? ' epdp-hidden' : '';
	$themes_option = epdp_get_demo_clone_theme_action( $post->ID );

	ob_start(); ?>

	<div id="epdp-clone-themes" class="epdp_repeatable_theme_wrapper epdp_repeatable_row<?php echo $themes_hidden; ?>">
		<div class="epdp-option-row">
			<div class="epdp-option-item">
				<span class="epdp-repeatable-row-setting-label"><?php _e( 'Themes action', 'epd-premium' ); ?></span>
				<select name="_epdp_clone_themes_action" id="clone-themes-action" class="epdp_select_chosen">
					<?php foreach( epdp_get_theme_setting_options() as $value => $label ) : ?>
						<?php printf(
							'<option value="%s"%s>%s</option>',
							esc_attr( $value ),
							selected( $value, $themes_option, false ),
							esc_attr( $label )
						); ?>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>

	<?php echo ob_get_clean();
} // epdp_output_theme_cloning_options_metabox
add_action( 'epdp_after_cloning_repeatable_rows', 'epdp_output_theme_cloning_options_metabox', 10, 2 );

/**
 * Output the theme row in the themes/plugins meta box.
 *
 * @since   1.2
 * @param   object  $post   WP_Post object
 * @return  void
 */
function epdp_output_theme_options_metabox( $post ) {
    $selected_theme  = epdp_get_demo_theme( $post->ID );
	$hide_appearance = epdp_get_demo_hide_appearance_menu( $post->ID );

    ob_start(); ?>

	<div id="epdp_themes">
        <div id="epdp_theme_fields" class="epdp_meta_table_wrap">
            <div class="widefat epdp_repeatable_table">
                <?php do_action( 'epdp_demo_themes_table_head', $post->ID ); ?>

                <div class="epdp-theme-options epdp-repeatables-wrap">
					<div class="epdp-themes-header">
						<span class="epdp-themes-title"><?php _e( 'Theme Options', 'epd-premium' ); ?></span>
					</div>
					<div class="epdp_repeatable_theme_wrapper epdp_repeatable_row">
						<div class="epdp-theme-row">
							<div class="epdp-theme-item">
								<span class="epdp-repeatable-row-setting-label"><?php _e( 'Select a theme for this demo', 'epd-premium' ); ?></span>
								<select name="_epdp_theme" id="demo-theme" class="epdp_select_chosen">
									<?php foreach( wp_get_themes() as $stylesheet => $theme ) : ?>
										<?php printf(
											'<option value="%s"%s>%s</option>',
											esc_attr( $stylesheet ),
											selected( $selected_theme, $theme->stylesheet, false ),
											esc_html( $theme->Name )
										); ?>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="epdp-theme-hide-menu">
								<?php printf(
									'<input type="checkbox" name="_epdp_hide_appearance_menu" id="hide-appearance-menu" value="1" class="epdp-checkbox"%s>',
									checked( true, $hide_appearance, false )
								); ?> <label for="hide-appearance-menu"><?php _e( 'Hide appearance menu on demo site?', 'epd-premium' ); ?></label>
							</div>
						</div>
					</div>
					<?php do_action( 'epdp_after_theme_repeatable_rows', $post ); ?>
				</div>
			</div>
		</div>
	</div>

    <?php echo ob_get_clean();
} // epdp_output_theme_options_metabox
add_action( 'epdp_demo_theme_plugin_fields', 'epdp_output_theme_options_metabox', 10 );

/**
 * Ouput the available themes row in the themes/plugins metabox
 *
 * @since	1.2
 * @param	object  $post   WP_Post object
 * @return  void
 */
function epdp_output_additional_themes_options_metabox( $post )	{
	$allowed_themes = epdp_get_demo_allowed_themes( $post->ID );
	$selected_theme = epdp_get_demo_theme( $post->ID );
	$themes_options = array();
	$themes         = wp_get_themes();
	$maybe_hidden   = epdp_get_demo_hide_appearance_menu( $post->ID ) ? ' epdp-hidden' : '';

	ob_start(); ?>

	<div id="epdp-allowed-themes" class="epdp_repeatable_theme_wrapper epdp_repeatable_row<?php echo $maybe_hidden; ?>">
		<div class="epdp-theme-row">
			<div class="epdp-theme-item">
				<span class="epdp-repeatable-row-setting-label"><?php _e( 'Select available themes', 'epd-premium' ); ?></span>
				<select name="_epdp_available_themes[]" id="available-themes" class="epdp_select_chosen" multiple>
					<?php foreach( $themes as $stylesheet => $theme ) : ?>
						<?php if ( $stylesheet == $selected_theme )	: continue; endif; ?>

						<?php printf(
							'<option value="%s"%s>%s</option>',
							$stylesheet,
							selected( 1, in_array( $stylesheet, $allowed_themes ), false ),
							$theme
						); ?>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>

	<?php echo ob_get_clean();
} // epdp_output_additional_themes_options_metabox
add_action( 'epdp_after_theme_repeatable_rows', 'epdp_output_additional_themes_options_metabox', 10 );

/**
 * Output the plugin row in the themes/plugins meta box.
 *
 * @since   1.2
 * @param   object  $post   WP_Post object
 * @return  void
 */
function epdp_output_plugin_options_metabox( $post ) {
	$hide_plugins   = epdp_get_demo_hide_plugins_menu( $post->ID );
    $plugins        = epdp_get_demo_plugins( $post->ID );
    $plugin_options = epd_get_non_network_enabled_plugins();

    ob_start(); ?>

    <div id="epdp_plugins">
        <div id="epdp_plugin_fields" class="epdp_meta_table_wrap">
            <div class="widefat epdp_repeatable_table">
                <?php do_action( 'epdp_demo_plugins_table_head', $post->ID ); ?>

                <div class="epdp-plugins-select epdp-repeatables-wrap">
					<div class="epdp-plugins-header">
						<span class="epdp-plugins-title"><?php _e( 'Plugin Options', 'epd-premium' ); ?></span>
					</div>
                    <?php if ( $plugins ) : ?>
						<div class="epdp_repeatable_plugin_wrapper epdp_repeatable_row">
							<div class="epdp-plugin-row">
								<div class="epdp-hide-plugin-menu">
									<?php printf(
										'<input type="checkbox" name="_epdp_hide_plugins_menu" id="hide-plugins-menu" value="1" class="epdp-checkbox"%s>',
										checked( true, $hide_plugins, false )
									); ?> <label for="hide-plugins-menu"><?php _e( 'Hide plugins menu on demo site?', 'epd-premium' ); ?></label>
								</div>
							</div>
						</div>
						<?php $index = 1; ?>
						<?php foreach ( $plugins as $key => $plugin ) : ?>
							<?php $checked = isset( $plugins_active[ $index ] ) ? ' checked' : ''; ?>
							<div class="epdp_repeatable_plugin_wrapper epdp_repeatable_row" data-key="<?php echo esc_attr( $index ); ?>">
								<div class="epdp-plugin-row">
									<div class="epdp-plugin-item">
										<input type="hidden" name="epdp_plugins[<?php echo $index; ?>][index]" class="epdp_repeatable_index" value="<?php echo $index; ?>"/>
										<span class="epdp-repeatable-row-setting-label"><?php _e( 'Activate Plugin', 'epd-premium' ); ?></span>
										<select name="_epdp_plugins[]" id="epdp_plugins_<?php echo $index; ?>" class="epdp_select_chosen">
											<option value="0"><?php _e( 'Select a Plugin', 'epd-premium' ); ?></option>
											<?php foreach( $plugin_options as $file => $name ) : ?>
												<?php printf(
													'<option value="%s"%s>%s</option>',
													$file,
													selected( $file, $plugin, false ),
													$name
												); ?>
											<?php endforeach; ?>
										</select>
									</div>
									<span class="epdp-plugin-actions">
										<a class="epdp-remove-row epdp-delete" data-type="file"><?php printf( __( 'Remove', 'epd-premium' ), $index ); ?><span class="screen-reader-text"><?php printf( __( 'Remove plugin', 'epd-premium' ), $index ); ?></span></a>
									</span>
									<?php do_action( 'epdp_demo_plugins_table_row', $post->ID ); ?>
								</div>
							</div>
							<?php $index++; ?>
						<?php endforeach; ?>

                    <?php else : ?>
                        <div class="epdp_repeatable_plugin_wrapper epdp_repeatable_row" data-key="1">
                            <div class="epdp-plugin-row">
                                <div class="epdp-plugin-item">
                                    <input type="hidden" name="epdp_plugins[1][index]" class="epdp_repeatable_index" value="1"/>
                                    <span class="epdp-repeatable-row-setting-label"><?php _e( 'Activate Plugins', 'epd-premium' ); ?></span>
                                    <select name="_epdp_plugins[]" id="epdp_plugins_1" class="epdp_select_chosen">
                                        <option value="0"><?php _e( 'Select a Plugin', 'epd-premium' ); ?></option>
                                        <?php foreach( $plugin_options as $file => $name ) : ?>
                                            <?php printf(
                                                '<option value="%s">%s</option>',
                                                $file,
                                                $name
                                            ); ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <span class="epdp-plugin-actions">
									<a class="epdp-remove-row epdp-delete" data-type="file"><?php printf( __( 'Remove', 'epd-premium' ) ); ?><span class="screen-reader-text"><?php __( 'Remove plugin 1', 'epd-premium' ); ?></span></a>
								</span>
								<?php do_action( 'epdp_ddemo_plugins_table_row', $post->ID ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="epdp-add-repeatable-row">
						<div class="submit" style="float: none; clear:both; background: #fff;">
							<button class="button-secondary epdp_add_repeatable"><?php _e( 'Activate Additional Plugin', 'epd-premium' ); ?></button>
						</div>
					</div>

                </div>
            </div>
        </div>
    </div>

    <?php echo ob_get_clean();
} // epdp_output_plugin_options_metabox
add_action( 'epdp_demo_theme_plugin_fields', 'epdp_output_plugin_options_metabox', 15 );

/**
 * Output the notices meta box.
 *
 * @since   1.4
 * @param   object  $post   The WP_Post object
 * @return  void
 */
function epdp_output_notices_metabox( $post )   {
    $all_notices     = epdp_get_notices();
    $demo_notices    = epdp_get_demo_notices( $post->ID );
    $display_options = epdp_get_notice_display_options_list();
    $add_url         = add_query_arg( array(
        'page'       => 'epdp-edit-notices',
        'epd_action' => 'add_notice'
    ), network_admin_url( 'settings.php' ) );

    ob_start(); ?>

    <div id="epdp_notices">
        <div id="epdp_notice_fields" class="epdp_meta_table_wrap">
            <div class="widefat epdp_repeatable_table">
                <?php do_action( 'epdp_demo_notices_table_head', $post->ID ); ?>

                <div class="epdp-notices-header">
                    <span class="epdp-notices-title"><?php _e( 'Select Active Notices', 'epd-premium' ); ?></span>
                </div>
                <div class="epdp-site-notices epdp-repeatables-wrap">
                    <?php if ( ! empty( $all_notices ) ) : ?>
                        <div class="epdp_repeatable_option_wrapper epdp_repeatable_row">
                            <?php foreach( $all_notices as $notice_id => $notice ) : ?>
                                <div class="epdp-option-row">
                                    <div class="epdp-option-item">
                                        <?php printf(
                                            '<input type="checkbox" name="_epdp_notices[]" id="epdp-notices-%s" value="%s"%s>',
                                            $notice_id,
                                            $notice['slug'],
                                            checked( true, in_array( $notice['slug'], $demo_notices ), false )
                                        ); ?> <label for="epdp-notices-<?php echo $notice_id; ?>">
                                                <?php printf( '%s (%s)',
                                                    esc_html( stripslashes( $notice['name'] ) ),
                                                    esc_html( stripslashes( $display_options[ $notice['display'] ] ) )
                                                ); ?>
                                            </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="epdp_repeatable_option_wrapper epdp_repeatable_row">
                            <div class="epdp-option-row">
                                <div class="epdp-option-item">
                                    <?php printf(
                                        __( 'You have not defined any notices. <a href="%s">Add notice</a>.', 'epd-premium' ),
                                        esc_url( $add_url )
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
			</div>
		</div>
	</div>

    <?php echo ob_get_clean();
} // epdp_output_notices_metabox
add_action( 'epdp_demo_notices_fields', 'epdp_output_notices_metabox' );
