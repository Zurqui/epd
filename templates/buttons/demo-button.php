<?php
/**
 * This is the template for the demo button.
 *
 * @since	1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

global $epdp_button_options;

extract( $epdp_button_options );

$class = ! empty( $class ) ? ' ' . $class : ''; ?>

<?php if ( ! empty( $demo_key ) ) : ?>

	<form class="epdp_demo_form epdp_demo_<?php echo $demo_key; ?>" method="post">
		<?php do_action( 'epdp_demo_button_top', $demo_id ); ?>

		<input type="submit" class="epdp_launch_demo<?php echo esc_attr( $class ); ?>" name="epdp_launch_demo" value="<?php echo esc_attr( $text ); ?>" data-action="epdp_launch_demo" data-demo-ref="<?php echo esc_attr( $demo_key ); ?>" />

		<?php do_action( 'epdp_demo_button_bottom', $demo_id ); ?>
	</form>

<?php else : ?>

	<span class="epd_no_demo"><?php _e( 'No demo found', 'epd-premium' ); ?></span>

<?php endif; ?>
