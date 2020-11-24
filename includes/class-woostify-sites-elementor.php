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
		add_action( 'elementor/editor/footer', array( $this, 'insert_templates' ), 99 );
		add_action( 'wp_ajax_woostify_modal_template', array( $this, 'modal_template' ) );
		add_action( 'wp_ajax_nopriv_woostify_modal_template', array( $this, 'modal_template' ) );
		add_action( 'elementor/editor/wp_head', array( $this, 'register_widget_style' ), 10 );
		add_action( 'wp_ajax_woostify_get_template', array( $this, 'get_template' ) );
		add_action( 'wp_ajax_nopriv_woostify_get_template', array( $this, 'get_template' ) );
		add_action( 'wp_ajax_woostify_import_template', array( $this, 'import_template' ) );
		add_action( 'wp_ajax_nopriv_woostify_import_template', array( $this, 'import_template' ) );
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

		$admin_vars = array(
			'url'   => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'woostify_nonce_field' ),
			'post_id' => get_the_ID(),
		);

		wp_localize_script(
			'woostify-sites-elementor',
			'admin',
			$admin_vars
		);

	}

	public function register_widget_style() {
		wp_enqueue_style(
			'woostify-sites-elementor',
			WOOSTIFY_SITES_URI . '/assets/css/elementor-editer.min.css',
			array(),
			'1.0.0'
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
		require_once WOOSTIFY_SITES_DIR . 'includes/templates/template.php';
		// require_once WOOSTIFY_SITES_DIR . 'includes/templates/image-templates.php';
		ob_end_flush();
	}


	public function modal_template() {
		check_ajax_referer( 'woostify_nonce_field' );
		$all_demo = woostify_sites_local_import_files();
		$demos    = array();
		foreach ($all_demo as $item) {
			if ( 'elementor' == $item['page_builder'] ) {
				$demos[] = $item;
			}
		}
		// echo "<pre>";
		// var_dump( $demos );
		// echo "</pre>";
		?>
		<div class="dialog-widget-content woostify-widget-content dialog-lightbox-widget-content">
			<div class="dialog-header dialog-lightbox-header">
				<div class="elementor-templates-modal__header">
					<div class="elementor-templates-modal__header__logo-area">
						<div class="elementor-templates-modal__header__logo">
							<span class="elementor-templates-modal__header__logo__icon-wrapper e-logo-wrapper">
								<i class="eicon-elementor"></i>
							</span>
							<span class="elementor-templates-modal__header__logo__title"><?php echo esc_html__( 'Library', 'woostify-sites-library' ) ?></span>
						</div>
						<div id="woostify-template-library-header-preview-back">
							<i class="eicon-" aria-hidden="true"></i>
							<span><?php echo esc_html__( 'Back to Library', 'woostify-sites-library' ) ?></span>
						</div>
					</div>
					<div class="elementor-templates-modal__header__menu-area">
						<div id="elementor-template-library-header-menu">
							<div class="elementor-component-tab elementor-template-library-menu-item" data-tab="templates/blocks"><?php echo esc_html__( 'Blocks', 'woostify-sites-library' ) ?></div>

							<div class="elementor-component-tab elementor-template-library-menu-item elementor-active" data-tab="templates/pages"><?php echo esc_html__( 'Pages', 'woostify-sites-library' ) ?></div>

						</div>
					</div>
					<div class="elementor-templates-modal__header__items-area">

						<div class="elementor-templates-modal__header__close elementor-templates-modal__header__close--normal elementor-templates-modal__header__item woostify-close-button">

							<i class="eicon-close" aria-hidden="true" title="Close"></i>
							<span class="elementor-screen-only"><?php echo esc_html__( 'Close', 'woostify-sites-library' ) ?></span>
						</div>

						<div id="woostify-template-library-header-tools">
							<div id="woostify-template-library-header-actions">
								<div id="woostify-template-library-header-import" class="elementor-templates-modal__header__item">
									<span class="button-text"><?php echo esc_html__( 'Import Template', 'woostify-sites-library' ); ?></span>
								</div>

								<div id="woostify-template-library-header-save" class="elementor-templates-modal__header__item">
									<span class="button-text"><?php echo esc_html__( 'Save', 'woostify-sites-library' ); ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
			<div id="wooostify-template-library-templates-container" class="wooostify-template-library-templates-container">

				<div class="woostify-template-wrapper">
					<?php foreach ( $demos as $demo ) : ?>
						<div class="woostify-tempalte-item template-builder-elementor elementor-template-library-template-page elementor-template-library-template-remote elementor-template-library-template-page" data-id="<?php echo esc_attr( $demo['id'] ); ?>">
							<div class="elementor-template-library-template-body">
								<div class="template-screenshot elementor-template-library-template-screenshot" style="background-image: url(<?php echo esc_url( $demo['import_preview_image_url'] ); ?>);">
									<div class="elementor-template-library-template-preview woostify-template-library-template-preview">
										<i class="eicon-zoom-in" aria-hidden="true"></i>
									</div>
								</div>
							</div>
							<div class="elementor-template-library-template-footer theme-id-container">
								<span class="theme-name"><?php echo esc_html( $demo['import_file_name'] ); ?></span>
							</div>
						</div>
					<?php endforeach ?>
				</div>
			</div>
		</div>

		<?php
		die();
	}

	public function get_template() {
		check_ajax_referer( 'woostify_nonce_field' );
		$id = $_GET['id'];
		$all_demo = woostify_sites_local_import_files();

		$demo = $all_demo[$id];
		?>
		<div class="woostify-template-wrapper">
			<div class="image-wrapper">
				<img src="<?php echo esc_url( $demo['import_preview_image_url'] ); ?>" alt="Image Preview">
				<input type="hidden" id="woostify-demo-data" value="<?php echo esc_attr( $demo['id'] ); ?>" name="demo-data">
			</div>
		</div>
		<?php
		die();

	}

	public function import_template() {
		check_ajax_referer( 'woostify_nonce_field' );
		$id = $_POST['id'];
		$all_demo = woostify_sites_local_import_files();
		$demo = $all_demo[$id];
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'woostify-sites-library' ) );
		}

		// if ( ! isset( $demo['preview_url'] ) ) {
		// 	wp_send_json_error( __( 'Invalid API URL', 'woostify-sites-library' ) );
		// }

		$response = wp_remote_get( 'https://travelcation.boostifythemes.com/wp-json/wp/v2/pages/1995' );// $demo['preview_url']

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( wp_remote_retrieve_body( $response ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( ! isset( $data['post-meta']['_elementor_data'] ) ) {
			wp_send_json_error( __( 'Invalid Post Meta', 'woostify-sites-library' ) );
		}

		$meta    = json_decode( $data['post-meta']['_elementor_data'][0], true );
		$post_id = (int) $_POST['post_id'];

		if ( empty( $post_id ) || empty( $meta ) ) {
			wp_send_json_error( __( 'Invalid Post ID or Elementor Meta', 'woostify-sites-library' ) );
		}

		$import      = new Woostify_Sites_Elementor_Pages();
		$import_data = $import->import( $post_id, $meta );

		wp_send_json_success( $import_data ); //$import_data

		die();

	}

}
/**
 * Kicking this off by calling 'get_instance()' method
 */
Woostify_Sites_Elementor::get_instance();