<?php
/**
 * Add Calendar Meta Box
 *
 * @package SimpleCalendar/Admin
 */
namespace SimpleCalendar\Admin\Metaboxes;

use SimpleCalendar\Abstracts\Meta_Box;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attach a calendar to a post.
 *
 * Meta box for attaching calendars to WordPress posts.
 */
class Attach_Calendar implements Meta_Box {

	/**
	 * Output the meta box markup.
	 *
	 * @param \WP_Post $post
	 */
	public static function html( $post ) {

		// @see Meta_Boxes::save_meta_boxes()
		wp_nonce_field( 'simcal_save_data', 'simcal_meta_nonce' );

		simcal_print_field( array(
			'type'       => 'select',
			'id'         => '_simcal_attach_calendar_id',
			'name'       => '_simcal_attach_calendar_id',
			'context'    => 'metabox',
			'enhanced'   => 'enhanced',
			'allow_void' => 'allow_void',
			'value'      => absint( get_post_meta( $post->ID, '_simcal_attach_calendar_id', true ) ),
			'options'    => simcal_get_calendars(),
			'attributes' => array(
				'data-allowclear' => 'true'
			)
		) );

		$position = get_post_meta( $post->ID, '_simcal_attach_calendar_position', true );

		simcal_print_field( array(
			'type'      => 'radio',
			'id'        => '_simcal_attach_calendar_position',
			'name'      => '_simcal_attach_calendar_position',
			'context'   => 'metabox',
			'value'     => $position ? $position : 'after',
			'options'   => array(
				'after'  => __( 'After content', 'google-calendar-events' ),
				'before' => __( 'Before content', 'google-calendar-events' )
			),
		) );

	}

	/**
	 * Validate and save the meta box fields.
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public static function save( $post_id, $post ) {

		$id = isset( $_POST['_simcal_attach_calendar_id'] ) ? absint( $_POST['_simcal_attach_calendar_id'] ) : '';
		update_post_meta( $post_id, '_simcal_attach_calendar_id', $id );

		$position = isset( $_POST['_simcal_attach_calendar_position'] ) ? sanitize_title( $_POST['_simcal_attach_calendar_position'] ) : 'after';
		update_post_meta( $post_id, '_simcal_attach_calendar_position', $position );

	}

}
