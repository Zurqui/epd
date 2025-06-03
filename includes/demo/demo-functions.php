<?php
/**
 * Demo Functions
 *
 * @package     EPD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve the number of times a template has been used.
 *
 * @since   1.5.2
 * @param   int     $demo_id    ID of the demo template
 * @return  int     Number of times the template has been used
 */
function epdp_get_template_build_count( $demo_id )    {
    $count = get_post_meta( $demo_id, '_epd_build_count', true );
    $count = absint( $count );

    return $count;
} // epdp_get_template_build_count

/**
 * Increment the number of times a template has been used.
 *
 * @since   1.5.2
 * @param   int     $demo_id    ID of the demo template
 * @param   int     $increase   The number to increase the count by
 * @return  int     Number of times the template has been used
 */
function epdp_increment_template_build_count( $demo_id, $increase = 1 )    {
    $current = epdp_get_template_build_count( $demo_id );
    $updated = $current + $increase;

    update_post_meta( $demo_id, '_epd_build_count', $updated );

    return epdp_get_template_build_count( $demo_id );
} // epdp_increment_template_build_count

/**
 * Retrieve available demos
 *
 * @since	1.2
 * @param	array	$args	Array of args for get_posts()
 * @return	array	Array of demo WP_Post objects
 */
function epdp_get_demos( $args = array() )	{
	$defaults = array(
		'post_type'      => 'epd_demo',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC'
	);

	$args = wp_parse_args( $args, $defaults );

	return get_posts( $args );
} // epdp_get_demos

/**
 * Get a demo URL.
 *
 * @since	1.2
 * @param	int		$demo_id	Demo ID
 * @return	string	Demo URL
 */
function epdp_get_demo_url( $demo_id )	{
	$page = epd_get_registration_page_url();
	$key  = epdp_get_demo_key( $demo_id );
	$link = add_query_arg( 'demo_ref', $key, $page );

	return $link;
} // epdp_get_demo_url

/**
 * Create a unique key for the demo.
 *
 * @since	1.2
 * @return	string	Unique key
 */
function epdp_create_demo_key()	{
	$auth_key  = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
	$site_url  = get_site_url();
	$key       = strtolower( md5( $site_url . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'epd', true ) ) );

	return $key;
} // epdp_create_demo_key

/**
 * Retrieve the demo key.
 *
 * @since	1.2
 * @param	int		$demo_id	Post ID
 * @return	string	Demo key
 */
function epdp_get_demo_key( $demo_id )	{
	$key = get_post_meta( $demo_id, '_epd_site_ref', true );

	return $key;
} // epdp_get_demo_key

/**
 * Retrieve demo ID from key.
 *
 * @since	1.2
 * @param	string	$key	Demo key
 * @return	int		Demo ID
 */
function epdp_get_demo_id_from_key( $key )	{
	global $wpdb;

	$demo_id = $wpdb->get_col( $wpdb->prepare(
		"
		SELECT post_id
		FROM {$wpdb->postmeta}
		WHERE meta_key ='_epd_site_ref'
		AND meta_value = '%s'
		",
		$key
	) );

	return ! empty( $demo_id ) ? $demo_id[0] : false;
} // epdp_get_demo_id_from_key

/**
 * Get the default demo to be used for standard registrations.
 *
 * @since	1.3.1
 * @return	int		The post ID of the default demo template
 */
function epdp_get_default_demo_template_id()	{
	return epd_get_option( 'default_demo' );
} // epdp_get_default_demo_template_id

/**
 * Retrieve demo template ID from demo site.
 *
 * @since	1.2
 * @param	int		$site_id	The site ID to retrieve the demo template ID from
 * @return	int		Demo template ID
 */
function epdp_get_site_demo_template_id( $site_id )	{
	$demo_id = get_site_meta( $site_id, 'epd_demo_template', true );

	return absint( $demo_id );
} // epdp_get_site_demo_template_id

/**
 * Get demo button placement for post.
 *
 * @since	1.2
 * @param	int		$demo_id	Post ID
 * @return	string	Demo button placement
 */
function epdp_demo_get_button_placement( $demo_id )	{
	$placement = get_post_meta( $demo_id, '_epdp_button_placement', true );
	$placement = empty( $placement ) ? epdp_get_button_placement() : $placement;
	$placement = apply_filters( 'epdp_demo_button_placement', $placement, $demo_id );

	return $placement;
} // epdp_demo_get_button_placement

/**
 * Get the site title to be used for new demo sites.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  string  The title of the site
 */
function epdp_get_demo_site_title( $demo_id )    {
    $title = ! empty( $demo_id ) ? get_post_meta( $demo_id, '_epdp_site_title', true ) : '';
    $title = apply_filters( 'epdp_demo_site_title', $title, $demo_id );

    return $title;
} // epdp_get_demo_site_title

/**
 * Get the site tag line to be used for new demo sites.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  string  The tag line of the site
 */
function epdp_get_demo_site_tag_line( $demo_id )    {
    $tag_line = ! empty( $demo_id ) ? get_post_meta( $demo_id, '_epdp_site_tag_line', true ) : '';
    $tag_line = apply_filters( 'epdp_demo_site_tag_line', $tag_line, $demo_id );

    return $tag_line;
} // epdp_get_demo_site_tag_line

/**
 * Get the discourage search engines setting.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  bool    Whether or not to discourage search engines
 */
function epdp_get_demo_discourage_search( $demo_id )    {
    $discourage = ! empty( $demo_id ) ? get_post_meta( $demo_id, '_epdp_discourage_search', true ) : '';
    $discourage = (bool) apply_filters( 'epdp_demo_discourage_search', $discourage, $demo_id );

    return $discourage;
} // epdp_get_demo_discourage_search

/**
 * Get the discourage search engines setting.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  bool    Whether or not to discourage search engines
 */
function epdp_get_demo_disable_visibility_setting( $demo_id )    {
    $setting = ! empty( $demo_id ) ? get_post_meta( $demo_id, '_epdp_disable_visibility', true ) : '';
    $setting = (bool) apply_filters( 'epdp_demo_disable_visibility', $setting, $demo_id );

    return $setting;
} // epdp_get_demo_disable_visibility_setting

/**
 * Get the disable default welcome panel setting.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  bool    Whether or not to default welcome panel
 */
function epdp_get_demo_disable_default_welcome_panel_setting( $demo_id )    {
    $disable = ! empty( $demo_id ) ? get_post_meta( $demo_id, '_epdp_disable_default_welcome', true ) : '';
    $disable = (bool) apply_filters( 'epdp_demo_disable_default_welcome', $disable, $demo_id );

    return $disable;
} // epdp_get_demo_disable_default_welcome_panel_setting

/**
 * Get the add custom welcome panel setting.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  bool    Whether or not to default welcome panel
 */
function epdp_get_demo_add_custom_welcome_panel_setting( $demo_id )    {
    $add = ! empty( $demo_id ) ? get_post_meta( $demo_id, '_epdp_add_custom_welcome', true ) : '';
    $add = (bool) apply_filters( 'epdp_demo_add_custom_welcome', $add, $demo_id );

    return $add;
} // epdp_get_demo_add_custom_welcome_panel_setting

/**
 * Get the custom welcome panel message.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  string  Custom welcome panel text
 */
function epdp_get_demo_custom_welcome_panel_setting( $demo_id )    {
    $message = ! empty( $demo_id ) ? get_post_meta( $demo_id, '_epdp_custom_welcome_message', true ) : '';
	$message = ! empty( $message ) ? $message : epd_get_option( 'custom_welcome' );
    $message = apply_filters( 'epdp_demo_custom_welcome_message', $message, $demo_id );

    return $message;
} // epdp_get_demo_custom_welcome_panel_setting

/**
 * Get the demo site upload space setting value.
 *
 * @since	1.4.1
 * @param	int		$demo_id	The demo ID
 * @return	int		The site upload space value
 */
function epdp_get_demo_site_upload_space( $demo_id )	{
	$upload_space = ! empty( $demo_id ) ? absint( get_post_meta( $demo_id, '_epdp_upload_space', true ) ) : 0;
	$upload_space = ! empty( $upload_space ) ? $upload_space : 0;
	$upload_space = apply_filters( 'epdp_demo_upload_space', $upload_space, $demo_id );

	return $upload_space;
} // epdp_get_demo_site_upload_space

/**
 * Get the user action upon site registration.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  string  The action on registration
 */
function epdp_get_demo_registration_action( $demo_id )  {
    $action = get_post_meta( $demo_id, '_epdp_registration_action', true );
    $action = apply_filters( 'epdp_demo_registration_action', $action, $demo_id );

    return $action;
} // epdp_get_demo_registration_action

/**
 * Get the redirect page.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  string  The page to redirect to
 */
function epdp_get_demo_redirect_page( $demo_id )  {
    $page = get_post_meta( $demo_id, '_epdp_redirect_page', true );
    $page = apply_filters( 'epdp_demo_redirect_page', $page, $demo_id );

    return $page;
} // epdp_get_demo_redirect_page

/**
 * Get the auto login setting for the demo.
 *
 * @since   1.5.1
 * @param   int     $demo_id    The demo ID
 * @return  bool	Whether or not the user should be auto logged in
 */
function epdp_get_demo_auto_login_option( $demo_id )  {
    $auto_login = get_post_meta( $demo_id, '_epdp_auto_login', true );
    $auto_login = (bool) apply_filters( 'epdp_demo_auto_login', $auto_login, $demo_id );

    return $auto_login;
} // epdp_get_demo_auto_login_option

/**
 * Get the site expiration option.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  int     Site lifetime in seconds
 */
function epdp_get_demo_site_lifetime( $demo_id )  {
    $delete = get_post_meta( $demo_id, '_epdp_delete_site_after', true );
    $delete = apply_filters( 'epdp_demo_delete_site_after', $delete, $demo_id );

    return $delete;
} // epdp_get_demo_site_lifetime

/**
 * Get the site cloning site.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  int     Site lifetime in seconds
 */
function epdp_get_demo_clone_site( $demo_id )  {
    $clone = get_post_meta( $demo_id, '_epdp_clone_site', true );
    $clone = apply_filters( 'epdp_clone_site', $clone, $demo_id );

    return absint( $clone );
} // epdp_get_demo_clone_site

/**
 * Get the site cloning plugin options.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  int     Plugins action
 */
function epdp_get_demo_clone_plugin_action( $demo_id )  {
    $plugins_action = get_post_meta( $demo_id, '_epdp_clone_plugins_action', true );
    $plugins_action = apply_filters( 'epdp_clone_plugins_action', $plugins_action, $demo_id );

    return $plugins_action;
} // epdp_get_demo_clone_plugin_action

/**
 * Get the site cloning theme options.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  int     Themes action
 */
function epdp_get_demo_clone_theme_action( $demo_id )  {
    $themes_action = get_post_meta( $demo_id, '_epdp_clone_themes_action', true );
    $themes_action = apply_filters( 'epdp_clone_themes_action', $themes_action, $demo_id );

    return $themes_action;
} // epdp_get_demo_clone_theme_action

/**
 * Get the theme assigned to the demo.
 *
 * @since   1.1
 * @param   int     $demo_id    The demo ID
 * @return  string  The name of the theme to use
 */
function epdp_get_demo_theme( $demo_id )    {
    $theme = ! empty( $demo_id ) ? get_post_meta( $demo_id, '_epdp_theme', true ) : '';

    if ( '' == $theme ) {
		switch_to_blog( get_network()->blog_id );
        $current_theme = wp_get_theme();
        $theme         = esc_attr( $current_theme->stylesheet );
    }

    $theme = apply_filters( 'epdp_demo_theme', $theme, $demo_id );

    return $theme;
} // epdp_get_demo_theme

/**
 * Whether or not to hide the appearance menu.
 *
 * @since	1.1
 * @param	int     $demo_id    The demo ID
 * @return	bool	True to hide, or false
 */
function epdp_get_demo_hide_appearance_menu( $demo_id )	{
	$hide_appearance = get_post_meta( $demo_id, '_epdp_hide_appearance_menu', true );
	$hide_appearance = '' == $hide_appearance ? false : true;

    $hide_appearance = (bool) apply_filters( 'epdp_demo_hide_appearance_menu', $hide_appearance, $demo_id );

	return $hide_appearance;
} // epdp_get_demo_hide_appearance_menu

/**
 * Retrieve themes allowed to be activated.
 *
 * @since	1.1
 * @param	int     $demo_id    The demo ID
 * @return	array	Array of available themes
 */
function epdp_get_demo_allowed_themes( $demo_id )	{
	$allowed = get_post_meta( $demo_id, '_epdp_available_themes', true );
	$allowed = '' == $allowed ? array() : $allowed;

    $allowed = apply_filters( 'epdp_demo_allowed_themes', $allowed, $demo_id );

	return $allowed;
} // epdp_get_demo_allowed_themes

/**
 * Whether or not to hide the plugins menu.
 *
 * @since	1.1
 * @param	int     $demo_id    The demo ID
 * @return	bool	True to hide, or false
 */
function epdp_get_demo_hide_plugins_menu( $demo_id )	{
	$hide_plugins = get_post_meta( $demo_id, '_epdp_hide_plugins_menu', true );
	$hide_plugins = '' == $hide_plugins ? false : true;

    $hide_plugins = (bool) apply_filters( 'epdp_demo_hide_plugins_menu', $hide_plugins, $demo_id );

	return $hide_plugins;
} // epdp_get_demo_hide_plugins_menu

/**
 * Retrieve the plugins that should be active in the demo.
 *
 * @since	1.1
 * @param	int     $demo_id    The demo ID
 * @return	array	Array of plugin data for demo
 */
function epdp_get_demo_plugins( $demo_id )	{
	$plugins = get_post_meta( $demo_id, '_epdp_plugins', true );
	$plugins = '' == $plugins ? array() : $plugins;

    $plugins = apply_filters( 'epdp_demo_plugins', $plugins, $demo_id );

	return $plugins;
} // epdp_get_demo_plugins

/**
 * Retrieve the notices that should be active for the demo.
 *
 * @since	1.4
 * @param	int     $demo_id    The demo ID
 * @return	array	Array of plugin data for demo
 */
function epdp_get_demo_notices( $demo_id )	{
	$notices = get_post_meta( $demo_id, '_epdp_notices', true );
	$notices = '' == $notices ? array() : $notices;

    $notices = apply_filters( 'epdp_demo_notices', $notices, $demo_id );

	return $notices;
} // epdp_get_demo_notices
