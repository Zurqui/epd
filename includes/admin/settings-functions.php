<?php
/**
 * Register Settings.
 *
 * @package     EPD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2019, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Add the sites settings sections.
 *
 * @since	1.0
 * @param	array	$sections	Array of setting tab sections
 * @return	array	Array of setting tab sections
 */
function epdp_add_site_settings_sections_action( $sections )	{
	$sections['users'] = __( 'Users', 'epd-premium' );
    $sections['clone'] = __( 'Cloning', 'epd-premium' );
	$sections['demos'] = __( 'Demo Templates', 'epd-premium' );

	return $sections;
} // epdp_add_settings_sections_action
add_filter( 'epd_settings_sections_sites', 'epdp_add_site_settings_sections_action' );

/**
 * Add the premium settings.
 *
 * @since	1.0
 * @param	array	$settings	Array of settings
 * @return	array	Array of settings
 */
function epdp_register_settings( $settings )	{
	$settings['sites']['clone'] = array(
        'clone_site_id' => array(
            'id'       => 'clone_site_id',
            'name'     => __( 'Clone Site', 'epd-premium' ),
            'type'     => 'select',
            'options'  => epdp_get_site_setting_options(),
            'chosen'   => true,
            'desc'     => __( 'If you select an existing site here, all new sites will be cloned from here.', 'epd-premium' ),
            'std'      => 0
        ),
        'clone_tables' => array(
            'id'       => 'clone_tables',
            'name'     => __( 'Clone Tables', 'epd-premium' ),
            'type'     => 'multicheck',
            'options'  => epdp_get_db_table_setting_options(),
            'desc'     => __( 'Select the tables you would like to duplicate to newly created sites.', 'epd-premium' ),
            'std'      => array()
        ),
        'plugins_action' => array(
            'id'       => 'plugins_action',
            'name'     => __( 'Plugins Action', 'epd-premium' ),
            'type'     => 'select',
            'options'  => epdp_get_plugin_setting_options(),
            'chosen'   => true,
            'desc'     => __( 'How do you want to manage plugins for the newly cloned site?', 'epd-premium' ),
            'std'      => 'clone'
        ),
        'themes_action' => array(
            'id'       => 'themes_action',
            'name'     => __( 'Themes Action', 'epd-premium' ),
            'type'     => 'select',
            'options'  => epdp_get_theme_setting_options(),
            'chosen'   => true,
            'desc'     => __( 'How do you want to manage plugins for the newly cloned site?', 'epd-premium' ),
            'std'      => 'clone'
        ),
        'exclude_options' => array(
            'id'       => 'exclude_options',
            'name'     => __( 'Ignore Options', 'epd-premium' ),
            'type'     => 'textarea',
            'desc'     => __( 'Enter the names of any options you do not wish to clone from the master site options database table. Names should be one per line and match the value within the <code>option_name</code> column.', 'epd-premium' )
        )
	);

	$settings['sites']['demos'] = array(
		'default_demo' => array(
			'id'      => 'default_demo',
			'name'    => __( 'Default Site Demo Template', 'epd-premium' ),
			'type'    => 'select',
			'chosen'  => true,
			'desc'    => __( 'If you select a template here, all new registrations that are not linked from a demo URL will be based on the template.', 'epd-premium' ),
			'std'     => 0,
			'options' => epdp_get_default_demo_setting_options()
		),
		'button_placement' => array(
			'id'      => 'button_placement',
			'name'    => __( 'Output Demo Button', 'epd-premium' ),
			'type'    => 'select',
			'chosen'  => true,
			'desc'    => __( 'Choose where to place the Launch Demo button by default.', 'epd-premium' ),
			'std'     => 'after_post',
			'options' => epdp_get_button_placement_setting_options()
		),
		'button_text' => array(
			'id'      => 'button_text',
			'name'    => __( 'Button Text', 'epd-premium' ),
			'type'    => 'text',
			'desc'    => __( 'Text to be displayed on the button.', 'epd-premium' ),
			'std'     => __( 'Launch Demo', 'epd-premium' )
		)
	);

	return $settings;
} // epdp_register_settings
add_filter( 'epd_registered_settings', 'epdp_register_settings' );

/**
 * Add user and role settings.
 *
 * @since   1.0
 * @param   array   $settings   Array of settings
 * @return  array   Array of settings
 */
function epdp_register_user_role_settings( $settings )   {
    $options = array();
    $roles   = get_editable_roles();
    $users   = get_users( array(
        'blog_id'      => get_network()->blog_id,
        'number'       => -1,
        'orderby'      => 'display_name',
        'order'        => 'ASC'
    ) );

    foreach( $users as $user )  {
        if ( is_super_admin( $user->ID ) )  {
            continue;
        }

        $options[ $user->ID ] = $user->display_name;
    }

    foreach( $roles as $role_name => $role_info )    {
        $key = 'blog_' . $role_name;
        $settings['sites']['users'][ $key ] = array(
            'id'          => $key,
            'name'        => sprintf( __( 'Add %s', 'epd-premium' ), $role_info['name'] . "'s" ),
            'type'        => 'select',
            'options'     => $options,
            'multiple'    => true,
            'chosen'      => true,
            'std'         => 0,
            'desc'        => sprintf( __( 'List of users to be granted the <strong>%s</strong> capability for new sites.', 'epd-premium' ), strtolower( $role_info['name'] ) ),
            'placeholder' => __( 'Type to search users', 'epd-premium' )
        );
    }

    return $settings;
} // epdp_register_user_role_settings
add_filter( 'epd_registered_settings', 'epdp_register_user_role_settings' );

/**
 * Add author setting.
 *
 * @since   1.0
 * @param   array   $settings   Array of settings
 * @return  Array of settings
 */
function epdp_set_post_author_option( $settings )   {
    $settings[ 'set_author' ] = array(
        'id'       => 'set_author',
        'name'     => __( 'Set Author As', 'epd-premium' ),
        'type'     => 'select',
        'options'  => epdp_get_post_author_setting_options(),
        'std'      => 'current',
        'desc'     => __( 'Who should be defined as the author for replicated content?.', 'epd-premium' )
    );

    return $settings;
} // epdp_set_post_author_option
add_filter( 'epd_posts_pages_options', 'epdp_set_post_author_option', 9 );

/**
 * Restrieve a list of options for the default demo setting.
 *
 * @since   1.3.1
 * @return  array   Array of options
 */
function epdp_get_default_demo_setting_options() {
    $options = array(
        0        => __( 'None', 'epd-premium' )
    );

	$demos = epdp_get_demos();

	foreach( $demos as $demo )	{
		$options[ $demo->ID ] = get_the_title( $demo );
	}

	$options = apply_filters( 'epdp_default_demo_setting_options', $options );

    return $options;
} // epdp_get_default_demo_setting_options

/**
 * Restrieve a list of options for the button placement setting.
 *
 * @since   1.2
 * @return  array   Array of options
 */
function epdp_get_button_placement_setting_options() {
    $options = array(
        'none'        => __( 'Do not add button', 'epd-premium' ),
		'before_post' => __( 'Before content', 'epd-premium' ),
		'after_post'  => __( 'After content', 'epd-premium' ),
		'both'        => __( 'Both before and after content', 'epd-premium' )
    );

	$options = apply_filters( 'epdp_button_placement_setting_options', $options );

    return $options;
} // epdp_get_button_placement_setting_options

/**
 * Restrieve a list of options for the post author setting.
 *
 * @since   1.0
 * @return  array   Array of options
 */
function epdp_get_post_author_setting_options() {
    $options = array(
        'current'    => __( 'Leave as current author', 'epd-premium' ),
        'blog_owner' => __( 'Set as blog owner', 'epd-premium' )
    );

    $users = get_users( array(
        'blog_id'  => get_network()->blog_id,
        'number'   => -1,
        'role__in' => array( 'super_admin', 'administrator', 'editor', 'author', 'contributor' )
    ) );

    if ( ! empty( $users ) )    {
        foreach( $users as $user )  {
            $options[ $user->ID ] = $user->display_name;
        }
    }

    return $options;
} // epdp_get_post_author_setting_options

/**
 * Restrieve a list of users for the user role settings.
 *
 * @since   1.0
 * @return  array   Array of options
 */
function epdp_get_user_role_setting_options() {
    $users = get_users( array(
        'blog_id'  => get_network()->blog_id,
        'number'   => -1
    ) );

    if ( ! empty( $users ) )    {
        foreach( $users as $user )  {
            $options[ $user->ID ] = $user->display_name;
        }
    }

    return $options;
} // epdp_get_user_role_setting_options

/**
 * Retrieve a list of sites for the settings field.
 *
 * @since	1.0
 * @return	array	Array of site ID's => Names
 */
function epdp_get_site_setting_options()	{
	$options = array(
        0 => __( 'None', 'epd-premium' )
    );

	$sites = get_sites();

	if ( $sites )	{
		foreach( $sites as $site )	{
            if ( is_subdomain_install() ) {
                $name = $site->domain;
            } else  {
                $name = $site->domain . $site->path;
            }
			$options[ $site->blog_id ] = $name;
		}
	}

	return $options;
} // epdp_get_site_setting_options

/**
 * Retrieve a list of database table options
 *
 * @since   1.0
 * @param	int		$site_id	Site ID from which to retrieve DB tables
 * @return  array   Array of database table options
 */
function epdp_get_db_table_setting_options( $site_id = 0 )   {
    global $wpdb;

    $options = array();
    $query   = "
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema='{$wpdb->dbname}'
    ";

    $results = $wpdb->get_results( $query );
    $site_id = ! empty( $site_id ) ? $site_id : epdp_get_clone_master();
    $site_id = ! empty( $site_id ) ? $site_id : null;
    $prefix  = $wpdb->get_blog_prefix( $site_id );

    if ( ! empty( $results ) )  {
        foreach( $results as $tables )  {
            $obj_name = isset( $tables->table_name ) ? $tables->table_name : $tables->TABLE_NAME;
            if ( preg_match( "/\A({$prefix})([a-z].*)/i", $obj_name ) ) {
                $table_name    = str_replace( $prefix, '', $obj_name );
                $ignore_tables = array_merge( $wpdb->global_tables, $wpdb->ms_global_tables );

                if ( ! in_array( $table_name, $ignore_tables ) )   {
                    $options[ $table_name ] = $table_name;
                }
            }
        }
    }

    return $options;
} // epdp_get_db_table_setting_options

/**
 * Retrieve a list of plugin options on clone
 *
 * @since   1.0
 * @return  array   Array of plugin options
 */
function epdp_get_plugin_setting_options()   {
    global $wpdb;

    $options = array(
        'clone' => __( 'Replicate From Clone Site', 'epd-premium' ),
        'honor' => __( 'Honor Enable Plugins Setting', 'epd-premium' ),
        'none'  => __( 'Do Nothing', 'epd-premium' )
    );

    return $options;
} // epdp_get_plugin_setting_options

/**
 * Retrieve a list of theme options on clone
 *
 * @since   1.0
 * @return  array   Array of plugin options
 */
function epdp_get_theme_setting_options()   {
    global $wpdb;

    $options = array(
        'clone' => __( 'Replicate From Clone Site', 'epd-premium' ),
        'honor' => __( 'Honor Themes Settings', 'epd-premium' )
    );

    return $options;
} // epdp_get_theme_setting_options

/**
 * Adds the option to clone all posts within settings.
 *
 * @since   1.0
 * @param   array   $post_options   Array of setting options for posts
 * @param   string  $post_type      Post type
 * @param   object  $post_object    The current post type object
 * @return  Array of settings options for posts
 */
function epdp_add_replicate_all_posts_option_action( $post_options, $post_type, $post_object )    {
    $key = "replicate_all_$post_type";
    $post_options[ $key ] = array(
        'id'       => $key,
        'name'     => sprintf( __( 'Replicate All %s', 'epd-premium' ), $post_object->label ),
        'type'     => 'checkbox',
        'std'      => false,
        'desc'     => sprintf(
            __( 'Select to replicate all <strong>%1$s</strong>. The <code>Replicate %1$s</code> option will be ignored if this option is enabled.', 'epd-premium' ),
            strtolower( $post_object->label )
        )
    );

    return $post_options;
} // epdp_add_replicate_all_posts_option_action
add_filter( 'epd_post_type_options_after_header', 'epdp_add_replicate_all_posts_option_action', 10, 3 );

/**
 * Adds the option to include post data.
 *
 * @since   1.0
 * @param   array   $post_options   Array of setting options for posts
 * @param   string  $post_type      Post type
 * @param   object  $post_object    The current post type object
 * @return  Array of settings options for posts
 */
function epdp_add_replicate_post_data_option_action( $post_options, $post_type, $post_object )    {

	$ignore_for_comments = apply_filters( 'epdp_replicate_comments_option_ignore_list', array(
        'attachment'
    ) );

    $ignore_for_taxonomies = apply_filters( 'epdp_replicate_taxonomy_terms_option_ignore_list', array(
        'attachment'
    ) );

	if ( ! in_array( $post_type, $ignore_for_taxonomies ) ) {
		$key = "replicate_comments_$post_type";
		$post_options[ $key ] = array(
			'id'       => $key,
			'name'     => __( 'Include Comments?', 'epd-premium' ),
			'type'     => 'checkbox',
			'std'      => false,
			'desc'     => sprintf(
				__( 'Select to include comments for the %s.', 'epd-premium' ), $post_object->label
			)
		);
	}

    if ( ! in_array( $post_type, $ignore_for_taxonomies ) ) {

        $key = "replicate_tax_$post_type";
        $post_options[ $key ] = array(
            'id'       => $key,
            'name'     => __( 'Include Taxonomy Terms?', 'epd-premium' ),
            'type'     => 'checkbox',
            'std'      => false,
            'desc'     => __( 'Select to include taxonomy terms as part of the post replication.', 'epd-premium' )
        );

    }

	$key = "replicate_media_$post_type";
	$post_options[ $key ] = array(
		'id'       => $key,
		'name'     => __( 'Include Media?', 'epd-premium' ),
		'type'     => 'checkbox',
		'std'      => false,
		'desc'     => sprintf(
			__( 'Select to include any media attached to the %s.', 'epd-premium' ), $post_object->label
		)
	);

    return $post_options;
} // epdp_add_replicate_post_data_option_action
add_filter( 'epd_post_type_options_after_posts', 'epdp_add_replicate_post_data_option_action', 15, 3 );
