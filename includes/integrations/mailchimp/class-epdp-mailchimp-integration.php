<?php
/**
 * MailChimp Integration
 *
 * @package     EPD Premium
 * @subpackage  Integrations/MailChimp/Classes
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'EPDP_MailChimp' ) )	{
	class EPDP_MailChimp	{

		/**
		 * The integration ID.
		 *
		 * @since	1.3
		 * @var		string	$id		ID of the integration
		 */
		private $id = 'mailchimp';

		/**
		 * The integration name.
		 *
		 * @since	1.3
		 * @var		string	$integration		Name of the integration
		 */
		private $integration = '';

		/**
		 * The version of the MailChimp integration.
		 *
		 * @since	1.3
		 * @var		string	$version		Version of the MailChimp integration
		 */
		private $version = '1.0';

		/**
         * @var		int		$required_epd	The minimum required Easy Plugin Demo version
         * @since	1.0
         */
		private $required_epd = '1.3.3';

		/**
		 * Get things started.
		 *
		 * @since	1.3
		 */
		public function __construct()	{
			$this->integration = __( 'MailChimp Integration', 'epd-premium' );
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
		 * Initialise.
		 *
		 * @since	1.3
		 */
		public function init()	{
			$this->includes();
			$this->hooks();
		} // __construct

		/**
		 * Includes.
		 *
		 * @since	1.3
		 */
		public function includes()	{
            require_once EPD_PREMIUM_DIR . '/includes/integrations/mailchimp/mailchimp-functions.php';
            require_once EPD_PREMIUM_DIR . '/includes/integrations/mailchimp/mailchimp-actions.php';
            require_once EPD_PREMIUM_DIR . '/includes/libraries/MailChimp/MailChimp.php';
		} // includes

		/**
		 * Hooks.
		 *
		 * @since	1.3
		 */
		public function hooks()	{
			// Settings
			add_filter( 'epdp_mailchimp_integration_settings', array( $this, 'settings' ), 100 );
		} // hooks

		/**
		 * Add the MailChimp settings.
		 *
		 * @since	1.3
		 * @param	array	$settings	Array of settings
		 * @return	array	Array of settings
		 */
		function settings( $settings )	{
			$settings = array(
				'mailchimp_api' => array(
					'id'      => 'mailchimp_api',
					'name'    => __( 'MailChimp API Key', 'epd-premium' ),
					'type'    => 'text',
					'desc'    => sprintf(
						__( 'Enter your API Key. <a href="%s" target="_blank">Get your API key</a>.', 'epd-premium' ),
						'http://admin.mailchimp.com/account/api-key-popup'
					)
				),
				'mailchimp_signup' => array(
					'id'      => 'mailchimp_signup',
					'name'    => __( 'Show Signup On Register', 'epd-premium' ),
					'type'    => 'checkbox',
					'desc'    => __( 'Enable to display the signup field on the registration field.', 'epd-premium' )
				),
				'mailchimp_signup_label' => array(
					'id'      => 'mailchimp_signup_label',
					'name'    => __( 'Signup Label', 'epd-premium' ),
					'type'    => 'text',
					'std'     => __( 'Signup for the newsletter', 'epd-premium' ),
					'desc'    => __( 'Enter a label for the newsletter signup checkbox.', 'epd-premium' )
				),
				'mailchimp_list' => array(
					'id'      => 'mailchimp_list',
					'name'    => __( 'List', 'epd-premium' ),
					'desc'    => sprintf(
						__( "If your list doesn't appear, try a <a href=%s>refresh</a>.", 'epd-premium' ),
						add_query_arg( 'epd_action', 'refresh_mailchimp_lists' )
					),
					'type'    => 'select',
					'options' => $this->list_options()
				),
				'mailchimp_double_opt_in' => array(
					'id'      => 'mailchimp_double_opt_in',
					'name'    => __( 'Double Opt-In', 'epd-premium' ),
					'type'    => 'checkbox',
					'desc'    => __( 'Enable if you would like customers to receive a verification email before being subscribed to your mailing list.', 'epd-premium' )
				)
			);

			return $settings;
		} // settings

		/**
		 * Retrieve list options for settings.
		 *
		 * @since   1.3
		 * @return  array   Array of list options
		 */
		function list_options()  {
			$options = array();

			if ( ! epdp_mailchimp_is_connected() )  {
				$options[0] = __( 'Not connected to MailChimp', 'epd-premium' );
			} else  {
				$placeholder = array( 0 => __( 'Select a List', 'epd-premium' ) );
				$lists   = epdp_mailchimp_get_lists();

				foreach( $lists as $id => $list )	{
					$options[ $id ] = $lists[ $id ]['name'];
				}

				$options = $placeholder + $options;
			}

			return $options;
		} // list_options

	} // EPDP_MailChimp
} // if ( ! class_exists( 'EPDP_MailChimp' ) )
