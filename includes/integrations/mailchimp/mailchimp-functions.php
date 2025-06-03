<?php
/**
 * MailChimp Integration
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

use \DrewM\MailChimp\MailChimp;

/**
 * Retrieve the API key.
 *
 * @since   1.3
 * @return  string|false    The MailChimp API key or false if empty
 */
function epdp_mailchimp_get_api_key()   {
    return epd_get_option( 'mailchimp_api', false );
} // epdp_mailchimp_get_api_key

/**
 * Whether or not to display the newsletter signup field..
 *
 * @since   1.3
 * @return  bool    True if the field should be displayed
 */
function epdp_mailchimp_display_signup_field()   {
    $display = epd_get_option( 'mailchimp_signup' );
    $display = apply_filters( 'epdp_mailchimp_display_signup_field', $display );

    return $display;
} // epdp_mailchimp_display_signup_field

/**
 * Retrieve the API key.
 *
 * @since   1.3
 * @return  string  The label for the signup field
 */
function epdp_mailchimp_get_signup_label()   {
    $label = epd_get_option( 'mailchimp_signup_label', __( 'Signup for the newsletter', 'epd-premium' ) );
    $label = apply_filters( 'epdp_mailchimp_signup_label', $label );

    return $label;
} // epdp_mailchimp_get_signup_label

/**
 * Retrieve the default list ID.
 *
 * @since	1.3
 * @return	string		The default list ID.
 */
function epdp_get_default_list_id()	{
	return epd_get_option( 'mailchimp_list', false );
} // epdp_get_default_list_id

/**
 * Whether or not double opt-in is required.
 *
 * @since   1.3
 * @return  bool    True if double opt-in is required
 */
function epdp_mailchimp_require_double_opt_in()   {
    return epd_get_option( 'mailchimp_double_opt_in', false );
} // epdp_mailchimp_require_double_opt_in

/**
 * Connect to MailChimp.
 *
 * @since	1.3
 * @return	object   The MailChimp API Class Object or false on failure.
 */
function epdp_mailchimp_connect()	{
	$api_key = epdp_mailchimp_get_api_key();

	if ( ! $api_key )	{
		return false;
	}

	try	{
		$mailchimp = new MailChimp( $api_key );
	} catch( Exception $error )	{
        error_log( $error->getMessage() );
		return false;
	}

	return $mailchimp;
} // epdp_mailchimp_connect

/**
 * Whether or not we have a connection to MailChimp.
 *
 * @since	1.3
 * @return	bool	True if connected, otherwise false.
 */
function epdp_mailchimp_is_connected()	{
	if ( epdp_mailchimp_connect() )	{
		return true;
	}

	return false;
} // epdp_mailchimp_is_connected

/**
 * Retrieve existing MailChimp Lists.
 *
 * @since	1.3
 * @param   bool    $force  Whether or not to force a list refresh
 * @return	array	Retrieve MailChimp Lists
 */
function epdp_mailchimp_get_lists( $force = false )	{
    switch_to_blog( get_network()->blog_id );

	$mailchimp_lists = unserialize( get_site_transient( 'epdp_mailchimp_mailinglists' ) );

	if ( false === $mailchimp_lists || $force )	{
		$mailchimp_lists = array();
		$mailchimp       = epdp_mailchimp_connect();

		if ( ! $mailchimp )	{
			return $mailchimp_lists[0] = __( 'Unable to load MailChimp lists, check your API Key.', 'epd-premium' );
		}

		$lists = $mailchimp->get( 'lists' );

		if ( ! $mailchimp->success() ) {
			$mailchimp_lists[0] = __( 'Unable to load MailChimp lists, check your API Key.', 'epd-premium' );
		} else {

			if ( ! $lists ) {
				$mailchimp_lists[0] = __( 'You have not created any lists at MailChimp', 'epd-premium' );
				return $mailchimp_lists;
			}

			foreach ( $lists['lists'] as $list )	{
				$mailchimp_lists[ $list['id'] ] = $list;
			}

			set_site_transient( 'epdp_mailchimp_mailinglists', serialize( $mailchimp_lists ), 3600 );
		}
	}

    restore_current_blog();

	return $mailchimp_lists;
} // epdp_mailchimp_get_lists

/**
 * Subscribe a user to a list.
 *
 * @since	1.3
 * @param	object	$mailchimp	MailChimp API Class Object
 * @param	array	$data	Subscriber data.
 * @return	bool	True if successful, or false
 */
function epdp_mailchimp_subscribe_user( $mailchimp = false, $data )	{
	if ( empty( $data['email_address'] ) )	{
		return false;
	}

	if ( empty( $mailchimp ) )	{
		$mailchimp = epdp_mailchimp_connect();
	}

	if ( ! $mailchimp )	{
		return false;
	}

	$list_id        = epdp_get_default_list_id();
	$double_opt_in  = epdp_mailchimp_require_double_opt_in() ? 'pending' : 'subscribed';
	$subscribed     = false;

	$data['status'] = $double_opt_in;

	if ( ! epdp_mailchimp_user_is_subscribed( $mailchimp, $list_id, $data['email_address'] ) )	{
		$subscribed = epdp_mailchimp_add_user_to_list( $mailchimp, $list_id, $data );
	} else	{
		$subscribed = epdp_mailchimp_update_user_in_list( $mailchimp, $list_id, $data );
	}

	do_action( 'epdp_mailchimp_subscribe_user', $data, $list_id, $subscribed );

	return $subscribed;
} // epdp_mailchimp_subscribe_user

/**
 * Checks if a user is subscribed to a list.
 *
 * @since	1.3
 * @param	object|false	$MailChimp	MailChimp API Class Object
 * @param	string			$list_id	The list to check
 * @param	string			$email		Users email address
 * @return	bool			True if successful, or false
 */
function epdp_mailchimp_user_is_subscribed( $mailchimp = false, $list_id = '', $email )	{
	if ( empty( $mailchimp ) )	{
		$mailchimp = epdp_mailchimp_connect();
	}

	if ( ! $mailchimp )	{
		return false;
	}

	if ( empty( $list_id ) )	{
		$list_id = epdp_get_default_list_id();
	}

	$email = $mailchimp->subscriberHash( $email );

	$subscriber_data = $mailchimp->get(
		"lists/$list_id/members/$email",
		array( 'fields' => array( 'status' ) )
	);

	if ( ! empty( $subscriber_data['status'] ) && 'subscribed' == $subscriber_data['status'] )	{
		return true;
	}

	return false;
} // epdp_mailchimp_user_is_subscribed

/**
 * Add a user to a list.
 *
 * @since	1.3
 * @param	object|false	$mailchimp	MailChimp API Class Object
 * @param	string			$list_id	The ID of the list to subscribe to
 * @param	array			$data		Subscriber data
 * @return	bool			True if successful, or false
 */
function epdp_mailchimp_add_user_to_list( $mailchimp = false, $list_id, $data )	{
	if ( empty( $data['email_address'] ) )	{
		return false;
	}

	if ( empty( $mailchimp ) )	{
		$mailchimp = epdp_mailchimp_connect();
	}

	if ( ! $mailchimp )	{
		return false;
	}

	$subscribed = $mailchimp->post( "/lists/$list_id/members", $data );

	return $mailchimp->success();
} // epdp_mailchimp_add_user_to_list

/**
 * Update a user within a list.
 *
 * @since	1.3
 * @param	object|false	$mailchimp	MailChimp API Class Object
 * @param	string			$list_id	The list to subscribe to
 * @param	array			$data		Subscriber data.
 * @return	bool			True if successful, or false
 */
function epdp_mailchimp_update_user_in_list( $mailchimp = false, $list_id, $data )	{
	if ( empty( $data['email_address'] ) )	{
		return false;
	}

	if ( empty( $mailchimp ) )	{
		$mailchimp = epdp_mailchimp_connect();
	}

	if ( ! $mailchimp )	{
		return false;
	}

	$email      = $mailchimp->subscriberHash( $data['email_address'] );
	$subscribed = $mailchimp->put( "/lists/$list_id/members/$email", $data );

	return $mailchimp->success();
} // epdp_mailchimp_update_user_in_list
