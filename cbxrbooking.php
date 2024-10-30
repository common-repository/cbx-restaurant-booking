<?php
/**
 *
 * @link              http://codeboxr.com
 * @since             1.0.0
 * @package           CBXRBooking
 *
 * @wordpress-plugin
 * Plugin Name:       CBX Restaurant Booking
 * Plugin URI:        https://codeboxr.com/product/cbx-restaurant-booking-for-wordpress/
 * Description:       Online restaurant bookings and reservations with branch manager
 * Version:           1.2.1
 * Author:            Codeboxr
 * Author URI:        http://codeboxr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cbxrbooking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

defined('CBXRBOOKING_PLUGIN_NAME') or define('CBXRBOOKING_PLUGIN_NAME', 'cbxrbooking');
defined('CBXRBOOKING_BASE_NAME') or define('CBXRBOOKING_BASE_NAME', plugin_basename(__FILE__));
defined('CBXRBOOKING_PLUGIN_VERSION') or define('CBXRBOOKING_PLUGIN_VERSION', '1.2.1');
defined('CBXRBOOKING_ROOT_PATH') or define('CBXRBOOKING_ROOT_PATH', plugin_dir_path(__FILE__));
defined('CBXRBOOKING_ROOT_URL') or define('CBXRBOOKING_ROOT_URL', plugin_dir_url(__FILE__));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cbxrbooking-activator.php
 */
function activate_cbxrbooking(){
	//need to check if any specific plugin is activate to make the plugin compatible for it.
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	/**
	 * Check if  CBX Restaurant Booking Frontend plugin is active
	 * */
	if ( in_array( 'cbxrbookingfrontendlogaddon/cbxrbookingfrontendlogaddon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		//core plugin activated, trying to activate the addon
		$plugin_version = CBXRBOOKINGFRONTENDLOGADDON_PLUGIN_VERSION;
		$version_required = '1.1.6';


		if(version_compare($plugin_version, $version_required, '<') ){
			deactivate_plugins( 'cbxrbookingfrontendlogaddon/cbxrbookingfrontendlogaddon.php' );
			//echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__( 'CBX Restaurant Booking Frontend Addon V%s or any previous version is not compatible with CBX Restaurant Booking V%s or later. Please update CBX Restaurant Booking Frontend Addon to version V%s or later  - Codeboxr Team', 'cbxrbooking' ), $plugin_version, CBXRBOOKING_PLUGIN_VERSION, $version_required) . '</p></div>';

			// Throw an error in the wordpress admin console
			//$error_message = '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__( 'CBX Restaurant Booking Frontend Addon V%s or any previous version is not compatible with CBX Restaurant Booking V%s or later. Please update CBX Restaurant Booking Frontend Addon to version V%s or later  - Codeboxr Team', 'cbxrbooking' ), $plugin_version, CBXRBOOKING_PLUGIN_VERSION, $version_required) . '</p></div>';
			//die( $error_message );

			set_transient( 'cbxrbooking_frontendaddon_deactivate_notice', 1 );
		}
	}

	/**
	 * Check if  CBX Restaurant Booking Pro plugin is active
	 * */
	if ( in_array( 'cbxrbookingproaddon/cbxrbookingproaddon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		//core plugin activated, trying to activate the addon
		$plugin_version = CBXRBOOKINGPROADDON_PLUGIN_VERSION;
		$version_required = '1.0.12';


		if(version_compare($plugin_version, $version_required, '<') ){
			deactivate_plugins( 'cbxrbookingproaddon/cbxrbookingproaddon.php' );
			//echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__( 'CBX Restaurant Booking Pro Addon V%s or any previous version is not compatible with CBX Restaurant Booking V%s or later. Please update CBX Restaurant Booking Pro Addon to version V%s or later  - Codeboxr Team', 'cbxrbooking' ), $plugin_version, CBXRBOOKING_PLUGIN_VERSION, $version_required) . '</p></div>';

			// Throw an error in the wordpress admin console
			//$error_message = '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__( 'CBX Restaurant Booking Pro Addon V%s or any previous version is not compatible with CBX Restaurant Booking V%s or later. Please update CBX Restaurant Booking Pro Addon to version V%s or later  - Codeboxr Team', 'cbxrbooking' ), $plugin_version, CBXRBOOKING_PLUGIN_VERSION, $version_required) . '</p></div>';
			//die( $error_message );

			set_transient( 'cbxrbooking_proaddon_deactivate_notice', 1 );
		}
	}

    require_once plugin_dir_path(__FILE__) . 'includes/class-cbxrbooking-setting.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-cbxrbooking-helper.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-cbxrbooking-activator.php';

    CBXRBooking_Activator::activate();
}//end function activate_cbxrbooking

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cbxrbooking-deactivator.php
 */
function deactivate_cbxrbooking()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-cbxrbooking-setting.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-cbxrbooking-helper.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-cbxrbooking-deactivator.php';

    CBXRBooking_Deactivator::deactivate();
}//end function deactivate_cbxrbooking

/**
 * The code that runs during plugin uninstall
 * This action is documented in includes/class-cbxrbooking-uninstall.php
 */
function uninstall_cbxrbooking()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-cbxrbooking-setting.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-cbxrbooking-helper.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-cbxrbooking-uninstall.php';

    CBXRBooking_Uninstall::uninstall();
}//end function uninstall_cbxrbooking

register_activation_hook(__FILE__, 'activate_cbxrbooking');
register_deactivation_hook(__FILE__, 'deactivate_cbxrbooking');
register_deactivation_hook(__FILE__, 'uninstall_cbxrbooking');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-cbxrbooking.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cbxrbooking()
{

    $plugin = new CBXRBooking();
    $plugin->run();

}

run_cbxrbooking();