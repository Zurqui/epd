<?php
/**
 * Integrations class
 *
 * @package     EPD Premium
 * @subpackage  Classes/Integrations
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * EPDP_Integrations Class
 *
 * This class handles the activation of integrations.
 *
 * @since	1.3
 */
class EPDP_Integrations {

	/**
	 * Integrations.
	 *
	 * @since	1.3
	 * @var		array
	 */
	public $integrations = array();

	/**
	 * Get things going
	 *
	 * @since	1.3
	 */
	public function __construct() {
		$this->integrations = array(
			'mailchimp' => __( 'MailChimp', 'epd-premium' ),
			'zapier'    => __( 'Zapier', 'epd-premium' )
		);

		$this->setup_integrations();
		$this->hooks();
	} // __construct

	/**
	 * Setup integrations.
	 *
	 * @since	1.3
	 */
	private function setup_integrations()	{
		foreach( $this->integrations as $id => $integration )	{
			$path = EPD_PREMIUM_DIR . "/includes/integrations/{$id}/class-epdp-{$id}-integration.php";

			if ( file_exists( $path ) )	{
				require_once $path;
				$class     = "EPDP_{$integration}";
				$this->$id = new $class;

				if ( $this->meets_requirements( $id ) )	{
					$this->$id->init();
				}
			}
		}
	} // setup_integrations

	/**
	 * Hooks.
	 *
	 * @since	1.3
	 */
	private function hooks()	{
		// Settings
		add_filter( 'epd_settings_tabs_before_licenses', array( $this, 'settings_tabs' ), 100 );
		add_filter( 'epd_settings_sections', array( $this, 'settings_sections' ), 100 );
		add_filter( 'epd_registered_settings', array( $this, 'settings' ) );
	} // function

	/**
	 * Add the Integrations settings tabs.
	 *
	 * @since	1.3
	 * @param	array	$tabs	Array of setting tabs
	 * @return	array	Array of setting tabs
	 */
	public function settings_tabs( $tabs )	{
		$tabs['integrations'] = __( 'Integrations', 'epd-premium' );

		return $tabs;
	} // settings_tabs

	/**
	 * Add the integration settings sections.
	 *
	 * @since	1.3
	 * @param	array	$sections	Array of setting tab sections
	 * @return	array	Array of setting tab sections
	 */
	public function settings_sections( $sections )	{
		foreach( $this->integrations as $id => $integration )	{
			$sections['integrations'][ $id ] = sprintf( __( '%s', 'epd-premium' ), $integration );
		}

		return $sections;
	} // settings_sections

	/**
	 * Add the mailchimp settings.
	 *
	 * Fires the filter to allow each integration to add their settings.
	 *
	 * @since	1.3
	 * @param	array	$settings	Array of settings
	 * @return	array	Array of settings
	 */
	public function settings( $settings )	{
		foreach( $this->integrations as $id => $integrations )	{
			$settings['integrations'][ $id ] = apply_filters( "epdp_{$id}_integration_settings", array() );
		}

		if ( empty( $settings['integrations'][ $id ] ) )	{
			$settings['integrations'][ $id ] = array(
				"{$id}_notice" => array(
					'id'   => "{$id}_notice",
					'name' => __( 'Upgrade Required', 'epd-premium' ),
					'type' => 'descriptive_text',
					'desc' => sprintf(
						__( '%1$s requires that Easy Plugin Demo is running at version %2$s or above. Please <a href="%3$s">updgrade</a> the core Easy Plugin Demo plugin. %1$s settings will then be displayed here.', 'epd-premium' ),
						$this->$id->__get( 'integration' ),
						$this->$id->__get( 'required_epd' ),
						network_admin_url( 'plugins.php' )
					)
				)
			);
		}

		return $settings;
	} // settings

	/**
	 * Whether or not the environment meets the requirements for the integration.
	 *
	 * @since	1.3
	 * @param	string	$integration	The integration
	 * @return	bool	True if the requirements are met, otherwise false
	 */
	public function meets_requirements( $integration )	{
		if ( ! array_key_exists( $integration, $this->integrations ) )	{
			return false;
		}

		$class = "EPD_{$this->integrations[ $integration ]}";
		
		if ( version_compare( $this->$integration->__get( 'required_epd' ), EPD_VERSION, '>' ) )	{
			return false;
		}

		return true;
	} // meets_requirements

} // EPDP_Integrations
