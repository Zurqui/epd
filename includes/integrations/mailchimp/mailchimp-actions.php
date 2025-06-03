<?php
/**
 * MailChimp Integration Actions
 *
 * @package     EPD Premium
 * @subpackage  Integrations/Functions
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Adds the signup field to the registration form.
 *
 * @since   1.3
 * @return  void
 */
function epdp_mailchimp_add_signup_field_action()   {
    if ( ! epdp_mailchimp_display_signup_field() || ! epdp_mailchimp_get_api_key() )  {
        return;
    }

    $label = epdp_mailchimp_get_signup_label();

    ob_start(); ?>

    <p>
        <input type="checkbox" name="epd_mailchimp_signup" id="epd-mailchimp-signup" value="1" />
        <label for="epd-mailchimp-signup"><?php echo $label; ?></label>
    </p>

    <?php
    $output = ob_get_clean();
    $output = apply_filters( 'epdp_mailchimp_signup_field', $output );

    echo $output;
} // epdp_mailchimp_add_signup_field_action
add_action( 'epd_register_form_fields_before_submit', 'epdp_mailchimp_add_signup_field_action' );

/**
 * Add mailchimp data to API request.
 *
 * @since   1.5
 * @param   array   $data   Array of data in API request
 * @param   array   $params Array of params received in API request
 * @return  array   Array of data in API request
 */
function epdp_mailchimp_add_api_field_action( $data, $params )  {
    if ( ! empty( $params['epd_mailchimp_signup'] ) ) {
        $data['mailchimp_signup'] = true;
    }
 
    return $data;
} // epdp_mailchimp_add_api_field_action
add_filter( 'epdp_api_response', 'epdp_mailchimp_add_api_field_action', 10, 2 );

/**
 * Process newsletter signup after registration.
 *
 * @since	1.3
 * @param	int		$site_id	The new site ID
 * @param	int		$user_id	The ID of the site user
 * @param	array	$data		Form data submitted with registration
 * @return	void
 */
function epdp_mailchimp_registration_signup_action( $site_id, $user_id, $data )	{
	if ( ! epdp_mailchimp_get_api_key() )	{
		return;
	}

	if ( empty( $data['mailchimp_signup'] ) || empty( $data['email'] ) )	{
		return;
	}

	$user = get_userdata( $user_id );

	if ( ! $user )	{
		return;
	}

	$mailchimp = epdp_mailchimp_connect();
	$merge     = array();
	$args      = array(
		'email_address' => $user->user_email
	);

	if ( ! empty( $user->first_name ) )	{
		$merge['FNAME'] = $user->first_name;
	}

	if ( ! empty( $user->first_name ) )	{
		$merge['LNAME'] = $user->last_name;
	}

	if ( ! empty( $merge ) )	{
		$args['merge_fields'] = $merge;
	}

	epdp_mailchimp_subscribe_user( $mailchimp, $args );
} // epdp_mailchimp_registration_signup_action
add_action( 'epd_registration', 'epdp_mailchimp_registration_signup_action', 10, 3 );

/**
 * Force a refresh of the MailChimp lists.
 *
 * @since	1.3
 * @return	void
 */
function epdp_mailchimp_refresh_lists_action()	{
	if ( ! isset( $_GET['epd_action'] ) || 'refresh_mailchimp_lists' != $_GET['epd_action'] )	{
		return;
	}

	epdp_mailchimp_get_lists( true );

	wp_safe_redirect(
		remove_query_arg( 'epd_action' )
	);

	exit;
} // epdp_mailchimp_refresh_lists_action
add_action( 'admin_init', 'epdp_mailchimp_refresh_lists_action' );

/**
 * Adds the MailChimp info to the right now dashboard.
 *
 * @since	1.3
 * @return	void
 */
function epdp_mailchimp_right_now_dashboard_action()	{
	$default_list = epdp_get_default_list_id();

	if ( ! $default_list )	{
		return;
	}

	$lists = epdp_mailchimp_get_lists();

	if ( ! array_key_exists( $default_list, $lists ) )	{
		return;
	}

	$member_count = $lists[ $default_list ]['stats']['member_count'];
	$member_count = sprintf(
		_n( '%s member', '%s members', $member_count, 'epd-premium' ),
		number_format_i18n( $member_count )
	);

	$output = sprintf( __( 'Connected to the <strong>%s</strong> MailChimp list. %s.', 'epd-premium' ),
		$lists[ $default_list ]['name'],
		$member_count
	);

	?>
	<p class="youhave"><?php echo $output; ?></p>
	<?php

} // epdp_mailchimp_right_now_dashboard_action
add_action( 'epd_right_now_dashboard', 'epdp_mailchimp_right_now_dashboard_action' );
