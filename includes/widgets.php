<?php
/**
 * Widgets
 *
 * Widgets related funtions and widget registration.
 *
 * @package     EPD Premium
 * @subpackage  Widgets
 * @copyright   Copyright (c) 2020, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
|--------------------------------------------------------------------------
| FRONT-END WIDGETS
|--------------------------------------------------------------------------
|
| - Button widget
*/

/**
 * Button Widget.
 *
 * @since	1.2
 * @return	void
*/
class epdp_button_widget extends WP_Widget {

	/** Constructor */
	function __construct()	{

		parent::__construct(
			'epd_button_widget',
			__( 'EPD Demo Button', 'epd-premium' ),
			array( 'description' => __( 'Output a demo button', 'epd-premium' ) )
		);

	} // __construct

	/** @see WP_Widget::widget */
	function widget( $args, $instance )	{
		// Set defaults.
		$args['id']          = ( isset( $args['id'] ) )           ? $args['id']           : 'epd_button_widget';
		$instance['title']   = ( isset( $instance['title'] ) )    ? $instance['title']    : '';
		$instance['demo_id'] = ( isset( $instance['demo_id'] ) )  ? $instance['demo_id']  : '0';
		$instance['text']    = ( isset( $instance['text'] ) )     ? $instance['text']     : epdp_get_button_text();

		$title   = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		$demo_id = $instance['demo_id'];

		echo $args['before_widget'];

		if ( $title )	{
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'epd_before_button_widget' );

		echo epdp_output_demo_button( array(
			'demo_id' => $demo_id,
			'text'    => $instance['text']
		) );

		do_action( 'epd_after_button_widget' );

		echo $args['after_widget'];
	} // widget

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance )	{
		$instance            = $old_instance;
		$instance['title']   = strip_tags( $new_instance['title'] );
		$instance['text']    = strip_tags( $new_instance['text'] );
		$instance['demo_id'] = absint( $new_instance['demo_id'] );

		return $instance;
	} // update

	/** @see WP_Widget::form */
	function form( $instance )	{

		// Set up some default widget settings.
		$defaults = array(
			'title'   => '',
			'demo_id' => 0,
			'text'    => epdp_get_button_text()
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'epd-premium' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>"/>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'demo_id' ) ); ?>"><?php _e( 'Demo:', 'epd-premium' ); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'demo_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'demo_id' ) ); ?>" class="epdp_select_chosen">

				<?php $demos = get_posts( array(
					'post_type'      => 'epd_demo',
					'orderby'        => 'post_title',
					'order'          => 'ASC',
					'posts_per_page' => -1
				) ); ?>

				<?php foreach( $demos as $demo ) : ?>
					<?php printf(
						'<option value="%s"%s>%s</option>',
						$demo->ID,
						selected( $demo->ID, $instance['demo_id'], false ),
						get_the_title( $demo )
					); ?>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php _e( 'Button Text:', 'epd-premium' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" value="<?php echo $instance['text']; ?>"/>
		</p>

	<?php
	} // form

} // epdp_button_widget

/**
 * Register Widgets.
 *
 * Registers the EPD Widgets.
 *
 * @since	1.2
 * @return	void
 */
function epdp_register_widgets() {
	register_widget( 'epdp_button_widget' );
} // epdp_register_widgets
add_action( 'widgets_init', 'epdp_register_widgets' );
