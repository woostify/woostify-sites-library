<?php
/**
 * Woostify for Import Export Contact Form 7.
 *
 * @see https://woostify.com/
 *
 * @package Woostify
 */

namespace Woostify\Admin;

/**
 * Woostify Import export contact form 7
 */
class Admin {

	/**
	 * Instance of Admin
	 *
	 * @since  1.0.0
	 * @var (Object) Admin
	 */
	private static $instance = null;

	/**
	 * Instance of Admin.
	 *
	 * @since  1.0.0
	 *
	 * @return object Class object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Register hook action.
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
	}

	public function add_submenu_page() {
		add_theme_page(
			// 'themes.php',
			'Woostify Import Template',
			'Woostify Import Template',
			'edit_theme_options',
			'import-template-setting',
			array( $this, 'setting_screen' )
		);
	}

	public function setting_screen()
	{
		# code...
	}

}
/**
 * Kicking this off by calling 'get_instance()' method
 */
\Woostify\Admin\Admin::get_instance();
