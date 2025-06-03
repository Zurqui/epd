<?php
/**
 * Zapier Integration
 *
 * @package     EPD Premium
 * @subpackage  Integrations/Zapier/Classes
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * EPDP_Zapier Class
 *
 * @since	1.5.1
 */
if ( ! class_exists( 'EPDP_Zapier' ) )	{
	class EPDP_Zapier	{

		/**
		 * The integration ID.
		 *
		 * @since	1.5.1
		 * @var		string	$id		ID of the integration
		 */
		private $id = 'zapier';

		/**
		 * The integration name.
		 *
		 * @since	1.5.1
		 * @var		string	$integration		Name of the integration
		 */
		private $integration = '';

		/**
		 * The version of the Zapier integration.
		 *
		 * @since	1.5.1
		 * @var		string	$version		Version of the Zapier integration
		 */
		private $version = '1.0';

		/**
         * @var		int		$required_epd	The minimum required Easy Plugin Demo version
         * @since	1.5.1
         */
		private $required_epd = '1.3.9';

        /**
         * @var		bool	$enabled	Whether the integration is enabled
         * @since	1.5.1
         */
		private $enabled = false;

		/**
		 * Get things going
		 *
		 * @since	1.5.1
		 */
		public function __construct()	{
			$this->integration = __( 'Zapier Integration', 'epd-premium' );
		} // __construct

		/**
		 * Magic GET function.
		 *
		 * @since	1.5.1
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
		 * @since	1.5.1
		 */
		public function init()	{
            $this->enabled = epd_get_option( 'enable_zapier' );
			$this->includes();
			$this->hooks();

            if ( $this->enabled )   {
                new EPDP_Zapier_API;
            }
		} // __construct

		/**
		 * Includes.
		 *
		 * @since	1.5.1
		 */
		public function includes()	{
            if ( $this->enabled )   {
                require_once EPD_PREMIUM_DIR . '/includes/integrations/zapier/class-epdp-zapier-api.php';
                require_once EPD_PREMIUM_DIR . '/includes/integrations/zapier/zapier-actions.php';
            }
        } // includes

		/**
		 * Hooks.
		 *
		 * @since	1.5.1
		 */
		public function hooks()	{
            // Settings
			add_filter( 'epdp_zapier_integration_settings', array( $this, 'settings' ), 100 );

            if ( $this->enabled )   {
                // Post type
                add_action( 'init', array( $this, 'register_post_type' ), 1 );

				// Settings
				add_action( 'epd_settings_tab_bottom_integrations_zapier', array( $this, 'test_buttons' ) );
            }
        } // hooks

        /**
         * Register the EPD Zapier post type.
         *
         * @since   1.5.1
         * @return  void
         */
        public function register_post_type()    {
            if ( ! is_main_site() ) {
                return;
            }

            register_post_type(
                'epd_zap'
            );
        } // register_post_type

        /**
		 * Add the Zapier settings.
		 *
		 * @since	1.5.1
		 * @param	array	$settings	Array of settings
		 * @return	array	Array of settings
		 */
		function settings( $settings )	{
			$settings = array(
				'enable_zapier' => array(
					'id'   => 'enable_zapier',
					'name' => __( 'Enable Zapier', 'epd-premium' ),
					'type' => 'checkbox',
					'std'  => 0
				)
			);

			return $settings;
		} // settings

		/**
		 * Output the Zapier test buttons.
		 *
		 * @since	1.5.1
		 * @return	string	Test buttons
		 */
		function test_buttons()	{
			$test_url = add_query_arg( array(
				'page'       => 'epd-settings',
				'tab'        => 'integrations',
				'section'    => 'zapier',
				'epd_action' => 'zap_test'
			), network_admin_url( 'settings.php' ) );

			ob_start(); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">&nbsp;</th>
						<td id="epdp-zap-register-test">
							<?php printf(
								'<a id="epdp-zap-register-test-button" href="%s" class="button epd-button">%s</a>',
								esc_url( wp_nonce_url(
									add_query_arg(
										'trigger', 'registered', $test_url
									), 'test_zap', 'epd_nonce'
								) ),
								__( 'Send Demo Registered Notification', 'epd-premium' )
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">&nbsp;</th>
						<td id="epdp-zap-deleted-test">
							<?php printf(
								'<a id="epdp-zap-deleted-test-button" href="%s" class="button epd-button">%s</a>',
								esc_url( wp_nonce_url(
									add_query_arg(
										'trigger', 'deleted', $test_url
									), 'test_zap', 'epd_nonce'
								) ),
								__( 'Send Demo Deleted Notification', 'epd-premium' )
							); ?>
						</td>
					</tr>
                    <tr>
						<th scope="row">&nbsp;</th>
						<td id="epdp-zap-reset-test">
							<?php printf(
								'<a id="epdp-zap-reset-test-button" href="%s" class="button epd-button">%s</a>',
								esc_url( wp_nonce_url(
									add_query_arg(
										'trigger', 'reset', $test_url
									), 'test_zap', 'epd_nonce'
								) ),
								__( 'Send Demo Reset Notification', 'epd-premium' )
							); ?>
						</td>
					</tr>
				</tbody>
			</table>

			<?php echo ob_get_clean();
		} // test_buttons
	} // EPDP_Zapier
}
