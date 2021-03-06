<?php
/**
 * Update Plugin
 *
 * @package SimpleCalendar/Updates
 */
namespace SimpleCalendar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update script.
 *
 * Updates the installed plugin to the current version.
 */
class Update {

	/**
	 * Previous version.
	 *
	 * @access protected
	 * @var string
	 */
	private $installed_ver = '0.0.0';

	/**
	 * Current version.
	 *
	 * @access private
	 * @var string
	 */
	private $new_ver = '0.0.0';

	/**
	 * Existing posts.
	 *
	 * @access private
	 * @var array
	 */
	private $posts = array();

	/**
	 * Update path.
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $update_path = array(
		'2.1.0',
		'2.2.0',
		'3.0.0',
	);

	/**
	 * Constructor.
	 *
	 * @param string $version (optional) Current plugin version, defaults to value in plugin constant.
	 */
	public function __construct( $version = SIMPLE_CALENDAR_VERSION ) {
		// Look for previous version in current or legacy option, null for fresh install.
		$installed = get_option( 'simple-calendar_version', null );
		$this->installed_ver = is_null( $installed ) ? get_option( 'gce_version', null ) : $installed;
		$this->new_ver = $version;
	}

	/**
	 * Update to current version.
	 *
	 * Runs all the update scripts through version steps.
	 */
	public function run_updates() {

		do_action( 'simcal_before_update', $this->installed_ver );

		if ( ! is_null( $this->installed_ver ) ) {

			if ( version_compare( $this->installed_ver, $this->new_ver ) === -1 ) {

				$post_type = version_compare( $this->installed_ver, '3.0.0' ) === -1 ? 'gce_feed' : 'calendar';
				$this->posts = $this->get_posts( $post_type );

				foreach ( $this->update_path as $update_to ) {
					if ( version_compare( $this->installed_ver, $update_to, '<' ) ) {
						$this->update( $update_to );
					}
				}

			}

			simcal_delete_feed_transients();
		}

		do_action( 'simcal_updated', $this->new_ver );

		// Redirect to a welcome page if new install or major update.
		if ( is_null( $this->installed_ver ) ) {
			set_transient( '_simple-calendar_activation_redirect', 'fresh', 60 );
		} else {
			$major_new = substr( $this->new_ver, 0, strrpos( $this->new_ver, '.' ) );
			$major_old = substr( $this->installed_ver, 0, strrpos( $this->installed_ver, '.' ) );
			if ( version_compare( $major_new, $major_old, '>' ) ) {
				set_transient( '_simple-calendar_activation_redirect', 'update', 60 );
			}
		}

		update_option( 'simple-calendar_version', $this->new_ver );
	}

	/**
	 * Get posts.
	 *
	 * @param  $post_type
	 *
	 * @return array
	 */
	private function get_posts( $post_type ) {

		$posts = array();

		if ( ! empty( $post_type ) ) {

			// https://core.trac.wordpress.org/ticket/18408
			$posts = get_posts( array(
				'post_type'   => $post_type,
				'post_status' => array(
					'draft',
					'future',
					'publish',
					'pending',
					'private',
					'trash',
				),
				'nopaging'    => true,
			) );

			wp_reset_postdata();
		}

		return $posts;
	}

	/**
	 * Update.
	 *
	 * Runs an update script for the specified version passed in argument.
	 *
	 * @param string $version
	 */
	private function update( $version ) {

		$update_v = '\\' . __NAMESPACE__ . '\Updates\\Update_V' . str_replace( '.', '', $version );

		if ( class_exists( $update_v ) ) {
			new $update_v( $this->posts );
		}
	}

}
