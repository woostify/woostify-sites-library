<?php
/**
 * Plugin Name:  Woostify Sites Library
 * Description:  Import site demos built with Woostify theme
 * Version:      1.6.0
 * Author:       duongancol
 * Author URI:   https://woostify.com
 * License:      GPLv2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  woostify-sites-library
 *
 * The following code is a derivative work from Merlin WP by the
 * Rich Tabor from ThemeBeans.com & the team at ProteusThemes.com and
 * Envato WordPress Theme Setup Wizard by David Baker.
 *
 * @package Woostify Sites
 */

/**
 * Set constants.
 */
if ( ! defined( 'WOOSTIFY_SITES_VER' ) ) {
	define( 'WOOSTIFY_SITES_VER', '1.6.0' );
}

if ( ! defined( 'WOOSTIFY_SITES_FILE' ) ) {
	define( 'WOOSTIFY_SITES_FILE', __FILE__ );
}

if ( ! defined( 'WOOSTIFY_SITES_BASE' ) ) {
	define( 'WOOSTIFY_SITES_BASE', plugin_basename( WOOSTIFY_SITES_FILE ) );
}

if ( ! defined( 'WOOSTIFY_SITES_DIR' ) ) {
	define( 'WOOSTIFY_SITES_DIR', plugin_dir_path( WOOSTIFY_SITES_FILE ) );
}

if ( ! defined( 'WOOSTIFY_SITES_URI' ) ) {
	define( 'WOOSTIFY_SITES_URI', plugins_url( '/', WOOSTIFY_SITES_FILE ) );
}

require_once WOOSTIFY_SITES_DIR . 'class-woostify-sites.php';
require_once WOOSTIFY_SITES_DIR . 'vendor/autoload.php';

function woostify_sites_load_textdomain() {
	load_plugin_textdomain( 'woostify_sites', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'woostify_sites_load_textdomain' );

/**
 * Set directory locations, text strings, and settings.
 */
$wizard      = new Woostify_Sites(
	$config  = array(
		'woostify_sites_url'   => 'woostify-sites', // The wp-admin page slug where Merlin WP loads.
		'parent_slug'          => 'themes.php', // The wp-admin parent page slug for the admin menu item.
		'capability'           => 'manage_options', // The capability required for this menu to be displayed to the user.
		'child_action_btn_url' => 'https://codex.wordpress.org/child_themes', // URL for the 'child-action-link'.
		'dev_mode'             => true, // Enable development mode for testing.
		'license_step'         => false, // EDD license activation step.
		'license_required'     => false, // Require the license activation step.
		'license_help_url'     => '', // URL for the 'license-tooltip'.
		'edd_remote_api_url'   => '', // EDD_Theme_Updater_Admin remote_api_url.
		'edd_item_name'        => '', // EDD_Theme_Updater_Admin item_name.
		'edd_theme_slug'       => '', // EDD_Theme_Updater_Admin item_slug.
		'ready_big_button_url' => '', // Link for the big button on the ready step.
	)
);

add_action(
	'init',
	function() {
		require_once WOOSTIFY_SITES_DIR . 'demos/demos.php';
		// require_once WOOSTIFY_SITES_DIR . 'includes/class-woostify-sites-elementor.php';

		/**
		 * Initialize the tracker
		 *
		 * @return void
		 */
		function appsero_init_tracker_woostify_sites_library() {

			if ( ! class_exists( 'Appsero\Client' ) ) {
				require_once __DIR__ . '/appsero/client/src/Client.php';
			}

			$client = new Appsero\Client( '424aa9f8-2435-4fa7-a61c-fad11ff04249', 'Woostify Sites Library', __FILE__ );

			// Active insights
			$client->insights()->hide_notice()->init();

		}

		appsero_init_tracker_woostify_sites_library();
	}
);
