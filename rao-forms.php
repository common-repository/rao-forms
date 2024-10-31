<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://app.raoforms.com
 * @since             1.0.0
 * @package           RFIP
 *
 * @wordpress-plugin
 * Plugin Name:       RAO Forms
 * Plugin URI:        https://raoinformationtechnology.com
 * Description:       Manage website form submissions at RAO Forms
 * Version:           1.0.0
 * Author:            RAO Information Technology
 * Author URI:        https://app.raoforms.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       raoforms
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RFIP_VERSION', "1.0" );
define('RFIP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RFIP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RFIP_APP_URL','https://app.raoforms.com/');
define('RFIP_PROFILE_URL','https://app.raoforms.com/user/profile');
define('RFIP_LIST_URL', 'https://app.raoforms.com/forms/list');
define('RFIP_ENDPOINT_URL', 'https://app.raoforms.com/api/v1/');

/**
 * Autoload classes to avoid adding require_once/include_once
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rfip-activator.php
 */
function activate_rao_forms() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rfip-activator.php';
	RFIP_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rfip-deactivator.php
 */
function deactivate_rao_forms() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rfip-deactivator.php';
	RFIP_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rao_forms' );
register_deactivation_hook( __FILE__, 'deactivate_rao_forms' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rfip.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rfip() {

	$plugin = new RFIP();
	$plugin->run();

}
run_rfip();