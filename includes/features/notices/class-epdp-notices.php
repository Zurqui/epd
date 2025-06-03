<?php
/**
 * EPDP Notices Class
 *
 * @package		EPD
 * @subpackage	Demos/Sites
 * @since		2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * EPDP_Notices Class
 *
 * @since	1.4
 */
class EPDP_Notices {
    /**
	 * The ID of the site currently being viewed
	 *
	 * @since	1.4
	 * @var		int
	 */
    private $site_id = 0;

    /**
	 * The ID of the user currently vieweing
	 *
	 * @since	1.4
	 * @var		int
	 */
    private $user_id = 0;

    /**
     * Whether the site is the main site
     *
     * @since   1.4
     * @var     bool
     */
    private $is_main_site = false;

    /**
     * Defined notices
     *
     * @since   1.4
     * @var     array
     */
    private $notices = array();

    /**
     * Active admin notices
     *
     * @since   1.4
     * @var     array
     */
    private $admin_notices = array();

    /**
     * Active front notices
     *
     * @since   1.4
     * @var     array
     */
    private $front_notices = array();

    /**
     * Is admin?
     *
     * @since   1.4
     * @var     bool
     */
    private $is_admin = false;

    /**
     * Is front end?
     *
     * @since   1.4
     * @var     bool
     */
    private $is_front_end = false;

    /**
	 * Setup the EPDP_Notices class
	 *
	 * @since	1.4
	 * @return	mixed	void|false
	 */
	public function __construct() {
        add_action( 'init', array( $this, 'init' ), 1 );
	} // __construct

    /**
     * Initialise the class.
     *
     * @since   1.4
     * @return  void
     */
    public function init()    {
        if ( $this->setup_notices() )   {
            $this->hooks();
        }
    } // init

    /**
     * Hooks.
     *
     * @since   1.4
     * @return  void
     */
    private function hooks()    {
        if ( $this->admin_notices )   {
            add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        }

        if ( $this->front_notices )   {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_front_scripts' ) );
            add_action( 'wp_head', array( $this, 'front_notices' ) );
        }
    } // hooks

    /**
     * Setup the notice variables.
     *
     * @since   1.4
     * @param   object  $site   The WP_Site object
     * @return  void
     */
    private function setup_notices()    {
        $this->site_id      = get_current_blog_id();
        $this->is_main_site = $this->site_id == get_network()->blog_id;

        if ( $this->is_main_site || is_network_admin() || is_super_admin() )  {
            return false;
        }

        $active_only = true;

        $demo_template = epdp_get_site_demo_template_id( $this->site_id );

        if ( ! empty( $demo_template ) )    {
            $active_only = false;
        }

        $this->notices = epdp_get_notices( $active_only );

        if ( empty( $this->notices ) )  {
            return false;
        }

        if ( is_admin() )   {
            $this->is_admin = true;
        } else  {
            $this->is_front_end = true;
        }

        $this->user_id = get_current_user_id();

        return $this->prepare_notices();
    } // setup_notices

    /**
     * Prepare notices.
     *
     * @since   1.4
     * @return  void
     */
    private function prepare_notices()  {
        $admin_notices   = array();
        $front_notices   = array();
		$display_notices = epdp_get_site_notices( $this->site_id );

		if ( empty( $display_notices )	)	{
			return false;
		}

        foreach( $this->notices as $notice_id => $notice )    {
            if ( in_array( $notice['slug'], $display_notices ) && false === get_transient( $notice['slug'] ) )   {

                if ( ( 'both' == $notice['display'] ) || 'admin' == $notice['display'] && $this->is_admin )    {
                    $this->admin_notices[] = $notice;
                }

                if ( ( 'both' == $notice['display'] ) || 'front' == $notice['display'] && $this->is_front_end )  {
                    $this->front_notices[] = $notice;
                }
            }
        }

        if ( $this->is_admin && ! empty( $this->admin_notices ) )   {
            return true;
        } elseif ( $this->is_front_end && ! empty( $this->front_notices ) ) {
            return true;
        } else  {
            return false;
        }
    } // prepare_notices

    /**
     * Display admin notices.
     *
     * @since   1.4
     * @return  void
     */
    public function admin_notices()    {
        $output = '';

        foreach( $this->admin_notices as $id => $notice )  {
            $border     = stripslashes( $notice['border'] );
            $background = stripslashes( $notice['background'] );
            $color      = stripslashes( $notice['text'] );
            $message    = wpautop( stripslashes( $notice['notice'] ) );
            $slug       = stripslashes( $notice['slug'] );

            $output .= sprintf(
                '<div class="notice" style="border-color: %s; background-color: %s; color: %s">',
                $border,
                $background,
                $color
            );
            $output .= epd_do_email_tags( $message, $this->site_id, $this->user_id );
            $output .= '</div>';

            unset( $this->admin_notices[ $id ] );
            $this->delete_notice( $slug );
            $this->increment_display_count( $slug );
        }

        if ( ! empty( $output ) )   {
            echo $output;
        }
    } // admin_notices

	/**
	 * Enqueue styles and scripts for front end notices.
	 *
	 * @since	1.4
	 * @return	void
	 */
	public function load_front_scripts()	{
        $vendor_dir  = trailingslashit( EPD_PREMIUM_URL . '/assets/vendor' );
        $suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_style( 
			'noty',
			$vendor_dir . 'noty/noty.css',
            array(),
            '3.1.4'
		);

		wp_register_script(
			'noty',
			$vendor_dir . 'noty/noty' . $suffix . '.js',
			array( 'jquery' ),
            '3.1.4'
		);

		wp_enqueue_style( 'noty' );
		wp_enqueue_script( 'noty' );
	} // load_front_scripts

    /**
     * Display front end notices.
     *
     * @since   1.4
     * @param   string  $content    The the_content field of the demo object
     * @return  void
     */
    public function front_notices( $content ) {
        $output = '';

        foreach( $this->front_notices as $id => $notice )  {
            $message    = wpautop( stripslashes( $notice['notice'] ) );
            $slug       = stripslashes( $notice['slug'] );

            unset( $this->front_notices[ $id ] );
            $this->delete_notice( $slug );
            $this->increment_display_count( $slug );

            $output .= $this->print_noty_styles( $id, $notice );
    
            $output .= '<script>' . "\n";
            $output .= 'jQuery(document).ready(function ($) {' . "\n";
            $output .= sprintf(
                "new Noty({
                    text: %s,
                    layout: 'topCenter',
                    theme: 'epd-%s',
                    type: 'info'
                }).show();\n",
                json_encode( epd_do_email_tags( $message, $this->site_id, $this->user_id ) ),
                $id
            );
            $output .= '});' . "\n";
            $output .= '</script>' . "\n";

        }

        if ( ! empty( $output ) )   {
            echo $output;
        }
    } // front_notices

    /**
     * Increment display count.
     *
     * @since   1.4
     * @param   string  $notice_id   Notice id
     * @return  void
     */
    private function increment_display_count( $slug )    {
        foreach( $this->notices as $notice_id => $notice )  {
            if ( $slug == $notice['slug'] )    {
                epdp_increment_notice_display_count( $notice_id );
                break;
            }
        }
    } // increment_display_count

    /**
     * Delete notice.
     *
     * @since   1.4
     * @param   string  $slug   Notice slug
     * @return  void
     */
    private function delete_notice( $slug )    {
        epdp_remove_notice_from_site( $this->site_id, $slug );
    } // delete_notice

    /**
     * Print Noty styles for front end.
     *
     * @since   1.4
     * @param   int     $notice_id  Notice ID
     * @param   array   $notice     Notice data array
     * @return  void
     */
    public function print_noty_styles( $notice_id, $notice )    {
        $border     = stripslashes( $notice['border'] );
        $background = stripslashes( $notice['background'] );
        $color      = stripslashes( $notice['text'] );
        $output     = '';

        $output .= '<style>' . "\n";
        $output .= sprintf(
            '#noty_layout__topCenter {
                width: %1$s !important; }
            .noty_theme__epd-%2$s.noty_bar {
                  margin: 4px 0;
                  overflow: hidden;
                  position: relative;
                  border: 1px solid transparent;
                  border-radius: .25rem; }
                  .noty_theme__epd-%2$s.noty_bar .noty_body {
                    padding: .75rem 1.25rem; }
                  .noty_theme__epd-%2$s.noty_bar .noty_buttons {
                    padding: 10px; }
                  .noty_theme__epd-%2$s.noty_bar .noty_close_button {
                    font-size: 1.5rem;
                    font-weight: 700;
                    line-height: 1;
                    color: #000;
                    text-shadow: 0 1px 0 #fff;
                    filter: alpha(opacity=20);
                    opacity: .5;
                    background: transparent; }
                  .noty_theme__epd-%2$s.noty_bar .noty_close_button:hover {
                    background: transparent;
                    text-decoration: none;
                    cursor: pointer;
                    filter: alpha(opacity=50);
                    opacity: .75; }

                .noty_theme__epd-%2$s.noty_type__alert,
                .noty_theme__epd-%2$s.noty_type__notification,
                .noty_theme__epd-%2$s.noty_type__warning,
                .noty_theme__epd-%2$s.noty_type__error,
                .noty_theme__epd-%2$s.noty_type__info,
                .noty_theme__epd-%2$s.noty_type__information,
                .noty_theme__epd-%2$s.noty_type__success {
                    background-color: %3$s;
                    color: %4$s;
                    border-color: %5$s; }
            ' . "\n",
            '80%',
            $notice_id,
            $background,
            $color,
            $border
        );
        $output .= '</style>' . "\n";

        echo $output;
    } // print_noty_styles

} // EPDP_Notices

new EPDP_Notices();
