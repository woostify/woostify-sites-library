<?php
/**
 * Class for the Redux importer.
 *
 * @see https://wordpress.org/plugins/redux-framework/
 *
 * @package Merlin WP
 */

class Woostify_Sites_Elementor {

	/**
	 * Instance of Astra_Sites
	 *
	 * @since  1.0.0
	 * @var (Object) Astra_Sites
	 */
	private static $instance = null;

	/**
	 * Instance of Astra_Sites.
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

	public function __construct() {
		$this->hooks();
	}

	public function hooks()
	{
		add_action( 'elementor/editor/footer', array( $this, 'register_widget_scripts' ), 99 );
	}


		/**
		 * Register module required js on elementor's action.
		 *
		 * @since 2.0.0
		 */
		public function register_widget_scripts() {

			$page_builders = self::get_instance()->get_page_builders();
			$has_elementor = false;

			foreach ( $page_builders as $page_builder ) {

				if ( 'elementor' === $page_builder['slug'] ) {
					$has_elementor = true;
				}
			}

			if ( ! $has_elementor ) {
				return;
			}
			wp_enqueue_script(
				'woostify-sites-elementor',
				WOOSTIFY_SITES_URI . 'assets/js/elementor-admin-page.js',
				array( 'jquery' ),
				'1.0.0',
				true
			);

		}

		/**
		 * Get Page Builders
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function get_page_builders() {
			return $this->get_default_page_builders();
		}

		/**
		 * Get Default Page Builders
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function get_default_page_builders() {
			return array(
				array(
					'id'   => 33,
					'slug' => 'elementor',
					'name' => 'Elementor',
				),
			);
		}

		/**
		 * Insert Template
		 *
		 * @return void
		 */
		public function insert_templates() {
			ob_start();
			require_once WOOSTIFY_SITES_DIR . 'inc/includes/template.php';
			require_once WOOSTIFY_SITES_DIR . 'inc/includes/image-templates.php';
			ob_end_flush();
		}

}
/**
 * Kicking this off by calling 'get_instance()' method
 */
Woostify_Sites_Elementor::get_instance();