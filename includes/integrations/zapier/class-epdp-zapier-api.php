<?php
/**
 * Easy Plugin Demo Zapier Integration REST API
 *
 * @package     EPD
 * @subpackage  Classes/REST API
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * EPDP_Zapier_API Class
 *
 * @since	1.5.1
 */
class EPDP_Zapier_API extends EPDP_API	{
    /**
	 * Get things going
	 *
	 * @since	1.5.1
	 */
	public function __construct()	{
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Test triggers
		add_action( 'init', array( $this, 'send_sample_data' ) );

		// Processing
		add_action( 'epdp_zap_demo_registered', array( $this, 'process_triggers' ), 10, 2 );
		add_action( 'epdp_zap_demo_deleted',    array( $this, 'process_triggers' ), 10, 2 );
        add_action( 'epdp_zap_demo_reset',      array( $this, 'process_triggers' ), 10, 2 );

        // Remove hooks during demo resets
        add_action( 'epd_before_site_reset', array( $this, 'remove_reset_actions' ) );
	} // __construct

    /**
     * Whether or not the request is authorized.
     *
     * @since   1.5.1
     * @return  bool    True if authorized, or false
     */
    public function is_authorized( $key = '' ) {
        $phrase = $this->get_phrase();

        if ( ! empty( $key ) && ! empty( $phrase ) ) {
            return $key === $phrase;
        }

        return false;
    } // is_authorized

	/**
	 * Register the custom EPD routes.
	 *
	 * @since	1.5.1
     * @return  void
	 */
	public function register_routes()	{
		$namespace = $this->url . $this->version;

        register_rest_route(
            $namespace,
            '/zap_subs',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'manage_subscription_hook' ),
                    'permission_callback' => array( $this, 'permissions_check' )
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'manage_subscription_hook' ),
                    'permission_callback' => array( $this, 'permissions_check' )
                ),
				array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'manage_subscription_hook' ),
                    'permission_callback' => array( $this, 'permissions_check' )
                )
            )
        );
	} // register_routes

    /**
     * Retrieve subscription hooks.
     *
     * @since   1.5.1
     * @return  array   Array of routes => hooks
     */
    public function get_hooks() {
        return get_site_option( 'epd_zap_hooks', array() );
    } // get_hooks

    /**
     * Retrieve a single subscription hook.
     *
     * @since   1.5.1
     * @param   string  $trigger  The trigger for which we are retrieving the hook
     * @return  string  The subscription hook URL
     */
    public function get_hook( $trigger )  {
        $hooks = $this->get_hooks();

        return ( array_key_exists( $trigger, $hooks ) ? $hooks[ $trigger ] : false );
    } // get_hook

    /**
     * Sets a subscription hook.
     *
     * @since   1.5.1
     * @param   string  $trigger	The trigger for which we are setting the hook
     * @param   string  $hook		The subscription hook URL
     * @return  string  The subscription hook URL
     */
    public function set_hook( $trigger, $hook )  {
        $hooks             = $this->get_hooks();
        $hooks[ $trigger ] = $hook;

        if ( update_site_option( 'epd_zap_hooks', $hooks ) )    {
            return $this->get_hook( $trigger );
        }

        return false;
    } // set_hook

    /**
     * Deletes a subscription hook.
     *
     * @since   1.5.1
     * @param   string  $trigger	The trigger for which we are deleting the hook
     * @param   string  $hook   	The subscription hook URL
     * @return  bool
     */
    public function delete_hook( $trigger, $hook )  {
		$hooks = $this->get_hooks();
        $hook  = $this->get_hook( $trigger );

        if ( $hook )    {
            unset( $hooks[ $trigger ] );
        }

        return update_site_option( 'epd_zap_hooks', $hooks );
    } // delete_hook

	/**
	 * Check if a request is authorized.
	 *
	 * @since	1.5.1
	 * @param	WP_REST_Request	$request	Full data about the request.
	 * @return	WP_Error|bool
	 */
	public function permissions_check( $request ) {
		$key = $request->get_header( 'phrase' );

        return $this->is_authorized( $key );
	} // permissions_check

    /**
	 * Set the demo registration hook.
	 *
	 * @since	1.5.1
	 * @param	WP_REST_Request	$request	Full data about the request.
	 * @return	WP_Error|WP_REST_Response
	 */
 	public function manage_subscription_hook( $request ) {
        $hook    = $request->get_param( 'hookUrl' );
        $trigger = $request->get_param( 'sub_type' );
        $return  = false;

        switch( $request->get_method() )    {
            case 'POST':
                $return = $this->set_hook( $trigger, $hook );
                break;
            case 'DELETE':
                $return = $this->delete_hook( $trigger, $hook );
                break;
			case 'GET':
				$return = true;
				break;
        }

        if ( $return )  {
            return $this->send_response( array() );
        }
	} // manage_subscription_hook

	/**
	 * Process triggers and perpare data.
	 *
	 * @since	1.5.1
	 * @param	int		$post_id	ID of zap post
	 * @param	array	$data		Array of data to send
	 * @return	@void
	 */
 	public function process_triggers( $post_id, $data ) {
		$post_data = get_post_meta( $post_id, '_epd_zap_data', true );

		if ( ! empty( $post_data ) && is_array( $post_data ) )  {
			$post_data = array( 'id' => $post_id ) + $post_data;
			$trigger   = get_post_meta( $post_id, '_epd_zap_type', true );
		}

		wp_delete_post( $post_id, true );

        $this->post_data( $post_data, $trigger );
	} // process_triggers

	/**
	 * Retrieve test data.
	 *
	 * @since	1.5.1
	 * @param	string	$trigger	Trigger
	 * @return	array	Array of test data
	 */
	public function get_test_data( $trigger = 'registered' )	{
		switch( $trigger )	{
			case 'registered':
			case 'deleted':
            case 'reset':
			default:
				$data = array(
					'id'         => 100,
					'first_name' => 'John',
					'last_name'  => 'Smith',
					'email'      => 'john.smith@somedomain.com',
					'site_url'   => 'https://epddemo.com/johnsmithsomedomain.com',
					'registered' => '2020-12-02 14:27:29',
					'lifetime'   => 3600,
					'expires'    => '20202-12-02 15:27:29'
				);
				break;
		}

		return $data;
	} // get_test_data

	/**
	 * Send test data.
	 *
	 * @since	1.5.1
	 * @return	void
	 */
	function send_sample_data()	{
		if ( ! isset( $_GET['epd_action'] ) || 'zap_test' != $_GET['epd_action'] )	{
			return;
		}

		if ( ! wp_verify_nonce( $_GET['epd_nonce'], 'test_zap' ) ) {
			wp_die(
				__( 'Nonce verification failed', 'epd-premium' ),
				__( 'Error', 'epd-premium' ),
				array(
					'response' => 401
				)
			);
		}

		$trigger = $_GET['trigger'];

		$data = $this->get_test_data( $trigger );

		$this->post_data( $data, $trigger );

        $redirect = add_query_arg( array(
            'page'        => 'epd-settings',
            'tab'         => 'integrations',
            'section'     => 'zapier',
            'epd-message' => 'zap-data-sent'
        ), network_admin_url( 'settings.php' ) );

        wp_safe_redirect( $redirect );
        exit;
	} // send_sample_data

    /**
	 * Prepare rest response for new demo site
	 *
	 * @since	1.5.1
     * @param   array   $data   Array of data to be sent
	 * @param	string	trigger	The trigger to be sent
	 * @return	object  WP_REST_Response instance
	 */
	public function post_data( $data, $trigger )	{
		$hook = $this->get_hook( $trigger );

		$response = wp_remote_post(
			esc_url( $hook ),
			array(
				'headers' => array( 'content-type' => 'application/json' ),
				'body'    => json_encode( $data ),
			)
		);

        return absint( wp_remote_retrieve_response_code( $response ) );
	} // post_data

	/**
	 * Prepare rest response for new demo site
	 *
	 * @since	1.5.1
     * @param   array   $data   Array of data to be sent
	 * @return	object  WP_REST_Response instance
	 */
	public function send_response( $data )	{
        return rest_ensure_response( $data );
	} // send_response

    /**
     * Remove registered and deleted actions when a site is being reset.
     *
     * Stops triggers to Zapier for newly registered/deleted site that would ordinarily
     * happen with the functions used to reset a site.
     *
     * @since   1.5.1
     * @param   object  $reset  EPD_Reset_Site object
     * @return  void
     */
    public function remove_reset_actions( $reset )  {
        remove_action( 'epdp_zap_demo_registered', array( $this, 'process_triggers' ), 10, 2 );
        remove_action( 'epdp_zap_demo_deleted',    array( $this, 'process_triggers' ), 10, 2 );
    } // remove_reset_actions
} // EPDP_API
