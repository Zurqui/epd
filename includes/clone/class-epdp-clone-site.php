<?php
/**
 *EPDP Site Cloner Class
 *
 * @package		EPD
 * @subpackage	Sites
 * @since		1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * EPDP_Site_Cloner Class
 *
 * @since	1.0
 */
class EPDP_Site_Cloner {
    /**
	 * The new site ID
	 *
	 * @since	1.0
	 * @var		int
	 */
    public $site_id = 0;

    /**
	 * The ID of the site to clone
	 *
	 * @since	1.0
	 * @var		int
	 */
    private $clone_id = 0;

	/**
	 * Cloning from demo
	 *
	 * @since	1.1
	 * @var		int
	 */
	private $demo_id = 0;

    /**
	 * The table prefix for the new site
	 *
	 * @since	1.0
	 * @var		string
	 */
    private $new_prefix;

    /**
	 * The table prefix for the site being cloned
	 *
	 * @since	1.0
	 * @var		string
	 */
    private $old_prefix;

    /**
	 * The tables to clone
	 *
	 * @since	1.0
	 * @var		array
	 */
    private $tables = array();

	/**
	 * The uploads path for the cloner master
	 *
	 * @since	1.4.1
	 * @var		string
	 */
    private $clone_base_dir = '';

	/**
	 * The uploads path for the new site
	 *
	 * @since	1.4.1
	 * @var		string
	 */
    private $site_base_dir = '';

	/**
	 * The media file archive
	 *
	 * @since	1.4.1
	 * @var		string
	 */
    private $media_archive;

    /**
	 * The current table being cloned
	 *
	 * @since	1.0
	 * @var		string
	 */
    private $current_table;

	/**
	 * Array of plugins to activate
	 *
	 * @since	1.0
	 * @var		array
	 */
	private $plugins = array();

    /**
	 * Array of allowed themes for the new site
	 *
	 * @since	1.0
	 * @var		array
	 */
	private $allowed_themes = array();

    /**
	 * Theme to set for the new site
	 *
	 * @since	1.0
	 * @var		site
	 */
	private $theme_stylesheet;
    private $theme_template;

    /**
     * Options table columns that should be excluded from the cloning process
     *
     * @since   1.0
     * @var     array
     */
    public $excluded_option_columns = array();

    /**
     * Option data that should be excluded from the cloning process
     *
     * @since   1.0
     * @var     array
     */
    private $excluded_option_data = array();

    /**
     * The counter for the number of tables cloned
     *
     * @since   1.0
     * @var     int
     */
    public $table_count = 0;

    /**
     * Whether or not the cloner is ready to clone
     *
     * @since   1.0
     * @var     bool
     */
    private $cloner_ready = false;

    /**
	 * Setup the EPDP_Site_Cloner class
	 *
	 * @since	1.0
	 * @param	int		$site_id	The ID of the new site to clone into
	 * @param	int		$demo_id	ID of the demo template
	 * @return	mixed	void|false
	 */
	public function __construct( $site_id = false, $demo_id = 0 ) {
		if ( empty( $site_id ) ) {
			return false;
		}

		$this->cloner_ready = $this->setup_cloner( $site_id, $demo_id );
	} // __construct

    /**
	 * Setup the cloner properties.
	 *
	 * @since	1.0
	 * @param 	int		$site_id	The new site ID
	 * @param	int		$demo_id	ID of the demo template
	 * @return	bool	True if the setup was successful
	 */
	private function setup_cloner( $site_id, $demo_id = 0 ) {
		if ( empty( $site_id ) ) {
			return false;
		}

        $clone_id = empty( $demo_id ) ? epdp_get_clone_master() : epdp_get_demo_clone_site( $demo_id );

        if ( empty( absint( $clone_id ) ) )  {
            return false;
        }

        $site  = get_site( $site_id );
        $clone = get_site( $clone_id );

        if ( empty( $site ) || empty( $clone ) )    {
            return false;
        }

		// Extensions can hook here perform actions before the cloner data is loaded
		do_action( 'epdp_pre_setup_cloner', $this, $site_id );

		// Primary Identifiers
		$this->site_id  = absint( $site_id );
        $this->clone_id = absint( $clone_id );
		$this->demo_id  = absint( $demo_id );

        // Database vars
		$this->new_prefix = $this->setup_table_prefix( $this->site_id );
        $this->old_prefix = $this->setup_table_prefix( $this->clone_id );
        $this->tables     = $this->setup_clone_tables();

		// Plugins & Themes
		$this->plugins = $this->setup_plugins();
        $this->setup_themes();

        // Option vars
        $this->excluded_option_columns = $this->setup_excluded_option_columns();

		// Media files
		$this->clone_base_dir = $this->setup_uploads_path( $this->clone_id );
		$this->site_base_dir  = $this->setup_uploads_path( $this->site_id );
		$this->media_archive  = $this->clone_base_dir . "epd-clone-site-{$this->clone_id}.zip";

		// Extensions can hook here to add items to this object
		do_action( 'epdp_setup_cloner', $this, $site_id );
								
		return true;
	} // setup_cloner

    /**
     * Setup the table prefix for the site.
     *
     * @since   1.0
     * @param   int     $site_id    The site ID for which we want the prefix
     * @return  string  The table prefix for the site
     */
    private function setup_table_prefix( $site_id ) {
        global $wpdb;
    
        return $wpdb->get_blog_prefix( $site_id );
    } // setup_table_prefix

    /**
     * Setup the table names to clone.
     *
     * @since   1.0
     * @return  array   Array of database table names to clone
     */
    private function setup_clone_tables()   {
		if ( ! empty( $this->demo_id ) )	{
			$tables = epdp_get_db_table_setting_options( $this->clone_id );
		} else	{
			$tables = epd_get_option( 'clone_tables', array() );
		}

        if ( ! empty( $tables ) )   {
            $tables = array_keys( $tables );
        }

        return apply_filters( 'epdp_clone_tables', $tables );
    } // setup_clone_tables

	/**
	 * Setup the uploads path base directory for files.
	 *
	 * @since	1.4.1
	 * @param	int		Site ID for which to get the upload path
	 * @return	string	Path to uploads directory
	 */
	private function setup_uploads_path( $site_id )	{
		switch_to_blog( $site_id );

		$upload_dir = wp_get_upload_dir();
		$base_dir   = $upload_dir['basedir'] . '/';

		restore_current_blog();

		return $base_dir;
	} // setup_uploads_path

	/**
	 * Setup the plugins that need activating.
	 *
	 * We should activate before cloning tables to ensure hooks run.
	 *
	 * @since	1.0
	 * @return	array	Array of active plugins on the site to clone
	 */
	private function setup_plugins()	{
		if ( ! empty( $this->demo_id ) )	{
			$action = epdp_get_demo_clone_plugin_action( $this->demo_id );
		} else	{
			$action = epd_get_option( 'plugins_action', 'clone' );
		}

        switch( $action )   {
            case 'clone':
				$plugins = get_blog_option( $this->clone_id, 'active_plugins', array() );
                break;

            case 'honor':
				if ( ! empty( $this->demo_id ) )	{
					$plugins = epdp_get_demo_plugins( $this->demo_id );
				} else	{
					$plugins = epd_plugins_to_activate();
				}
                break;

            case 'none':
                $plugins = array();
                break;
        }

		return $plugins;
	} // setup_plugins

    /**
	 * Setup the themes for the site.
	 *
	 * @since	1.0
	 * @return	void
	 */
	private function setup_themes()	{
		if ( ! empty( $this->demo_id ) )	{
			$action = epdp_get_demo_clone_theme_action( $this->demo_id );
		} else	{
			$action = epd_get_option( 'themes_action', 'clone' );
		}

        $themes = array();

        switch( $action )   {
            case 'clone':
            default:
                $themes     = get_blog_option( $this->clone_id, 'allowedthemes', array() );
                $template   = get_blog_option( $this->clone_id, 'template' );
                $stylesheet = get_blog_option( $this->clone_id, 'stylesheet' );
                break;

            case 'honor':
				if ( ! empty( $this->demo_id ) )	{
					$allowed_themes = epdp_get_demo_allowed_themes( $this->demo_id );
					$theme          = wp_get_theme( epdp_get_demo_theme( $this->demo_id ) );
				} else	{					
					$allowed_themes = epd_get_option( 'allowed_themes', array() );
					$theme          = wp_get_theme( epd_get_option( 'theme' ) );
				}

                $themes = array();

                if ( ! empty( $allowed_themes ) )   {
                    foreach( $allowed_themes as $allowed_theme )    {
                        $_theme = wp_get_theme( $allowed_theme );
                        if ( ! $_theme->exists() || ! $_theme->is_allowed( 'site', $this->site_id ) )	{
                            $themes[ $_theme->stylesheet ] = true;
                        }
                    }
                }

                if ( ! $theme->exists() )	{
                    $theme = wp_get_theme();
                }

                if ( ! array_key_exists( $theme->stylesheet, $themes ) )    {
                    $themes[ $theme->stylesheet ] = true;
                }

                $template   = $theme->template;
                $stylesheet = $theme->stylesheet;
                break;
        }

		$this->allowed_themes   = $themes;
		$this->theme_template   = $template;
		$this->theme_stylesheet = $stylesheet;
	} // setup_themes

    /**
     * Setup the options table excluded column names to clone.
     *
     * @since   1.0
     * @return  array   Array of column names to exclude from the options table clone
     */
    private function setup_excluded_option_columns()   {
        $exclusions = array(
            'siteurl',
            'blogname',
            'blogdescription',
            'home',
            'admin_email',
            'upload_path',
            'upload_url_path',
            'active_plugins',
            'allowedthemes',
            'template',
            'stylesheet',
            'new_admin_email',
            $this->new_prefix . 'user_roles'
        );

        foreach( epd_get_default_blog_meta() as $key => $value )  {
            $exclusions[] = $key;
        }

        $exclusions = apply_filters( 'epdp_replication_options_exclusions', $exclusions, $this->demo_id );

        return $exclusions;
    } // setup_excluded_option_columns

    /**
     * Retrieve data from options table of new blog.
     *
     * This data will be restored after the clone completes.
     *
     * @since   1.0
     * @return  array   Array of objects containing table rows
     */
    public function get_excluded_options_data()   {
        global $wpdb;

		$excluded_options_data = array();

		foreach( $this->excluded_option_columns as $column )	{
			$excluded_options_data[ $column ] = get_blog_option( $this->site_id, $column );
		}

        return $excluded_options_data;
    } // get_excluded_options_data

	/**
     * Activate plugins.
     *
     * @since   1.0
     * @return  void
     */
	public function activate_plugins()	{
		switch_to_blog( $this->site_id );

		foreach( $this->plugins as $plugin )	{
			if ( ! is_plugin_active( $plugin ) )	{
				$activate = activate_plugin( $plugin );
			}
		}

		restore_current_blog();
	} // activate_plugins

    /**
     * Activate themes.
     *
     * @since   1.0
     * @return  void
     */
	public function activate_themes()	{
		update_blog_option( $this->site_id, 'allowedthemes', $this->allowed_themes );
        update_blog_option( $this->site_id, 'template', $this->theme_template );
        update_blog_option( $this->site_id, 'stylesheet', $this->theme_stylesheet );
	} // activate_themes

    /**
     * Restore options table rows to new blog.
     *
     * @since   1.0
     * @return  void
     */
    public function restore_options_data()   {
        foreach( (array) $this->excluded_option_data as $option => $value )  {
            delete_blog_option( $this->site_id, $option );
            update_blog_option( $this->site_id, $option, $value );
        }

        $this->excluded_option_data = array();
    } // restore_options_data

    /**
     * Create the DB tables as needed.
     *
     * @since   1.0
     * @return  bool
     */
    public function maybe_create_table()    {
        global $wpdb;

        $new_table = $this->new_prefix . $this->current_table;
        $old_table = $this->old_prefix . $this->current_table;
        $query     = "
            CREATE TABLE IF NOT EXISTS {$new_table}
            LIKE {$old_table}
        ";

        return (bool) $wpdb->query( $query );
    } // maybe_create_table

    /**
     * Drop the existing table from the new blog.
     *
     * @since   1.0
     * @return  bool   True if successful
     */
    public function empty_table()   {
        global $wpdb;

        $this->maybe_create_table();

        $table = $this->new_prefix . $this->current_table;
        $query = "TRUNCATE TABLE {$table}";

        return (bool) $wpdb->query( $query );
    } // empty_table

    /**
     * Clones a table to the new blog from the template blog.
     *
     * @since   1.0
     * @return  bool   True if successful
     */
    public function clone_table()   {
        global $wpdb;

        $new_table = $this->new_prefix . $this->current_table;
        $old_table = $this->old_prefix . $this->current_table;
        $query_new = "INSERT {$new_table} SELECT * FROM {$old_table}";

        if ( 'options' == $this->current_table )    {
            $excluded_options_list = implode( "','", $this->excluded_option_columns );
            $query_new .= " WHERE option_name NOT IN('{$excluded_options_list}')";
        }

        return (bool) $wpdb->query( $query_new );
    } // clone_table

	/**
	 * Replicate clone site media files.
	 *
	 * @since	1.4.1
	 * @return	void
	 */
	public function replicate_media()	{
		if ( $this->create_archive() )	{
			if ( $this->extract_archive() )	{
				$this->delete_archive();
			}
		}
	} // replicate_media

	/**
	 * Archive clone site media files.
	 *
	 * @since	1.4.1
	 * @return	bool	True on success, or false
	 */
	public function create_archive()	{
		if ( extension_loaded( 'zip' ) === true )	{
			if ( file_exists( $this->clone_base_dir ) === true)	{
				$zip      = new ZipArchive();
				$zip_file = $this->clone_base_dir . "epd-clone-site-{$this->clone_id}.zip";

				if ( $zip->open( $zip_file, ZIPARCHIVE::CREATE ) === true)	{
					$source = realpath( $this->clone_base_dir );

					if ( is_dir( $source ) === true )	{
						$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $source ), RecursiveIteratorIterator::SELF_FIRST );

						foreach ( $files as $file )	{
							$file = realpath( $file );

							if ( is_dir( $file ) === true )	{
								$zip->addEmptyDir( str_replace( $source . '/', '', $file . '/' ) );
							} else if ( is_file( $file ) === true )	{
								$zip->addFromString( str_replace( $source . '/', '', $file ), file_get_contents( $file ) );
							}
						}
					} elseif ( is_file( $source ) === true) {
						$zip->addFromString( basename( $source ), file_get_contents ( $source ) );
					}
				}

				return $zip->close();
			}
		}
	} // create_archive

	/**
	 * Extract media archive to new site.
	 *
	 * @since	1.4.1
	 * @return	bool	True on success, or false
	 */
	public function extract_archive()	{
		$zip = new ZipArchive;

		if ( $zip->open( $this->media_archive ) === TRUE )	{
			if ( $zip->extractTo( $this->site_base_dir ) )	{
				return $zip->close();
			}
		}
	} // extract_archive

	/**
	 * Delete clone site media files archive.
	 *
	 * @since	1.4.1
	 * @return	bool	True on success, or false
	 */
	public function delete_archive()	{
		unlink( $this->media_archive );
	} // delete_archive

    /**
     * Execute the clone process for the new site.
     *
     * @since   1.0
     * @return  void
     */
    public function execute_clone() {
        // Activate plugins & themes
		$this->activate_plugins();
        $this->activate_themes();

        // Store the data we're not cloning
        $this->excluded_option_data = $this->get_excluded_options_data();

        do_action( 'epdp_before_site_clone', $this );

        // Process cloning
        foreach( $this->tables as $this->current_table )    {
            if ( $this->empty_table() && $this->clone_table() )  {
                $this->table_count++;
            }
        }

        // Restore options table data
        $this->restore_options_data();

		// Replicate files
		$this->replicate_media();

        do_action( 'epdp_site_cloned', $this );

        update_site_meta( $this->site_id, 'epd_clone_site', $this->clone_id );

        return $this->table_count;
    } // execute_clone

    /**
     * Whether or not the cloner is ready to clone.
     *
     * @since   1.0
     * @return  bool
     */
    public function cloner_ready()  {
        return $this->cloner_ready;
    } // cloner_ready

} // EPDP_Site_Cloner
