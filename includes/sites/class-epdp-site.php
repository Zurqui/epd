<?php
/**
 * EPDP Site Class
 *
 * @package		EPDP Site
 * @subpackage	Posts/Site
 * @since		1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * EPDP_Site Class
 *
 * @since	1.2
 */
class EPDP_Site {

	/**
	 * The site id
	 *
	 * @since	1.2
	 * @var		int
	 */
	public $site_id = 0;

	/**
	 * Demo template ID
	 *
	 * @since	1.2
	 * @var		int
	 */
	public $demo_id = 0;

    /**
     * Whether or not to hide the theme menu (Appearances)
     *
     * @since   1.2
     * @var     bool
     */
    public $hide_theme_menu = false;

    /**
     * Whether or not to hide the plugins menu
     *
     * @since   1.2
     * @var     bool
     */
    public $hide_plugins_menu = false;

	/**
     * Whether or not to hide the discourage search setting
     *
     * @since   1.2
     * @var     bool
     */
    public $hide_discourage_search = false;

	/**
	 * Whether or not to hide he default welcome panel.
	 *
	 * @since	1.2
	 * @var		bool
	 */
	public $hide_default_welcome = false;

	/**
	 * Message for custom welcome panel.
	 *
	 * @since	1.2
	 * @var		bool
	 */
	public $custom_welcome = false;

	/**
	 * Demo product name.
	 *
	 * @since	1.2
	 * @var		string
	 */
	public $product;

	/**
	 * Get things going
	 *
	 * @since	1.2
	 */
	public function __construct( $_id = false ) {
		if ( $this->setup_site( $_id ) )  {
            $this->init();
        }
        
	} // __construct

	/**
	 * Given the site data, let's set the variables
	 *
	 * @since	1.2
	 * @param 	object	$site	The site post object
	 * @return	bool			If the setup was successful or not
	 */
	private function setup_site( $site_id ) {
        $this->site_id                = $site_id;
		$this->demo_id                = epdp_get_site_demo_template_id( $site_id );
		$this->product                = $this->get_product_name();
        $this->hide_theme_menu        = $this->get_hide_theme_menu();
        $this->hide_plugins_menu      = $this->get_hide_plugins_menu();
		$this->hide_discourage_search = $this->get_hide_discourage_search();
		$this->hide_default_welcome   = $this->get_hide_default_welcome();
		$this->custom_welcome         = $this->get_custom_welcome();

		return true;
	} // setup_site

    /**
     * Initialise hooks
     *
     * @since   2.0
     */
    public function init()  {
        // Manage menu items
        add_action( 'admin_menu', array( $this, 'hide_menu_items' ), 100 );

		// JS script vars
		add_filter( 'epd_admin_scripts_vars', array( $this, 'script_vars' ), 100 );

		// Welcome panels
		add_filter( 'epd_hide_default_welcome_panel', array( $this, 'hide_default_welcome'), 100 );
		add_filter( 'epd_add_custom_welcome_panel',   array( $this, 'show_custom_welcome' ), 100 );

		if ( ! empty( $this->custom_welcome ) )	{
			add_action( 'epd_welcome_panel_text', array( $this, 'custom_welcome_message' ), 10, 2 );
		}

		// Email tags
		add_filter( 'epd_tag_demo_product_name', array( $this, 'tag_product_name' ), 10 );
    } // init

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since	1.2
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			return new WP_Error(
				'epd-site-invalid-property', 
				printf( __( "Can't get property %s", 'epd-premium' ), $key )
			);
		}
	} // __get

    /**
     * Hide the menu items.
     *
     * @since   1.2
     * @return  void
     */
    public function hide_menu_items()   {
        if ( ! is_main_site() && ! is_network_admin() )	{
            $menu_items = array();

            if ( $this->hide_theme_menu )   {
                $menu_items[] = 'themes.php';
            }

            if ( $this->hide_plugins_menu ) {
                $menu_items[] = 'plugins.php';
            }

            if ( ! empty( $menu_items ) )   {
                foreach( $menu_items as $menu_item )    {
                    remove_menu_page( $menu_item );
                }
            }

        }
    } // hide_menu_items

	/**
	 *
	 * @since	1.2
	 * @param	array	$vars	Array of variables being parsed to JS
	 * @return	array	Array of variables being parsed to JS
	 */
	public function script_vars( $vars )	{
		$vars['hide_blog_public'] = $this->hide_discourage_search;

		return $vars;
	} // script_vars

	/**
     * Hide the default wecome panel.
     *
     * @since   1.2
	 * @param	bool	$hide	Whether or not to hide the default welcome panel
     * @return  bool	Whether or not to hide the default welcome panel
     */
    public function hide_default_welcome( $hide )   {
		return $this->hide_default_welcome;
	} // hide_default_welcome

	/**
     * Whether or not to show a custom welcome panel.
     *
     * @since   1.2
	 * @param	bool	$hide	Whether or not to hide the default welcome panel
     * @return  bool	Whether or not to hide the default welcome panel
     */
    public function show_custom_welcome( $hide )   {
		return ! empty( $this->custom_welcome );
	} // show_custom_welcome

	/**
     * Whether or not to show a custom welcome panel.
     *
     * @since   1.2
	 * @param	int		$user_id	Current user ID
     * @return  bool	$site_id	Site ID
     */
    public function custom_welcome_message( $user_id, $site_id )   {
		$welcome = $this->custom_welcome;
        $welcome = $welcome;
        $welcome = apply_filters( 'the_content', $welcome );
        $welcome = epd_do_email_tags( $welcome, $site_id, $user_id  );

        ?>
        <div class="welcome-panel-content">
            <?php echo $welcome; ?>
        </div>
        <?php
	} // custom_welcome_message

	/**
	 * Filters the {demo_product_name} tag.
	 *
	 * @since	1.2
	 * @param	string	$product_name	Product name
	 */
	public function tag_product_name( $product )	{
		if ( ! empty( $this->demo_id ) && ! empty( $this->product ) )	{
			$product = $this->product;
		}
		return $product;
	} // tag_product_name

	/**
	 * Retrieve the site ID
	 *
	 * @since	1.2
	 * @return	int
	 */
	public function get_id() {
		return $this->site_id;
	} // get_id

	/**
	 * Get product name
	 *
	 * @since	1.2
	 * @return	bool
	 */
	public function get_product_name()	{
		switch_to_blog( get_network()->blog_id );
		$product_name = get_the_title( $this->demo_id );
		restore_current_blog();

		return $product_name;
	} // get_product_name

    /**
	 * Whether or not to hide the appearance menu
	 *
	 * @since	1.2
	 * @return	bool
	 */
	public function get_hide_theme_menu()	{
		return epdp_get_hide_appearance_menu( $this->site_id );
	} // hide_theme_menu

    /**
	 * Whether or not to hide the plugins menu
	 *
	 * @since	1.2
	 * @return	bool
	 */
	public function get_hide_plugins_menu()	{
		return epdp_get_hide_plugins_menu( $this->site_id );
	} // get_hide_plugins_menu

	/**
	 * Whether or not to hide the plugins menu
	 *
	 * @since	1.2
	 * @return	bool
	 */
	public function get_hide_discourage_search()	{
		return epdp_get_hide_discourage_search( $this->site_id );
	} // get_hide_plugins_menu

	/**
	 * Whether or not to hide the plugins menu
	 *
	 * @since	1.2
	 * @return	bool
	 */
	public function get_hide_default_welcome()	{
		return epdp_get_hide_default_welcome( $this->site_id );
	} // get_hide_default_welcome

	/**
	 * Whether or not to hide the plugins menu
	 *
	 * @since	1.2
	 * @return	bool
	 */
	public function get_custom_welcome()	{
		return epdp_get_custom_welcome( $this->site_id );
	} // get_custom_welcome

} // EPDP_Site
