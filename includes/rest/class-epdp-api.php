<?php
/**
 * Easy Plugin Demo REST API
 *
 * @package     EPD
 * @subpackage  Classes/REST API
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * EPDP_API Class
 *
 * @since	1.0.8
 */
class EPDP_API	{
	/**
     * Namespace
     *
     * @since   1.5
     * @var     string
     */
    protected $url = 'epd/v';

	/**
	 * Version
	 *
	 * @since	1.5
	 * @var		int
	 */
	protected $version = '1';

    /**
     * Whether or not the remote API is enabled.
     *
     * @since   1.5
     * @var     bool
     */
    public $api_enabled;

    /**
     * The remote API phrase.
     *
     * @since   1.5
     * @var     string
     */
    public $remote_phrase;

    /**
     * Paramaters for the API request.
     *
     * @since   1.5
     * @var     array
     */
    public $params = array();

    /**
	 * Get things going
	 *
	 * @since	1.5
	 */
	public function __construct()	{
        add_action( 'init',          array( $this, 'setup_api'          ) );
		add_action( 'rest_api_init', array( $this, 'register_routes'    ) );
		add_action( 'rest_api_init', array( $this, 'register_demo_meta' ) );

        add_action( 'epdp_site_created_via_rest', array( $this, 'add_site_meta' ), 10, 3 );

		add_action( 'epdp_generating_phrase',        array( $this, 'erase_phrase' ) );
		add_action( 'epdp_generate_secret_phrase',   array( $this, 'set_phrase'   ) );

        add_action( 'wp_ajax_epdp_reveal_phrase',         array( $this, 'reveal_phrase'         ) );
        add_action( 'wp_ajax_epdp_regenerate_phrase',     array( $this, 'regenerate_phrase'     ) );
        add_action( 'wp_ajax_epdp_reenter_remote_phrase', array( $this, 'reenter_remote_phrase' ) );
	} // __construct

    /**
     * Setup the API
     *
     * @since   1.5
     * @return  void
     */
    public function setup_api() {
        $this->api_enabled   = epd_get_option( 'enable_rest', false );
        $this->remote_phrase = epd_get_option( 'remote_phrase' );
    } // setup_api

    /**
     * Whether or not the request is authorized.
     *
     * @since   1.5
     * @return  bool    True if authorized, or false
     */
    public function is_authorized( $key = '' ) {
        $authorized = is_user_logged_in() && is_super_admin();

        if ( ! $authorized )    {
            $phrase = $this->get_phrase();

            if ( ! empty( $key ) && isset( $this->params['remote_phrase'] ) ) {
                if ( ! empty( $phrase ) && ! empty( $this->remote_phrase ) ) {
                    return $key === $phrase && $this->params['remote_phrase'] === $this->remote_phrase;
                }
            }
        }

        return $authorized;
    } // is_authorized

	/**
	 * Register the custom EPD routes.
	 *
	 * @since	1.5
	 */
	public function register_routes()	{
		$namespace = $this->url . $this->version;

		register_rest_route(
			$namespace,
			'/demos',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_demo_data' ),
				'permission_callback' => array( $this, 'get_demos_permissions_check' )
			)
		);

        if ( $this->api_enabled )   {
            register_rest_route(
                $namespace,
                '/epd_sites',
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_site' ),
                    'permission_callback' => array( $this, 'create_site_permissions_check' )
                )
            );
        }
	} // register_routes

	/**
	 * Register demo meta fields to rest api.
	 *
	 * @since   1.2
	 * @return  void
	 */
	public function register_demo_meta()  {
        $meta_fields = array(
            '_epd_site_ref' => array(
                'type'              => 'string',
                'description'       => __( 'Demo reference.', 'epd-premium' ),
                'single'            => true,
                'show_in_rest'      => true
            ),
            '_epd_build_count' => array(
                'type'              => 'integer',
                'description'       => __( 'Build count.', 'epd-premium' ),
                'single'            => true,
                'default'           => 0,
                'sanitize_callback' => 'absint',
                'show_in_rest'      => true
            )
        );

        foreach( $meta_fields as $field => $args )  {
            register_post_meta( 'epd_demo', $field, $args );
        }
	} // register_demo_meta

	/**
	 * Checks if a given request has access to read demos.
	 *
	 * @since   1.2.1
	 * @param   WP_REST_Request $request Full details about the request.
	 * @return  true|WP_Error   True if the request has read access, WP_Error object otherwise.
	 */
	public function get_demos_permissions_check( $request ) {
		$post_type = get_post_type_object( 'epd_demo' );

		if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to edit posts in this post type.', 'epd-premium' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	} // get_demos_permissions_check

	/**
	 * Check if a request has access to create sites.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request	$request	Full data about the request.
	 * @return	WP_Error|bool
	 */
	public function create_site_permissions_check( $request ) {
		$key          = $request->get_header( 'phrase' );
        $this->params = $request->get_body_params();

        if ( empty( $this->params ) )   {
            $this->params = $request->get_json_params();
        }

        return $this->is_authorized( $key );
	} // create_site_permissions_check

	/**
	 * Create a new site.
	 *
	 * @since	1.5
	 * @param	WP_REST_Request	$request	Full data about the request.
	 * @return	WP_Error|WP_REST_Response
	 */
 	public function create_site( $request ) {
		$data = $this->validate_demo_request( $request );

        if ( is_wp_error( $data ) )    {
            return $data;
        }

        do_action( 'epdp_before_site_created_via_rest', $data, $request );

		if ( isset( $data['demo_id'] ) ) {
			new EPDP_Demo_Builder( $data['demo_id'] );
		}

		$blog_id = epd_process_registration( $data );
 
        if ( ! $blog_id )   {
            return new WP_Error(
                'cant_create',
                __( 'Unable to create site.', 'epd-premium' ),
                array( 'status' => 500 )
            );
        }

        do_action( 'epdp_site_created_via_rest', $blog_id, $data, $request );

        return $this->send_site_response( $blog_id, $data );
	} // create_site

    /**
	 * Prepare rest response for new demo site
	 *
	 * @since	1.5
     * @param   int     $site_id    ID of the new site
     * @param   array   $data       Array of data used during registration
	 * @return	array	Array of settings
	 */
	public function send_site_response( $site_id, $data )	{
        $user_id        = epd_get_site_primary_user_id( $site_id );
		$user           = get_userdata( $user_id );
		$data           = array();
		$after_register = epd_get_option( 'registration_action' );
        $after_register = apply_filters( 'epd_after_user_registration_action', $after_register, $site_id );

		switch( $after_register )	{
			case 'home':
				switch_to_blog( get_network()->blog_id );
				$redirect_url = apply_filters(
					'epd_after_registration_home_redirect_url',
					get_home_url( $site_id )
				);
				restore_current_blog();
				break;
			case 'admin':
				switch_to_blog( get_network()->blog_id );
				$redirect_url = apply_filters(
					'epd_after_registration_admin_redirect_url',
					get_admin_url( $site_id )
				);
				restore_current_blog();
				break;
			case 'confirm':
				$message      = is_archived( $site_id ) ? 'pending' : 'created';
				$redirect_url = add_query_arg( array(
					'epd-registered' => $site_id,
					'epd-message'    => $message
				), epd_get_registration_page_url() );

				$redirect_url = apply_filters(
					'epd_after_registration_confirm_redirect_url',
					$redirect_url
				);
				break;
			case 'redirect':
				$page         = epd_get_option( 'redirect_page', false );
				$redirect_url = get_permalink( $page );
				$redirect_url = apply_filters(
					'epd_after_registration_redirect_url',
					$redirect_url,
					$site_id
				);
				break;
		}

        $data = array(
			'admin_url'           => get_admin_url( $site_id ),
            'code'                => 'epd_demo_created',
			'demo'                => epd_email_tag_demo_product_name( $site_id, $user_id ),
			'expires'             => epd_get_site_expiration_date( $site_id ),
			'first_name'          => $user->first_name,
			'home_url'            => get_blog_details( $site_id )->home,
			'ID'                  => $site_id,
			'last_name'           => $user->last_name,
			'requires_activation' => is_archived( $site_id ) ? true : false,
			'url'                 => $redirect_url,
            'user_id'             => $user_id
        );

		return rest_ensure_response( $data );
	} // send_site_response

	/**
	 * Prepare EPD settings for rest response.
	 *
	 * @since	1.2
	 * @return	array	Array of settings
	 */
	public function get_demo_data()	{
		$data      = array();
		$demos     = array();
        $mailchimp = epdp_mailchimp_display_signup_field() && epdp_mailchimp_get_api_key() ? true : false;
		$settings  = array(
			'register_page' => epd_get_registration_page_url()
		);

        $integrations = array(
            'mailchimp' => $mailchimp ? epdp_mailchimp_get_signup_label() : false
        );

		$all_demos = get_posts( array(
			'post_type'      => 'epd_demo',
			'post_status'    => 'publish',
			'posts_per_page' => 250,
			'orderby'        => 'title',
			'order'          => 'ASC'
		) );

		foreach( $all_demos as $demo )	{
			$demo_key = epdp_get_demo_key( $demo->ID );

			if ( empty( $demo_key ) )	{
				continue;
			}

			$demos[ $demo->ID ] = array(
				'title' => get_the_title( $demo ),
				'ref'   => $demo_key,
				'url'   => get_permalink( $demo->ID ),
                'count' => epdp_get_template_build_count( $demo->ID )
			);
		}

		$data['demos']        = $demos;
		$data['settings']     = $settings;
        $data['integrations'] = $integrations;

		return $data;
	} // get_demo_data

    /**
	 * Validate a demo request.
	 *
	 * @since  1.5
	 *
	 * @param  WP_REST_Request $request    Request object.
	 * @return array           Array of data for demo creation
	 */
	protected function validate_demo_request( $request ) {
        $required_fields = epd_get_required_registration_fields();
        $response        = array();

        foreach ( $required_fields as $required_field )	{
            if ( empty( $this->params[ $required_field ] ) )	{
                return new WP_Error(
                    'required_fields',
                    __( 'Required fields missing.', 'epd-premium' ),
                    array( 'status' => 500 )
                );
            } elseif ( is_email( $this->params[ $required_field ] ) )  {
                $email = sanitize_email( $this->params[ $required_field ] );

                $limited_email_domains = get_site_option( 'limited_email_domains' );

                if ( is_array( $limited_email_domains ) && ! empty( $limited_email_domains ) ) {

                    $limited_email_domains = array_map( 'strtolower', $limited_email_domains );
                    $emaildomain           = strtolower( substr( $email, 1 + strpos( $email, '@' ) ) );

                    if ( ! in_array( $emaildomain, $limited_email_domains, true ) ) {
                        return new WP_Error(
                            'no_register',
                            __( 'Invalid email address.', 'epd-premium' ),
                            array( 'status' => 500 )
                        );
                    }
                }

                if ( is_email_address_unsafe( $email ) || ! epd_can_user_register( $email ) )    {
                    return new WP_Error(
                        'no_register',
                        __( 'User site count exceeded.', 'epd-premium' ),
                        array( 'status' => 500 )
                    );
                }
            }

            $response[ str_replace( 'epd_', '', $required_field ) ] = $this->params[ $required_field ];
        }

        // Demo template
        if ( isset( $this->params['demo_ref'] ) )   {
            $demo_id  = epdp_get_demo_id_from_key( $this->params['demo_ref'] );
            $demo_id  = 'epd_demo' == get_post_type( $demo_id ) ? absint( $demo_id ) : false;

            if ( ! $demo_id ) {
                return new WP_Error(
                    'invalid_demo',
                    __( 'Requested demo not found.', 'epd-premium' ),
                    array( 'status' => 500 )
                );
            }

            $response['demo_id'] = $demo_id;
        }

        $response = apply_filters( 'epdp_api_response', $response, $this->params );
        return $response;
    } // validate_demo_request

    /**
     * Add specific REST API data to the blog meta.
     *
     * @since   1.5.1
     * @param   int     $site_id  New site ID
     * @param	array	$data	  Array of processed site data
     * @param	WP_REST_Request   $request	Full data about the request.
	 * @return	array	Array of site meta
     */
    public function add_site_meta( $site_id, $data, $request ) {
        $options = array(
            'epd_rest_api' => 'v' . $this->version
        );

        $options = apply_filters( 'epdp_rest_api_blog_meta', $options );

        foreach( $options as $key => $value )   {
            update_site_meta( $site_id, $key, $value );
        }
    } // add_site_meta

	/**
	 * Generates and saves new API phrase.
	 *
	 * @since	1.5
	 * return	void
	 */
	public function generate_secret_phrase()	{
		$value = wp_generate_password( 12, false, false );

		switch_to_blog( get_network()->blog_id );
		/**
		 * Runs before the phrase is generated.
		 *
		 * @since	1.5
		 */
		do_action( 'epdp_generating_phrase' );
		update_site_option( 'epdp_faux', $value );
		/**
		 * Runs after the phrase is generated.
		 *
		 * @since	1.5
		 */
		do_action( 'epdp_generate_secret_phrase' );
		restore_current_blog();
	} // generate_secret_phrase

	/**
	 * Reset a secret phrase.
	 *
	 * @since	1.5
	 */
	public function erase_phrase()	{
		global $wpdb;

		switch_to_blog( get_network()->blog_id );
		$current = get_site_option( 'epdp_faux' );

		if ( $current )	{
            $wpdb->query( 
                $wpdb->prepare( 
                    "
                    DELETE FROM $wpdb->sitemeta
                    WHERE meta_value = %s
                    ",
                    $current . '-phrase'
                )
            );
		}
		restore_current_blog();
	} // erase_phrase

	/**
	 * Set a secret phrase.
	 *
	 * @since	1.5
	 */
	public function set_phrase()	{
		switch_to_blog( get_network()->blog_id );
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$random   = wp_generate_password( 24, true, true );
		$secret   = hash( 'md5', $random . $auth_key . date( 'U' ) );
		$key      = get_site_option( 'epdp_faux' );

		if ( $key )	{
			update_site_option( $secret, $key . '-phrase' );
		}
		restore_current_blog();
	} // set_phrase

	/**
	 * Retrieve a secret phrase.
	 *
	 * @since	1.5
	 */
	public function get_phrase()	{
		global $wpdb;

		switch_to_blog( get_network()->blog_id );
		$result = false;
		$value  = get_site_option( 'epdp_faux' );

        if ( ! $value ) {
            $this->generate_secret_phrase();
            $value = get_site_option( 'epdp_faux' );
        }

		if ( $value )	{
			$result = $wpdb->get_var( $wpdb->prepare(
                "
                SELECT meta_key
                FROM $wpdb->sitemeta
                WHERE meta_value = %s
                ",
				$value . '-phrase'
            ) );
		}
		restore_current_blog();

		return $result;
	} // get_phrase

    /**
	 * Reveals the phrase.
	 *
	 * @since	1.5
	 */
	public function reveal_phrase()	{
		$phrase = $this->get_phrase();

        if ( empty( $phrase ) ) {
            $this->generate_secret_phrase();
            $phrase = $this->get_phrase();
        }

        $phrase = ! empty( $phrase ) ? $phrase : '';

        wp_send_json_success( array(
			'phrase' => $phrase
		) );
	} // reveal_phrase

    /**
	 * Regenerate a phrase.
	 *
	 * @since	1.5
	 */
	public function regenerate_phrase()	{
        $this->generate_secret_phrase();

        wp_send_json_success();
	} // regenerate_phrase

    /**
	 * Re-enter remote phrase.
	 *
	 * @since	1.5
	 */
	public function reenter_remote_phrase()	{
        $key           = 'remote_phrase';
		$remote_phrase = epd_get_option( $key );
        $settings      = epd_get_registered_settings();

        if ( isset( $settings['features']['rest'][ $key ] ) )    {
            ob_start();

            epd_text_callback( $settings['features']['rest'][ $key ] );

            $html = ob_get_clean();

            wp_send_json_success( array(
                'input' => $html
            ) );
        }
	} // reenter_remote_phrase

} // EPDP_API
