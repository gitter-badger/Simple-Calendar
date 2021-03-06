<?php
/**
 * Installation
 *
 * @package SimpleCalendar
 */
namespace SimpleCalendar;

use SimpleCalendar\Admin\Settings_Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installation.
 *
 * Static class that deals with plugin activation and deactivation events.
 */
class Installation {

	/**
	 * What happens when the plugin is activated.
	 */
	public static function activate() {

		include_once 'functions/shared.php';
		include_once 'functions/admin.php';

		self::create_terms();
		self::create_options();

		self::update( SIMPLE_CALENDAR_VERSION );

		flush_rewrite_rules();

		do_action( 'simcal_activated' );
	}

	/**
	 * What happens when the plugin is deactivated.
	 */
	public static function deactivate() {

		flush_rewrite_rules();

		do_action( 'simcal_deactivated' );
	}

	/**
	 * Create default terms.
	 */
	public static function create_terms() {

		$taxonomies = array(
			'calendar_feed' => array(
				'google',
				'grouped-calendar',
			),
			'calendar_type' => array(
				'default-calendar'
			)
		);

		foreach ( $taxonomies as $taxonomy => $terms ) {
			foreach ( $terms as $term ) {
				if ( ! get_term_by( 'slug', sanitize_title( $term ), $taxonomy ) ) {
					wp_insert_term( $term, $taxonomy );
				}
			}
		}

	}

	/**
	 * Sets the default options.
	 */
	public static function create_options() {

		$default = '';
		$page    = 'settings';
		$settings_pages  = new Settings_Pages( $page );
		$plugin_settings = $settings_pages->get_settings();

		if ( $plugin_settings && is_array( $plugin_settings ) ) {

			foreach ( $plugin_settings as $id => $settings ) {

				$group = 'simple-calendar_' . $page . '_' . $id;

				if ( isset( $settings['sections'] ) ) {

					if ( $settings['sections'] && is_array( $settings['sections'] ) ) {

						foreach ( $settings['sections'] as $section_id => $section ) {

							if ( isset( $section['fields'] ) ) {

								if ( $section['fields'] && is_array( $section['fields'] ) ) {

									foreach ( $section['fields'] as $key => $field ) {

										if ( isset ( $field['type'] ) ) {
											// Maybe an associative array.
											if ( is_int( $key ) ) {
												$default[ $section_id ] = self::get_field_default_value( $field );
											} else {
												$default[ $section_id ][ $key ] = self::get_field_default_value( $field );
											}
										}

									} // Loop fields.

								} // Are fields non empty?

							} // Are there fields?

						} // Loop fields sections.

					} // Are sections non empty?

				} // Are there sections?

				add_option( $group, $default, '', true );

				// Reset before looping next settings page.
				$default = '';
			}

		}
	}

	/**
	 * Get field default value.
	 *
	 * Helper function to set the default value of a field.
	 *
	 * @param  $field
	 *
	 * @return mixed
	 */
	private static function get_field_default_value( $field ) {

		$saved_value   = isset( $field['value'] )   ? $field['value']   : '';
		$default_value = isset( $field['default'] ) ? $field['default'] : '';

		return ! empty( $saved_value ) ? $saved_value : $default_value;
	}

	/**
	 * Run upgrade scripts.
	 *
	 * @param string $version
	 */
	public static function update( $version ) {
		$update = new Update( $version );
		$update->run_updates();
	}

}
