<?php
/**
 * EPDP Demo Builder Class
 *
 * @package		EPD
 * @subpackage	Demos/Sites
 * @since		2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * EPDP_Demo_Builder Class
 *
 * @since	2.0
 */
class EPDP_Demo_Builder {
    /**
	 * The ID of the demo from which to build the site
	 *
	 * @since	2.0
	 * @var		int
	 */
    private $demo_id = 0;

	/**
	 * The unique key for the demo
	 *
	 * @since	2.0
	 * @var		int
	 */
	private $demo_key;

	/**
	 * The new site ID
	 *
	 * @since	2.0
	 * @var		int
	 */
    public $site_id = 0;

    /**
	 * The new site WP_Site object
	 *
	 * @since	2.0
	 * @var		object
	 */
    public $site = false;

	/**
	 * The ID of the site to act as the clone
	 *
	 * @since	2.0
	 * @var		int
	 */
    public $clone_master = false;

	/**
	 * Plugins action after cloning
	 *
	 * @since	2.0
	 * @var		string
	 */
	public $clone_plugins_action = 'clone';

	/**
	 * Themes action after cloning
	 *
	 * @since	2.0
	 * @var		string
	 */
	public $clone_themes_action = 'clone';

    /**
	 * The new site title
	 *
	 * @since	2.0
	 * @var		string
	 */
    public $title;

    /**
	 * The new site tag line
	 *
	 * @since	2.0
	 * @var		string
	 */
    public $tag_line;

    /**
	 * Discourage search engines
	 *
	 * @since	2.0
	 * @var		bool
	 */
    public $discourage_search = false;

    /**
	 * Disable visibility setting
	 *
	 * @since	2.0
	 * @var		bool
	 */
    public $visibility_setting = false;

    /**
	 * Disable default welcome panel
	 *
	 * @since	2.0
	 * @var		bool
	 */
    public $disable_default_welcome = false;

    /**
	 * Custom welcome panel
	 *
	 * @since	2.0
	 * @var		bool
	 */
    public $custom_welcome = false;

	/**
	 * Upload space
	 *
	 * @since	1.4.1
	 * @var		int
	 */
	public $upload_space = 0;

    /**
     * Site lifetime
     *
     * @since   2.0
     * @var     int
     */
    public $site_lifetime;

    /**
     * Hide appearance menu
     *
     * @since   2.0
     * @var     bool
     */
    public $hide_appearance_menu = false;

    /**
     * Hide plugins menu
     *
     * @since   2.0
     * @var     bool
     */
    public $hide_plugins_menu;

    /**
	 * Registration action
	 *
	 * @since	2.0
	 * @var		bool
	 */
    public $registration_action;

    /**
	 * Redirect page
	 *
	 * @since	2.0
	 * @var		bool
	 */
    public $redirect_page;

	/**
	 * Auto login
	 *
	 * @since	1.5.1
	 * @var		bool
	 */
    public $auto_login;

    /**
	 * Theme to set for the new site
	 *
	 * @since	2.0
	 * @var		object
	 */
	private $theme;

    /**
	 * Array of allowed themes for the new site
	 *
	 * @since	2.0
	 * @var		array
	 */
	private $allowed_themes = array();

	/**
	 * Array of plugins to activate
	 *
	 * @since	2.0
	 * @var		array
	 */
	private $plugins = array();

    /**
     * Array of notices to activate
     *
     * @since   1.4
     * @var     array
     */
    private $notices = array();

    /**
     * Number of times this template has been used
     *
     * @since   1.5.2
     * @var     int
     */
    private $build_count = 0;

	/**
     * Whether or not the we're ready to build
     *
     * @since   2.0
     * @var     bool
     */
    private $builder_ready = false;

	/**
	 * Setup the EPDP_Demo_Builder class
	 *
	 * @since	2.0
	 * @return	mixed	void|false
	 */
	public function __construct( $demo_id ) {
        // Grab the site ID and object as soon as we can
        // Priority of 1 so it runs before all other hooks
        add_action( 'wp_initialize_site', array( $this, 'initialise_site' ), 1 );

		$this->builder_ready = $this->setup_builder( $demo_id );

        if ( $this->builder_ready ) {
            $this->init();
        }
	} // __construct

    /**
	 * Magic GET function.
	 *
	 * @since	2.0
	 * @param	string	$key	The property to retrieve
	 * @return	mixed	The value retrieved
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			$value = call_user_func( array( $this, 'get_' . $key ) );
		} else {
			$value = $this->$key;
		}

		return $value;
	} // __get

    /**
     * Intialise the build process.
     *
     * @since   2.0
     * @return  void
     */
    public function init() {
        // Initial registration args
        add_filter( 'epd_site_registration_args', array( $this, 'set_site_args' ), 900 );
        add_filter( 'epd_default_blog_meta',      array( $this, 'set_blog_meta' ), 900 );
        // Site & blog options
        add_filter( 'epd_set_new_site_defaults', array( $this, 'set_site_options' ), 900 );

        // Plugins
        add_filter( 'epd_plugins_to_activate', array( $this, 'set_active_plugins' ), 900 );

		// Registration actions
		remove_filter( 'epd_after_user_registration_action', 'epdp_set_demo_registration_action_on_activate', 900, 2 );
		add_filter( 'epd_after_user_registration_action',  array( $this, 'set_registration_action' ), 900 );
		remove_filter( 'epd_after_registration_redirect_url', 'epdp_set_demo_redirection_page_on_activate', 900, 2 );
		add_filter( 'epd_after_registration_redirect_url', array( $this, 'set_redirect_page' ), 900 );

		// Auto login
		add_filter( 'epd_user_auto_login', array( $this, 'set_auto_login' ), 900, 3 );

        // Notices
        remove_action( 'epd_registration', 'epdp_add_notice_schedules_action' );
        add_action( 'epd_registration', array( $this, 'add_notice_schedules' ) );

        // Counts
        add_action( 'epd_registration', array( $this, 'increment_build_count' ), PHP_INT_MAX );

		// Cloning
		if ( ! empty( $this->clone_master ) )	{
			remove_action( 'wp_insert_site', 'epdp_define_new_site_actions_action', 1 );
			add_action( 'wp_insert_site', array( $this, 'init_clone' ), 1 );
		}
    } // init

	/**
	 * Setup the builder properties.
	 *
	 * @since	2.0
	 * @param 	int		$demo_id	The ID of the demo template
	 * @return	bool	True if the setup was successful
	 */
	private function setup_builder( $demo_id ) {

		if ( ! $demo_id || 'epd_demo' != get_post_type( $demo_id ) ) {
			return false;
		}

		// Extensions can hook here perform actions before the cloner data is loaded
		do_action( 'epdp_pre_setup_builder', $this, $demo_id );

		// Primary Identifiers
        $this->demo_id  = absint( $demo_id );
		$this->demo_key = $this->__get( 'demo_key' );

		// Clone options
		$this->clone_master            = $this->__get( 'clone_master' );

		if ( ! empty( $this->clone_master ) )	{
			$this->clone_plugins_action    = $this->__get( 'clone_plugins_action' );
			$this->clone_themes_action     = $this->__get( 'clone_themes_action' );
		}

        // Site settings
        $this->title                   = $this->__get( 'title' );
        $this->tag_line                = $this->__get( 'tag_line' );
        $this->discourage_search       = $this->__get( 'discourage_search' );
        $this->visibility_setting      = $this->__get( 'visibility_setting' );
        $this->disable_default_welcome = $this->__get( 'disable_default_welcome' );
        $this->custom_welcome          = $this->__get( 'custom_welcome' );
		$this->upload_space            = $this->__get( 'upload_space' );
		$this->auto_login              = $this->__get( 'auto_login' );
        $this->site_lifetime           = $this->__get( 'site_lifetime' );
        $this->hide_appearance_menu    = $this->__get( 'hide_appearance_menu' );
        $this->hide_plugins_menu       = $this->__get( 'hide_plugins_menu' );

        // Registration actions
        $this->registration_action = $this->__get( 'registration_action' );
        $this->redirect_page       = $this->__get( 'redirect_page' );

		// Plugins & Themes
		$this->plugins        = $this->__get( 'plugins' );
        $this->theme          = $this->__get( 'theme' );
        $this->allowed_themes = $this->__get( 'allowed_themes' );

        // Template usage
        $this->build_count = $this->__get( 'build_count' );

		// Extensions can hook here to add items to this object
		do_action( 'epdp_setup_builder', $this, $demo_id );
								
		return true;

	} // setup_builder

    /**
     * Setup the new site variables.
     *
     * @since   2.0
     * @param   object  $site   The WP_Site object
     * @return  void
     */
    public function initialise_site( $site )    {
        $this->site    = $site;
        $this->site_id = $site->blog_id;
    } // initialise_site

    /**
     * Setup the arguments for the new site.
     *
     * @since   1.2
     * @param   array   $args   New site arguments
     * @return  array   New site arguments
     */
    public function set_site_args( $args )  {
        $args['title'] = $this->title;
		$args['meta']['blogdescription'] = $this->tag_line;

        return $args;
    } // set_site_args

    /**
     * Set blog meta data.
     *
     * @since   2.0
     * @param   array   $options    Array of options and values
     * @return  array   Array of options and values
     */
    public function set_blog_meta( $options )    {
        $options['epd_demo_template']           = $this->demo_id;
        $options['epd_disable_visibility']      = $this->visibility_setting;
        $options['epd_disable_default_welcome'] = $this->disable_default_welcome;
        $options['epd_custom_welcome']          = $this->custom_welcome;
        $options['epd_hide_appearance_menu']    = $this->hide_appearance_menu;
        $options['epd_hide_plugins_menu']       = $this->hide_plugins_menu;

        if ( empty( $this->site_lifetime ) )    {
            $options['epd_site_lifetime'] = '0';
			$options['epd_site_expires']  = '0';
        } else  {
            $options['epd_site_lifetime'] = $this->site_lifetime;
			$options['epd_site_expires']  = current_time( 'timestamp' ) + $this->site_lifetime;
        }

        return $options;
    } // set_blog_meta

    /**
     * Set the plugins to activate.
     *
     * @since   1.2
     * @param   array   $plugins    Array of plugins to activate
     * @return  array   Array of plugins to activate
     */
    public function set_active_plugins( $plugins )  {
        return $this->plugins;
    } // set_active_plugins

    /**
     * Set options for new site.
     *
     * @since   1.2
     * @param   array   $options   Array of key => values to update
     * @return  array   Array of key => values to update
     */
    public function set_site_options( $options )   {
        $options['allowedthemes'] = $this->allowed_themes;
		$options['template']      = $this->theme->template;
		$options['stylesheet']    = $this->theme->stylesheet;
		$options['blog_public']   = $this->discourage_search ? 0 : 1;

		if ( ! empty( $this->upload_space ) )	{
			$options['blog_upload_space'] = $this->upload_space;
		} else	{
			if ( isset( $options['blog_upload_space'] ) )	{
				unset( $options['blog_upload_space'] );
			}
		}

        return $options;
    } // set_site_options

	/**
	 * Set the registration success action.
	 *
	 * @since	1.2
	 * @param	string	$action		The action to be taken
	 * @return	string	The action to be taken
	 */
	public function set_registration_action( $action )	{
		return $this->registration_action;
	} // set_registration_action

	/**
	 * Set the redirection page.
	 *
	 * @since	1.2
	 * @param	int		$url		The URL of the page to redirect to
	 * @return	int		The URL of the page to redirect to
	 */
	public function set_redirect_page( $url )	{
		if ( 'redirect' == $this->registration_action )	{
			$url = get_permalink( $this->redirect_page );
		}

		return $url;
	} // set_redirect_page

	/**
	 * Set the auto login option.
	 *
	 * @since	1.5.1
	 * @param	bool	$auto_login		Whether or not to auto login
	 * @param   int     $user_id    	ID of demo user
	 * @param   int     $site_id    	ID of new demo site
	 * @return	bool	Whether or not to auto login
	 */
	public function set_auto_login( $auto_login, $user_id, $site_id )	{
		return $this->auto_login;
	} // set_auto_login

    /**
	 * Add the notice schedules to the demo.
	 *
	 * @since	1.4
	 * @return	void
	 */
    public function add_notice_schedules( $site_id )  {
        $notices = epdp_get_notices();
        $active  = epdp_get_demo_notices( $this->demo_id );

        foreach( $notices as $notice_id => $notice )    {
            if ( ! in_array( $notice['slug'], $active ) )   {
                unset( $notices[ $notice_id ] );
            }
        }

        switch_to_blog( $site_id );

        foreach( $notices as $notice )	{
            set_transient( $notice['slug'], 'anything', $notice['timer'] );
            epdp_add_notice_for_site( $site_id, $notice['slug'] );
        }

        restore_current_blog();
    } // add_notice_schedules

	/**
     * Get the key for this demo.
     *
     * @since   2.0
     * @return  string  Demo key
     */
	public function get_demo_key()	{
		return epdp_get_demo_key( $this->demo_id );
	} // get_demo_key

	/**
     * Get the clone master for the new site.
     *
     * @since   2.0
     * @return  int  ID of the clone master
     */
    public function get_clone_master() {
        return absint( epdp_get_demo_clone_site( $this->demo_id ) );
    } // get_clone_master

	/**
     * Get the clone plugins action.
     *
     * @since   2.0
     * @return  string  Plugins action after cloning
     */
    public function get_clone_plugins_action() {
        return sanitize_text_field( epdp_get_demo_clone_plugin_action( $this->demo_id ) );
    } // get_clone_plugins_action

	/**
     * Get the clone themes action.
     *
     * @since   2.0
     * @return  int  ID of the clone master
     */
    public function get_clone_themes_action() {
        return sanitize_text_field( epdp_get_demo_clone_theme_action( $this->demo_id ) );
    } // get_clone_themes_action

	/**
	 * Initialise the clone.
	 *
	 * @since	2.0
	 * @return	void
	 */
	public function init_clone()	{
		remove_action( 'wp_initialize_site', 'epd_activate_new_blog_plugins', 11 );
        remove_action( 'epd_create_demo_site', 'epd_create_new_blog_posts_pages_action', 20, 2 );

        add_action( 'wp_initialize_site', array( $this, 'clone_site' ), 50 );
	} // init_clone

	/**
	 * Perform clone.
	 *
	 * @since	2.0
	 * @param	object	$site	WP_Site object
	 * @return	void
	 */
	public function clone_site( $site )	{
		epdp_clone_new_site( $site, $this->demo_id );
	} // clone_site

    /**
     * Retrieve the template build count.
     *
     * @since   1.5.2
     * @return  int     Number of times this template has been used
     */
    public function get_build_count()   {
        return epdp_get_template_build_count( $this->demo_id );
    } // get_build_count

    /**
     * Increment template usage count.
     *
     * @since   1.5.2
     * @return  int     Count of number of times template has been used
     */
    public function increment_build_count()  {
        $template = epdp_get_site_demo_template_id( $this->site_id );

        if ( ! empty( $template ) ) {
            $this->build_count = epdp_increment_template_build_count( $this->demo_id );
        }

        return $this->build_count;
    } // increment_build_count

    /**
     * Get the title for the new site.
     *
     * @since   2.0
     * @return  string  Title of new site
     */
    public function get_title() {
        return sanitize_text_field( epdp_get_demo_site_title( $this->demo_id ) );
    } // get_title

    /**
     * Get the tag line for the new site.
     *
     * @since   2.0
     * @return  string  Title of new site
     */
    public function get_tag_line() {
        return sanitize_text_field( epdp_get_demo_site_tag_line( $this->demo_id ) );
    } // get_title

    /**
     * Whether or not to discourage search engine visibility.
     *
     * @since   2.0
     * @return  bool
     */
    public function get_visibility_setting() {
        return epdp_get_demo_disable_visibility_setting( $this->demo_id );
    } // get_visibility_setting

    /**
     * Whether or not to discourage search enging visibility.
     *
     * @since   2.0
     * @return  bool
     */
    public function get_discourage_search() {
        return epdp_get_demo_discourage_search( $this->demo_id );
    } // get_discourage_search

    /**
     * Whether or not to disable the default welcome panel.
     *
     * @since   2.0
     * @return  bool
     */
    public function get_disable_default_welcome() {
        return epdp_get_demo_disable_default_welcome_panel_setting( $this->demo_id );
    } // get_disable_default_welcome

    /**
     * Custom welcome panel.
     *
     * @since   2.0
     * @return  bool|string
     */
    public function get_custom_welcome() {
        if ( epdp_get_demo_add_custom_welcome_panel_setting( $this->demo_id ) ) {
            return epdp_get_demo_custom_welcome_panel_setting( $this->demo_id );
        }

        return false;
    } // get_custom_welcome

	/**
	 * Site upload space.
	 *
	 * @since	1.4.1
	 * @return	int
	 */
	public function get_upload_space()	{
		return epdp_get_demo_site_upload_space( $this->demo_id );
	} // get_upload_space

	/**
	 * Auto login option for site.
	 *
	 * @since	1.5.1
	 * @return	bool
	 */
	public function get_auto_login()	{
		return epdp_get_demo_auto_login_option( $this->demo_id );
	} // get_auto_login

    /**
     * Site lifetime.
     *
     * @since   2.0
     * @return  int
     */
    public function get_site_lifetime() {
        return epdp_get_demo_site_lifetime( $this->demo_id );
    } // get_site_lifetime

    /**
     * Hide appearance menu.
     *
     * @since   2.0
     * @return  bool|string
     */
    public function get_hide_appearance_menu() {
        return epdp_get_demo_hide_appearance_menu( $this->demo_id );
    } // get_hide_appearance_menu

    /**
     * Hide plugins menu.
     *
     * @since   2.0
     * @return  bool|string
     */
    public function get_hide_plugins_menu() {
        return epdp_get_demo_hide_plugins_menu( $this->demo_id );
    } // get_hide_plugins_menu

    /**
     * Registration action.
     *
     * @since   2.0
     * @return  bool|string
     */
    public function get_registration_action() {
        return epdp_get_demo_registration_action( $this->demo_id );
    } // get_registration_action

    /**
     * Redirect page.
     *
     * @since   2.0
     * @return  bool|string
     */
    public function get_redirect_page() {
        return epdp_get_demo_redirect_page( $this->demo_id );
    } // get_redirect_page

    /**
     * Setup the active demo theme.
     *
     * @since   2.0
     * @return  object   Theme object for the theme to activate.
     */
    public function get_theme() {
        $_theme = wp_get_theme( epdp_get_demo_theme( $this->demo_id ) );

        if ( ! $_theme->exists() )	{
			$_theme = wp_get_theme();
		}

        return $_theme;
    } // get_theme

    /**
     * Setup the themes available within the site.
     *
     * @since   2.0
     * @return  array   Array of themes allowed
     */
    public function get_allowed_themes() {
        $themes  = epdp_get_demo_allowed_themes( $this->demo_id );
        $allowed = array();

        foreach( $themes as $_theme )    {
            $object = wp_get_theme( $_theme );
            if ( $object->exists() )    {
                $allowed[ $_theme ] = true;
            }
        }

        $allowed[ $this->theme->stylesheet ] = true;

        return $allowed;
    } // get_allowed_themes

    /**
     * Retrieve the plugins that should be activated.
     *
     * @since   2.0
     * @return  array   Array of plugins to activate.
     */
    public function get_plugins() {
        return epdp_get_demo_plugins( $this->demo_id );
    } // get_plugins

} // EPDP_Demo_Builder