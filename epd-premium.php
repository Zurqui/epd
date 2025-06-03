<?php
/**
 * Plugin Name:	Easy Plugin Demo - Premium Pack
 * Plugin URI:	https://easy-plugin-demo.com/downloads/premium-pack/
 * Description:	Extends the Easy plugin Demo plugin adding advanced features.
 * Version:		1.5.2
 * Date:		14th December 2020
 * Author:		Mike Howard <mike@mikesplugins.co.uk>
 * Author URI:	https://mikesplugins.co.uk
 * Text Domain:	epd-premium
 * Domain Path:	/languages
 * Network: true
 *
 * @package		EPD\Premium
 * @author		Mike Howard
 * @copyright	Copyright (c) 2020, Mike Howard
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! defined( 'EPD_PREMIUM_VERSION' ) )	{
	define( 'EPD_PREMIUM_VERSION', '1.5.2' );
}

if ( ! defined( 'EPD_PREMIUM_DIR' ) )	{
	define( 'EPD_PREMIUM_DIR', untrailingslashit( dirname( __FILE__ ) ) );
}

if ( ! defined( 'EPD_PREMIUM_BASENAME' ) )	{
	define( 'EPD_PREMIUM_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'EPD_PREMIUM_URL' ) )	{
	define( 'EPD_PREMIUM_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
}

if ( ! defined( 'EPD_PREMIUM_NAME' ) )	{
	define( 'EPD_PREMIUM_NAME', 'Premium Pack' );
}

if ( ! class_exists( 'EPD_Premium' ) )	{
	class EPD_Premium	{

		/**
         * @var		EPD_Premium	$instance	The one true EPD_Premium
         * @since	1.0
         */
		private static $instance;

		/**
         * @var		int		$required_epd	The minimum required Easy Plugin Demo version
         * @since	1.0
         */
		private static $required_epd = '1.3.8';

		/**
         * @var		object		$integrations	EPDP_Integrations
         * @since	1.3
         */
		public $integrations;

		public static function instance()	{
			// Do nothing if EPD is not activated
			if ( ! class_exists( 'Easy_Plugin_Demo', false ) || version_compare( self::$required_epd, EPD_VERSION, '>' ) ) {
				add_action( 'network_admin_notices', array( __CLASS__, 'notices' ) );
				return;
			}

			if ( ! self::$instance )	{
				self::$instance = new EPD_Premium();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->integrations = new EPDP_Integrations();
				self::$instance->hooks();

				new EPDP_API();
			}

			return self::$instance;
		} // __construct

		/**
		 * Calls the files that are required
		 * @since	1.0
		 */
		public static function includes()	{
            require_once EPD_PREMIUM_DIR . '/includes/posts/post-types.php';
			require_once EPD_PREMIUM_DIR . '/includes/rest/class-epdp-api.php';
			require_once EPD_PREMIUM_DIR . '/includes/demo/demo-functions.php';
            require_once EPD_PREMIUM_DIR . '/includes/demo/class-epdp-demo-builder.php';
			require_once EPD_PREMIUM_DIR . '/includes/demo/demo-builder-actions.php';
			require_once EPD_PREMIUM_DIR . '/includes/demo/demo-buttons.php';
			require_once EPD_PREMIUM_DIR . '/includes/admin/settings-functions.php';
            require_once EPD_PREMIUM_DIR . '/includes/admin/features/features-settings.php';
            require_once EPD_PREMIUM_DIR . '/includes/clone/clone-functions.php';
            require_once EPD_PREMIUM_DIR . '/includes/clone/class-epdp-clone-site.php';
            require_once EPD_PREMIUM_DIR . '/includes/posts/post-functions.php';
            require_once EPD_PREMIUM_DIR . '/includes/users/user-functions.php';
			require_once EPD_PREMIUM_DIR . '/includes/users/user-actions.php';
			require_once EPD_PREMIUM_DIR . '/includes/sites/site-functions.php';
            require_once EPD_PREMIUM_DIR . '/includes/sites/class-epdp-site.php';
			require_once EPD_PREMIUM_DIR . '/includes/sites/site-actions.php';
            require_once EPD_PREMIUM_DIR . '/includes/features/notices/notices-functions.php';
			require_once EPD_PREMIUM_DIR . '/includes/features/notices/notices-actions.php';
            require_once EPD_PREMIUM_DIR . '/includes/features/notices/class-epdp-notices.php';
			require_once EPD_PREMIUM_DIR . '/includes/scripts.php';
            require_once EPD_PREMIUM_DIR . '/includes/template-functions.php';
			require_once EPD_PREMIUM_DIR . '/includes/shortcodes.php';
			require_once EPD_PREMIUM_DIR . '/includes/widgets.php';

			if ( is_admin() ) {
				require_once EPD_PREMIUM_DIR . '/includes/admin/posts/demos.php';
                require_once EPD_PREMIUM_DIR . '/includes/admin/posts/meta-boxes.php';
                require_once EPD_PREMIUM_DIR . '/includes/admin/posts/sites.php';
				require_once EPD_PREMIUM_DIR . '/includes/admin/sites-functions.php';
				require_once EPD_PREMIUM_DIR . '/includes/admin/admin-pages.php';
			}

            // Integrations
			require_once EPD_PREMIUM_DIR . '/includes/integrations/class-epdp-integrations.php';

			if ( function_exists( 'EDD' ) )	{
				require_once EPD_PREMIUM_DIR . '/includes/integrations/easy-digital-downloads.php';
			}

			if ( function_exists( 'WC' ) )	{
				require_once EPD_PREMIUM_DIR . '/includes/integrations/woocommerce.php';
			}

            if ( class_exists( 'EPD_License' ) ) {
                $epd_kb_license = new EPD_License( __FILE__, EPD_PREMIUM_NAME, EPD_PREMIUM_VERSION, 'Michael Howard' );
            }
		} // includes

		/**
		 * Hooks
		 * @since	1.0
		 */
		public static function hooks()	{
            // Filter support post types
			add_filter( 'epd_supported_post_types',          array( self::$instance, 'supported_types'   ) );
			add_filter( 'epd_post_type_is_supported',        array( self::$instance, 'is_type_supported' ) );
			add_filter( 'epd_max_number_of_posts_to_create', array( self::$instance, 'max_num_posts'     ) );

            // Notices
            add_action( 'network_admin_notices', array( self::$instance, 'network_admin_notices' ) );
		} // hooks

		/**
         * Internationalization
         *
         * @access	public
         * @since	1.0
         * @return	void
         */
        public function load_textdomain()	{
            $lang_dir = EPD_PREMIUM_DIR . '/languages/';
            $lang_dir = apply_filters( 'epd_premium_languages_directory', $lang_dir );

            $locale = apply_filters( 'epd-premium', get_locale(), 'epd-premium' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'epd-premium', $locale );

            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/epd-premium/' . $mofile;

            if ( file_exists( $mofile_global ) )	{
                load_textdomain( 'epd-premium', $mofile_global );
            } elseif ( file_exists( $mofile_local ) ) {
                load_textdomain( 'epd-premium', $mofile_local );
            } else {
                load_plugin_textdomain( 'epd-premium', false, $lang_dir );
            }
        } // load_textdomain

		/**
		 * Display a notice if EPD not active or at required version.
		 *
		 * @since	1.0
		 */
		public static function notices()	{
			if ( ! defined( 'EPD_VERSION' ) )	{
				$message = sprintf(
					__( 'Easy Plugin Demo - %s requires that Easy Plugin Demo must be installed and activated.', 'epd-premium' ),
					EPD_PREMIUM_NAME
				);
			} else	{
				$message = sprintf(
					__( 'Easy Plugin Demo - %1$s requires Easy Plugin Demo version %s and higher. %1$s features will be disabled until Easy Plugin Demo is updated.', 'epd-premium' ),
					EPD_PREMIUM_NAME,
					self::$required_epd
				);
			}

			echo '<div class="notice notice-error">';
			echo '<p>' . $message . '</p>';
			echo '</div>';
		} // notices

		/**
         * Network admin notices.
         *
         * @since   1.0
         */
		public static function network_admin_notices()	{
            if ( ! isset( $_GET['epd-message'] ) )  {
                return;
            }

            if ( 'edit-success' == $_GET['epd-message'] ) {
                echo '<div class="notice notice-success is-dismissible">';
                    echo '<p>' . __( 'Expiry date updated for demo site.', 'epd-premium' ) . '</p>';
                echo '</div>';
            }

            if ( 'edit-error' == $_GET['epd-message'] ) {
                echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>' . __( 'Expiry date could not be updated for demo site.', 'epd-premium' ) . '</p>';
                echo '</div>';
            }

			if ( 'notice-updated' == $_GET['epd-message'] ) {
                echo '<div class="notice notice-success is-dismissible">';
                    echo '<p>' . __( 'Notice updated.', 'epd-premium' ) . '</p>';
                echo '</div>';
            }

			if ( 'notice-added' == $_GET['epd-message'] ) {
                echo '<div class="notice notice-success is-dismissible">';
                    echo '<p>' . __( 'Notice added.', 'epd-premium' ) . '</p>';
                echo '</div>';
            }

			if ( 'notice-deleted' == $_GET['epd-message'] ) {
                echo '<div class="notice notice-success is-dismissible">';
                    echo '<p>' . __( 'Notice deleted.', 'epd-premium' ) . '</p>';
                echo '</div>';
            }

            if ( 'zap-data-sent' == $_GET['epd-message'] ) {
                echo '<div class="notice notice-success is-dismissible">';
                    echo '<p>' . __( 'Zapier test data sent. Return to Zapier to complete your test.', 'epd-premium' ) . '</p>';
                echo '</div>';
            }
		} // network_admin_notices

/******************************
 -- MANAGE POSTS
******************************/
		/**
		 * Supported post types.
		 *
		 * @since	1.0
		 * @param	array	Array of supported post types
		 * @return	Array of supported post types
		 */
		public static function supported_types( $post_types )	{
			$ignore = array(
				'attachment',
				'revision',
				'custom_css',
				'customize_changeset',
				'oembed_cache',
				'user_request',
				'wp_block',
				'nav_menu_item'
			);

			$all_post_types = get_post_types( array() );

			foreach( $all_post_types as $all_post_type )	{
				if ( ! in_array( $all_post_type, $post_types ) && ! in_array( $all_post_type, $ignore ) )	{
					$post_types[] = $all_post_type;
				}
			}

			return $post_types;
		} // supported_types

		/**
		 * Add support for all post types.
		 *
		 * @since	1.0
		 * @param	array	$post_types	Whether or not a post type is supported
		 * @return	bool
		 */
		public static function is_type_supported( $post_types )	{
			return true;
		} // is_type_supported

		/**
		 * Enable unlimited post creations.
		 *
		 * @since	1.0
		 * @param	array	$post_types	Array of support post types
		 * @return	array	Array of supported post types
		 */
		public static function max_num_posts( $post_types )	{
			return -1;
		} // max_num_posts

	} // EPD_Premium

} // if ( ! class_exists( 'EPD_Premium' ) )

function LOAD_EPD_PREMIUM()	{
	return EPD_Premium::instance();
} // LOAD_EPD_PREMIUM
add_action( 'plugins_loaded', 'LOAD_EPD_PREMIUM', 20 );

/**
 * Runs when the plugin is activated.
 *
 * @since	1.0
 */
function epd_premium_install() {
    global $wpdb;

    if ( ! function_exists( 'is_plugin_active' ) )  {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    if ( ! is_plugin_active( 'easy-plugin-demo/easy-plugin-demo.php' ) )    {
        echo __( 'Easy Plugin Demo must be installed and activated!', 'epd-premium' );
        exit;
    }

	$current_version = get_site_option( 'epd_premium_version' );

	if ( ! $current_version ) {
		add_site_option( 'epd_premium_version', EPD_PREMIUM_VERSION, '', 'yes' );

        $tables = array();
        foreach( $wpdb->tables as $table )  {
            $tables[ $table ] = $table;
        }

		$options = array(
            'clone_site_id'           => '',
            'clone_tables'            => $tables,
            'plugins_action'          => 'clone',
            'themes_action'           => 'clone',
            'exclude_options'         => '',
            'set_author'              => 'current',
			'default_demo'            => 0,
			'button_placement'        => 'after_post',
			'button_text'             => __( 'Launch Demo', 'epd-premium' ),
			'mailchimp_api'           => '',
			'mailchimp_signup'        => '',
			'mailchimp_signup_label'  => __( 'Signup for the newsletter', 'epd-premium' ),
			'mailchimp_list'          => 0,
			'mailchimp_double_opt_in' => ''
        );

		foreach( $options as $key => $value )	{
			epd_update_option( $key, $value );
		}
	}

	// Clear the permalinks
	flush_rewrite_rules( false );
} // epd_premium_install
register_activation_hook( __FILE__, 'epd_premium_install' );

/**
 * Runs when the plugin is deactivated.
 *
 * @since	1.0
 */
function epd_premium_deactivate()	{
} // epd_premium_deactivate
register_deactivation_hook( __FILE__, 'epd_premium_deactivate' );

/*****************************************
 -- UPGRADE PROCEDURES
*****************************************/
/**
 * Perform automatic database upgrades when necessary
 *
 * @since	1.2
 * @return	void
*/
function epd_premium_upgrades() {
	$did_upgrade  = false;
	$epdp_version = preg_replace( '/[^0-9.].*/', '', get_site_option( 'epd_premium_version' ) );

	if ( version_compare( $epdp_version, '1.2', '<' ) ) {
		flush_rewrite_rules( false );
	}

	if ( version_compare( $epdp_version, '1.5.1', '<' ) ) {
		global $wpdb;

		$demo_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT      post_id
					FROM        $wpdb->postmeta
					WHERE       meta_key = %s
					AND			meta_value IN( 'confirm', 'home', 'admin' )
					ORDER BY    post_id
					ASC
				",
				'_epdp_registration_action'
			)
		);

		if ( $demo_ids ) {
			foreach( $demo_ids as $demo_id )	{
				update_post_meta( $demo_id, '_epdp_auto_login', 1 );
			}
		}
	}

    if ( version_compare( $epdp_version, '1.5.2', '<' ) ) {
        global $wpdb;

        $demos = epdp_get_demos( array( 'fields' => 'ids' ) );

        if ( ! empty( $demos ) )    {
            foreach( $demos as $demo_id )   {
                $count = $wpdb->get_var(
                    $wpdb->prepare(
                        "
                            SELECT  COUNT(*)
                            FROM    $wpdb->blogmeta
                            WHERE   meta_key = %s
                            AND     meta_value = %d
                        ",
                        'epd_demo_template',
                        (int) $demo_id
                    )
                );

                update_post_meta( $demo_id, '_epd_build_count', (int) $count );
            }
        }
    }

	// Let us know if an upgrade has happened
	if ( version_compare( $epdp_version, EPD_PREMIUM_VERSION, '<' ) )	{
		$did_upgrade = true;
	}

	if ( $did_upgrade )	{
		update_site_option( 'epd_premium_version_upgraded_from', $epdp_version );
		update_site_option( 'epd_premium_version', preg_replace( '/[^0-9.].*/', '', EPD_PREMIUM_VERSION ) );
	}
} // upgrades
add_action( 'admin_init', 'epd_premium_upgrades' );
