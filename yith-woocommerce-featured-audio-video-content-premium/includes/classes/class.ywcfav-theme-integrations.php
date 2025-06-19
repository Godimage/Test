<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH\WooCommerceFeaturedAudioandVideoContent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_FAV_Load_Themes_Integration' ) ) {

	/**
	 * YITH_FAV_Load_Themes_Integration
	 */
	class YITH_FAV_Load_Themes_Integration {
		/**
		 * Single instance of the class
		 *
		 * @var YITH_FAV_Load_Themes_Integration $instance
		 */
		protected static $instance;

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {

			$theme_name = wp_get_theme()->get( 'Name' );

			if ( strpos( strtolower( $theme_name ), 'flatsome' ) !== false || class_exists( 'Flatsome_Default' ) ) {

				require_once 'modules/class.yith-fav-flatsome-module.php';
			}
		}

		/**
		 * Get_instance
		 *
		 * @return YITH_FAV_Load_Themes_Integration
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
	}
}

/**
 * YITH_FAV_Load_Themes_Integration
 *
 * @return instance
 */
function YITH_FAV_Load_Themes_Integration() { // phpcs:ignore WordPress.NamingConventions
	return YITH_FAV_Load_Themes_Integration::get_instance();
}
