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
	 * Extract CSS rules from a URL by searching for <style> tags and elementor-element classes.
	 */
	private function extract_css_from_url( $url ) {
		$response = wp_remote_get( $url, [ 'timeout' => 15 ] );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return [];
		}

		$html = wp_remote_retrieve_body( $response );
		if ( empty( $html ) ) return [];

		$css_contents = [];
		// Extract all style blocks
		if ( preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $matches ) ) {
			$css_contents = array_merge( $css_contents, $matches[1] );
		}

		// Also look for external Elementor CSS files
		if ( preg_match_all( '/<link[^>]+href=[\'"]([^\'"]+elementor\/css\/post-[^\'"]+\.css[^\'"]*)[\'"]/i', $html, $link_matches ) ) {
			foreach ( $link_matches[1] as $css_url ) {
				$css_res = wp_remote_get( $css_url, [ 'timeout' => 10 ] );
				if ( ! is_wp_error( $css_res ) && 200 === wp_remote_retrieve_response_code( $css_res ) ) {
					$css_contents[] = wp_remote_retrieve_body( $css_res );
				}
			}
		}

		$css_rules = [];
		foreach ( $css_contents as $content ) {
			// Find blocks of { ... }
			if ( preg_match_all( '/([^{]+)\{([^}]+)\}/i', $content, $matches ) ) {
				foreach ( $matches[1] as $i => $selector_block ) {
					$declarations = trim( $matches[2][$i] );
					$parsed_props = $this->parse_css_declarations( $declarations );
					if ( empty( $parsed_props ) ) continue;

					$selectors = explode( ',', $selector_block );
					foreach ( $selectors as $selector ) {
						$selector = trim( $selector );
						// Find .elementor-element-{id}
						if ( preg_match( '/\.elementor-element-([a-f0-9]+)/i', $selector, $m_id ) ) {
							$el_id = $m_id[1];
							if ( ! isset( $css_rules[$el_id] ) ) {
								$css_rules[$el_id] = [ 'main' => [], 'backgrounds' => [] ];
							}

							// Determine if this selector targets the main element or a sub-element
							// If it has spaces after the ID (excluding pseudo-classes), it likely targets a child
							$after_id = substr( $selector, strpos( $selector, $m_id[0] ) + strlen( $m_id[0] ) );
							
							// Check if it targets common descendants
							$sub_targets = [ 
								'overlay' => '.elementor-background-overlay',
								'container' => '.elementor-widget-container',
								'wrap' => '.elementor-widget-wrap',
								'column_wrap' => '.elementor-column-wrap',
								'button' => '.elementor-button',
								'heading' => '.elementor-heading-title'
							];

							reset($sub_targets);
							$matched_sub = false;
							foreach ( $sub_targets as $key => $target_class ) {
								if ( strpos( $selector, $target_class ) !== false ) {
									if ( ! isset( $css_rules[$el_id][$key] ) ) $css_rules[$el_id][$key] = [];
									$css_rules[$el_id][$key] = array_merge( $css_rules[$el_id][$key], $parsed_props );
									$matched_sub = true;
									break;
								}
							}

							// If no specific sub-target, check if it's a "main" modifier (pseudo-classes, or direct element)
							if ( ! $matched_sub ) {
								// If it has spaces, but doesn't match above, it might be a generic descendant
								if ( preg_match( '/\s+[a-z0-9#\._-]/i', $after_id ) ) {
									// Generic descendant
									$css_rules[$el_id]['main'] = array_merge( $css_rules[$el_id]['main'], $parsed_props );
								} else {
									// Modifier or exact match
									$css_rules[$el_id]['main'] = array_merge( $css_rules[$el_id]['main'], $parsed_props );
								}
							}

							// Track background-image separately regardless of selector specificity as a strong hint
							if ( isset( $parsed_props['background-image'] ) && strpos( $parsed_props['background-image'], 'url(' ) !== false ) {
								$css_rules[$el_id]['fallback_background'] = $parsed_props['background-image'];
							}
						}
					}
				}
			}
		}

		return $css_rules;
	}

	/**
	 * Parse dimensions (padding/margin/border-radius) into Elementor format.
	 */
	private function parse_dimensions( $val ) {
		$unit = 'px';
		if ( preg_match( '/(px|em|rem|%|vh|vw|pt)/i', $val, $u_m ) ) {
			$unit = $u_m[1];
		}
		
		$clean_val = preg_replace( '/[a-z%]/i', '', $val );
		$values = preg_split( '/\s+/', trim( $clean_val ) );
		$struct = [ 'unit' => $unit, 'isLinked' => false ];
		
		$count = count( $values );
		if ( $count === 1 ) {
			$v = $values[0];
			$struct['top'] = $struct['right'] = $struct['bottom'] = $struct['left'] = $v;
			$struct['isLinked'] = true;
		} elseif ( $count === 2 ) {
			$struct['top'] = $struct['bottom'] = $values[0];
			$struct['left'] = $struct['right'] = $values[1];
		} elseif ( $count === 3 ) {
			$struct['top'] = $values[0];
			$struct['right'] = $struct['left'] = $values[1];
			$struct['bottom'] = $values[2];
		} elseif ( $count >= 4 ) {
			$struct['top'] = $values[0];
			$struct['right'] = $values[1];
			$struct['bottom'] = $values[2];
			$struct['left'] = $values[3];
		} else {
			$v = $values[0];
			if ( '' === $v ) $v = '0';
			$struct['top'] = $struct['right'] = $struct['bottom'] = $struct['left'] = $v;
		}
		return $struct;
	}

	/**
	 * Parse CSS declarations string into an associative array of properties.
	 */
	private function parse_css_declarations( $css_text ) {
		$properties = [];
		// Filter out !important and clean up
		$css_text = preg_replace( '/\s*!important/i', '', $css_text );
		// Basic comment removal
		$css_text = preg_replace( '/\/\*.*?\*\//s', '', $css_text );
		$rules = explode( ';', $css_text );
		foreach ( $rules as $rule ) {
			$parts = explode( ':', $rule, 2 );
			if ( count( $parts ) === 2 ) {
				$key = strtolower( trim( $parts[0] ) );
				$val = trim( $parts[1] );
				if ( $key && $val ) {
					// Clean up values (remove extra quotes, etc)
					$properties[$key] = $val;
				}
			}
		}
		return $properties;
	}

	/**
	 * Property to hold external CSS during parsing.
	 */
	private $external_css = [];
	private $last_parsed_count = 0;

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

	public function hooks() {
		add_action( 'elementor/editor/footer', array( $this, 'register_widget_scripts' ), 99 );
		add_action( 'elementor/editor/footer', array( $this, 'insert_templates' ), 99 );
		add_action( 'wp_ajax_woostify_modal_template', array( $this, 'modal_template' ) );
		add_action( 'wp_ajax_nopriv_woostify_modal_template', array( $this, 'modal_template' ) );
		add_action( 'elementor/editor/wp_head', array( $this, 'register_widget_style' ), 10 );
		add_action( 'wp_ajax_woostify_get_template', array( $this, 'get_template' ) );
		add_action( 'wp_ajax_woostify_import_template', array( $this, 'import_template' ) );
		add_action( 'rest_api_init', array( $this, 'create_api_posts_meta_field' ) );
		add_action( 'template_redirect',  array( $this, 'collect_post_id' ) );
		add_action( 'wp_ajax_woostify_select_demo_type', array( $this, 'select_demo_type' ) );
		add_action( 'wp_ajax_nopriv_woostify_select_demo_type', array( $this, 'select_demo_type' ) );
		add_action( 'wp_ajax_woostify_list_child_page', array( $this, 'list_child_page' ) );
		add_action( 'wp_ajax_nopriv_woostify_list_child_page', array( $this, 'list_child_page' ) );
		add_action( 'wp_ajax_woostify_wishlist_template', array( $this, 'favorite_template' ) );
		add_action( 'wp_ajax_nopriv_woostify_wishlist_template', array( $this, 'favorite_template' ) );

		add_action( 'wp_ajax_woostify_list_favorite', array( $this, 'list_favorite' ) );
		add_action( 'wp_ajax_nopriv_woostify_list_favorite', array( $this, 'list_favorite' ) );
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
			WOOSTIFY_SITES_VER,
			true
		);

		$admin_vars = array(
			'url'     => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'woostify_nonce_field' ),
			'post_id' => get_the_ID(),
			'icon'    => WOOSTIFY_SITES_URI . 'assets/images/logo-icon.png',
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
			WOOSTIFY_SITES_URI . 'assets/css/elementor-editer.min.css',
			array(),
			WOOSTIFY_SITES_VER
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
		ob_end_flush();
	}




	public function create_api_posts_meta_field() {
		$types = array( 'page', 'hf_builder', 'woo_builder', 'btf_builder' );

		foreach ( $types as $type ) {
			register_rest_field(
				$type,
				'post-meta',
				array(
					'get_callback' => array( $this, 'get_post_meta_for_api' ),
					'schema'       => null,
				)
			);
		}
	}

	public function get_post_meta_for_api( $object ) {
		//get the id of the post object array
		$post_id = $object['id'];
		//return the post meta
		return get_post_meta($post_id);
	}

	public function collect_post_id() {
		static $id = 0;
		if ( 'template_redirect' === current_filter() && is_singular() )
			$id = get_the_ID();

		return $id;
	}

	public function get_demo( $template_type ) {
		$all_demo = array();
		switch ( $template_type ) {
			case 'blocks':
				$all_demo = woostify_sites_section();
				break;

			case 'header':
				$all_demo = woostify_sites_header();
				break;

			case 'footer':
				$all_demo = woostify_sites_footer();
				break;

			case 'shop':
				$all_demo = woostify_sites_shop();
				break;
			default:
				$all_demo = woostify_sites_local_import_files();
				break;
		}

		return $all_demo;
	}

	public function demo_filter( $type_template ) {

		switch ( $type_template ) {
			case 'blocks':
				$list_sort = woostify_filter_section();
				break;

			case 'header':
				$list_sort = woostify_filter_header();
				break;

			case 'footer':
				$list_sort = woostify_filter_footer();
				break;

			case 'shop':
				$list_sort = woostify_filter_shop();
				break;
			default:
				$list_sort = woostify_filter_pages();
				break;
		}

		?>
		<div class="elementor-template-library-order" style="display: flex;">
			<select class="elementor-template-library-order-input elementor-template-library-filter-select elementor-select2 woostify-select-demo-type" data-type="<?php echo esc_attr( $type_template ); ?>">
				<option value="">
						<?php echo esc_html__( 'All', 'woostify-sites-library' ); ?>
					</option>
				<?php foreach ( $list_sort as $value => $label ): ?>
					<option value="<?php echo esc_attr( $value ); ?>">
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach ?>
			</select>
		</div>
		<?php
	}

	public function modal_template() {
		check_ajax_referer( 'woostify_nonce_field' );
		$template_type = $_GET['type'];

		$all_demo = $this->get_demo( $template_type );
		$demos    = array();
		foreach ($all_demo as $item) {
			if ( 'elementor' == $item['page_builder'] ) {
				$demos[] = $item;
			}
		}

		$types       = $this->modal_header_tab();
		$license_key = get_option( 'woostify_pro_license_key_status', 'invalid' );
		$user_id     = get_current_user_id();
		$usermeta    = get_user_meta( $user_id, 'woostify-favorite-template' );
		if ( $usermeta ) {
			$usermeta    = unserialize( $usermeta[0] ); //phpcs:ignore
		}
		$favorite    = array();
		if ( ! empty( $usermeta ) && array_key_exists( $template_type, $usermeta ) ) {
			$favorite = $usermeta[ $template_type ];
		}
		?>
		<div class="dialog-widget-content woostify-widget-content dialog-lightbox-widget-content">
			<div class="dialog-header dialog-lightbox-header">
				<div class="elementor-templates-modal__header">
					<div class="elementor-templates-modal__header__logo-area">
						<div class="elementor-templates-modal__header__logo">
							<span class="elementor-templates-modal__header__logo__icon-wrapper e-logo-wrapper" style="background: #4744b7; width: 35px;">
								<img height="15" width="15" src="http://demo.woostify.com/wp-content/uploads/2021/07/icon-logo.svg" style="margin-left: 1px;"/>
							</span>
							<span class="elementor-templates-modal__header__logo__title"><?php echo esc_html__( 'Library', 'woostify-sites-library' ) ?></span>
						</div>

					</div>
					<div class="elementor-templates-modal__header__menu-area">
						<div id="woostify-template-library-header-menu" class="woostify-template-library-header-menu">
							<?php
								foreach ($types as $key => $value):
									$active = '';
									if ( $key == $template_type ):
										$active = ' elementor-active';
									endif;
									?>
								<div class="elementor-component-tab elementor-template-library-menu-item woostify-template-library-menu-item<?php echo esc_attr__( $active ); ?>" data-tab="<?php echo esc_attr( $key ) ?>"><?php echo esc_html($value) ?></div>
							<?php endforeach ?>

						</div>
					</div>
					<div class="elementor-templates-modal__header__items-area">

						<div class="elementor-templates-modal__header__close elementor-templates-modal__header__close--normal elementor-templates-modal__header__item woostify-close-button">

							<i class="eicon-close" aria-hidden="true" title="Close"></i>
							<span class="elementor-screen-only"><?php echo esc_html__( 'Close', 'woostify-sites-library' ) ?></span>
						</div>

					</div>
				</div>

			</div>
			<div id="wooostify-template-library-templates-container" class="wooostify-template-library-templates-container">
				<div class="woostify-template-library-toolbar" style="display: flex;">
					<div class="elementor-template-library-filter-toolbar">
						<?php $this->demo_filter( $template_type ); ?>

						<div class="woostify-template-favorite">
							<a href="#" class="woostify-link-favorite"><?php echo esc_html__( 'My Favorites', 'woostify-sites-library' ); ?></a>
						</div>
					</div>

				</div>
				<div class="woostify-template-wrapper">
					<?php foreach ( $demos as $demo ) : ?>

						<?php
							$types = explode( '__', $demo['type']);
							$class = 'woostify-tempalte-item template-builder-elementor elementor-template-library-template-remote elementor-template-library-template-' . $template_type;
							$class .= ( end( $types ) == 'pro' || $template_type == 'shop' ) ? ' elementor-template-library-pro-template' : '';
							$class .= $template_type == 'pages' ? ' elementor-template-library-template-page' : '';

							$type = ( 'pages' == $template_type ) ? ' elementor-type-pages elementor-template-library-template-page' : ' woostify-template-library-template-preview elementor-type-blocks';
							$checked = '';
							$favorite_class = 'eicon-heart-o';
							if ( ! empty( $favorite ) && in_array( $demo['id'], $favorite ) ) {
								$checked = 'checked';
								$favorite_class = 'eicon-heart';
							}
						?>
						<div class="<?php echo esc_attr( $class ); ?>" data-id="<?php echo esc_attr( $demo['id'] ); ?>" data-type="<?php echo esc_attr( $template_type ); ?>">
							<div class="elementor-template-library-template-body">
								<div class="template-screenshot elementor-template-library-template-screenshot">
									<img src="<?php echo esc_url( $demo['import_preview_image_url'] ); ?>" alt="">
									<div class="elementor-template-library-template-preview <?php echo esc_attr( $type ); ?>">
										<i class="eicon-zoom-in" aria-hidden="true"></i>
									</div>
								</div>

							</div>

							<div class="elementor-template-library-template-footer theme-id-container">
								<span class="theme-name"><?php echo esc_html( $demo['import_file_name'] ); ?></span>
								<div class="woostify-template-library-favorite">
									<input type="checkbox" name="favorite" value="<?php echo $template_type . '-' . $demo['id']; ?>" class="woostify-favorite-template-input" <?php echo esc_attr( $checked ); ?>>
									<label class="favorite-label">
										<span class="<?php echo $favorite_class; ?>"></span>
									</label>
								</div>

							</div>

						</div>
					<?php endforeach ?>
				</div>
			</div>
		</div>

		<?php
		// wp_send_json_success( $license_key );
		die();
	}

	public function get_template() {
		check_ajax_referer( 'woostify_nonce_field' );
		$id      = $_GET['id'];
		$type    = $_GET['type'];
		$page_id = 0;
		if ( array_key_exists('page', $_GET) ) {
			$page_id = $_GET['page'];
		}

		$all_demo = $this->get_demo( $type );
		$demo = $all_demo[$id];
		$demo_type = $demo['type'];
		$types = explode( '__', $demo['type']);
		$image_preview = $demo['import_preview_image_url'];

		if ( 'pages' == $type ) {
			$image_preview = $demo['page'][$page_id]['preview'];
		}
		$check_pro = get_option( 'woostify_pro_license_key_status', 'invalid' );
		?>
			<div class="dialog-header dialog-lightbox-header">
				<div class="elementor-templates-modal__header">
					<div class="elementor-templates-modal__header__logo-area">
						<div id="woostify-template-library-header-preview-back" step="3">
							<i class="eicon-arrow-left" aria-hidden="true"></i>
							<span><?php echo esc_html__( 'Back to Library', 'woostify-sites-library' ) ?></span>
						</div>
					</div>

					<div class="elementor-templates-modal__header__items-area">

						<div class="elementor-templates-modal__header__close elementor-templates-modal__header__close--normal elementor-templates-modal__header__item woostify-close-button">

							<i class="eicon-close" aria-hidden="true" title="Close"></i>
							<span class="elementor-screen-only"><?php echo esc_html__( 'Close', 'woostify-sites-library' ) ?></span>
						</div>

						<div id="woostify-template-library-header-tools" class="<?php echo esc_attr( $check_pro ); ?>">
							<div id="woostify-template-library-header-actions">
								<?php if ( 'valid' != $check_pro && ( 'pro' == end($types) || $type == 'shop' ) ) : ?>
									<div id="woostify-template-library-go-pro" class="elementor-templates-modal__header__item">
										<a href="<?php echo esc_url( 'https://woostify.com/' ); ?>" class="elementor-go-pro" target="_blank">
											<span class="button-text"><?php echo esc_html__( 'Go Pro', 'woostify-sites-library' ); ?></span>
										</a>
									</div>
								<?php else : ?>
									<div id="woostify-template-library-header-import" class="elementor-templates-modal__header__item">
										<span class="button-text"><?php echo esc_html__( 'Import Template', 'woostify-sites-library' ); ?></span>
									</div>
								<?php endif ?>

							</div>
						</div>
					</div>
				</div>

			</div>
			<div id="wooostify-template-library-templates-container" class="wooostify-template-library-templates-container">
				<div class="woostify-template-wrapper">
					<div class="image-wrapper">
						<img src="<?php echo esc_url( $image_preview ); ?>" alt="Image Preview">
						<input type="hidden" id="woostify-demo-data" value="<?php echo esc_attr( $demo['id'] ); ?>" name="demo-data">
						<input type="hidden" id="woostify-demo-type" value="<?php echo esc_attr( $type ); ?>">
						<input type="hidden" id="woostify-demo-page" value="<?php echo esc_attr( $page_id ); ?>">
					</div>
				</div>
			</div>
		<?php
		die();

	}

	public function import_template() {
		check_ajax_referer( 'woostify_nonce_field' );
		$id           = $_POST['id'];
		$type         = $_POST['type'];
		$page         = $_POST['page'];
		$contact_form = '';

		switch ($type) {
			case 'blocks':
				$all_demo = woostify_sites_section();
				$rest_url = 'wp-json/wp/v2/pages/';
				$demo     = $all_demo[$id];
				$page     = $demo['font_page'];
				break;

			case 'header':
				$all_demo = woostify_sites_header();
				$rest_url = 'wp-json/wp/v2/pages/';
				$demo     = $all_demo[$id];
				$page     = $demo['font_page'];
				break;

			case 'footer':
				$all_demo = woostify_sites_footer();
				$rest_url = 'wp-json/wp/v2/pages/';
				$demo     = $all_demo[$id];
				$page     = $demo['font_page'];
				break;

			case 'shop':
				$all_demo = woostify_sites_shop();
				$rest_url = 'wp-json/wp/v2/pages/';
				$demo     = $all_demo[$id];
				$page     = $demo['font_page'];
				break;

			default:
				$all_demo = woostify_sites_local_import_files();
				$rest_url = 'wp-json/wp/v2/pages/';
				$demo     = isset( $all_demo[$id] ) ? $all_demo[$id] : null;
				break;
		}

		if ( ! $demo ) {
			wp_send_json_error( __( 'Demo data not found.', 'woostify-sites-library' ) );
		}


		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'woostify-sites-library' ) );
		}

		if ( ! isset( $demo['preview_url'] ) ) {
			wp_send_json_error( __( 'Invalid API URL', 'woostify-sites-library' ) );
		}
		$url = $demo['preview_url'] . $rest_url . $page;

		$response = wp_remote_get( $url, [ 'timeout' => 20 ] );
		$response_code = wp_remote_retrieve_response_code( $response );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		if ( 200 !== $response_code ) {
			wp_send_json_error( sprintf( __( 'Remote API returned error code %d', 'woostify-sites-library' ), $response_code ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			wp_send_json_error( __( 'Invalid API response format', 'woostify-sites-library' ) );
		}

		$meta_json = '';
		if ( isset( $data['post-meta']['_elementor_data'] ) ) {
			$meta_json = $data['post-meta']['_elementor_data'][0];
		} else if ( isset( $data['_elementor_data'] ) ) {
			$meta_json = $data['_elementor_data'];
		} else if ( isset( $data['meta']['_elementor_data'] ) ) {
			$meta_json = $data['meta']['_elementor_data'];
		}

		$meta = [];
		if ( ! empty( $meta_json ) ) {
			$meta = json_decode( $meta_json, true );
		}

		// Try local XML fallback if remote API failed
		if ( empty( $meta ) ) {
			if ( isset( $demo['local_import_file'] ) && file_exists( $demo['local_import_file'] ) ) {
				$local_meta_raw = $this->get_elementor_data_from_xml( $demo['local_import_file'], $page );
				if ( ! empty( $local_meta_raw ) ) {
					$meta = json_decode( $local_meta_raw, true );
				}
			}
		}

		// Search in ALL demo XMLs if still empty (Blocks are often hidden in other demo XMLs)
		if ( empty( $meta ) ) {
			$cache_key = 'woostify_sites_xml_path_' . $page;
			$cached_path = get_transient( $cache_key );
			
			if ( $cached_path && file_exists( $cached_path ) ) {
				$local_meta_raw = $this->get_elementor_data_from_xml( $cached_path, $page );
				if ( ! empty( $local_meta_raw ) ) {
					$meta = json_decode( $local_meta_raw, true );
				}
			}

			if ( empty( $meta ) ) {
				$all_demos = apply_filters( 'woostify_sites_import_files', [] );
				$searched_files = [];
				$count = 0;
				foreach ( $all_demos as $d ) {
					$xml_file = isset( $d['local_import_file'] ) ? $d['local_import_file'] : '';
					if ( ! empty( $xml_file ) && file_exists( $xml_file ) && ! in_array( $xml_file, $searched_files ) ) {
						$searched_files[] = $xml_file;
						$local_meta_raw = $this->get_elementor_data_from_xml( $xml_file, $page );
						if ( ! empty( $local_meta_raw ) ) {
							$meta = json_decode( $local_meta_raw, true );
							if ( ! empty( $meta ) ) {
								set_transient( $cache_key, $xml_file, WEEK_IN_SECONDS );
								break;
							}
						}
						// Limit search to first 50 files to prevent total timeout
						if ( ++$count > 50 ) break;
					}
				}
			}
		}

		// Final fallback: parse Elementor structure from content.rendered HTML
		if ( empty( $meta ) && isset( $data['content']['rendered'] ) && ! empty( $data['content']['rendered'] ) ) {
			// Try to fetch full page CSS if JSON is missing
			$full_page_css = [];
			if ( isset( $data['link'] ) ) {
				$full_page_css = $this->extract_css_from_url( $data['link'] );
			}
			$meta = $this->parse_elementor_from_rendered_html( $data['content']['rendered'], $full_page_css );
		}

		if ( empty( $meta ) ) {
			$error_msg = __( 'Elementor data not found in this block.', 'woostify-sites-library' );
			if ( empty( $data['content']['rendered'] ) ) {
				$error_msg .= ' ' . __( 'The API response has empty content.', 'woostify-sites-library' );
			} else {
				$error_msg .= ' HTML Length: ' . strlen($data['content']['rendered']);
				$error_msg .= ' HTML Snippet: ' . htmlspecialchars(substr($data['content']['rendered'], 0, 100));
				$error_msg .= ' Elements: ' . $this->last_parsed_count;
			}
			wp_send_json_error( $error_msg );
		}

		$post_id = (int) $_POST['post_id'];

		if ( empty( $post_id ) ) {
			wp_send_json_error( __( 'Invalid Post ID', 'woostify-sites-library' ) );
		}
		if ( array_key_exists( 'contact_form', $demo ) ) {
			$contact_form = $demo['contact_form'];
		}

		$import      = new Woostify_Sites_Elementor_Pages();
		$import_data = $import->import( $post_id, $meta, $contact_form, false );
		wp_send_json_success( $import_data );
	}

	/**
	 * Get elementor data from XML file
	 *
	 * @param string $xml_path Path to XML file.
	 * @param int    $post_id  Post ID.
	 * @return string|bool
	 */
	public function get_elementor_data_from_xml( $xml_path, $post_id ) {
		$handle = fopen( $xml_path, 'r' );
		if ( ! $handle ) {
			return false;
		}
		
		$id_string = '<wp:post_id>' . $post_id . '</wp:post_id>';
		$meta_key  = '<wp:meta_key><![CDATA[_elementor_data]]></wp:meta_key>';
		
		$found_post = false;
		$buffer     = '';
		$result     = false;

		while ( ! feof( $handle ) ) {
			$chunk = fread( $handle, 16384 ); // Read 16KB at a time
			$buffer .= $chunk;

			// Step 1: Find the post_id
			if ( ! $found_post ) {
				$pos = strpos( $buffer, $id_string );
				if ( $pos !== false ) {
					$found_post = true;
					$buffer = substr( $buffer, $pos ); // Keep from post_id onwards
				}
			}

			// Step 2: Once post is found, look for Elementor data key
			if ( $found_post ) {
				// Prevent buffer from growing too large if metadata is far away
				// But we must be careful not to cut off the meta_key
				if ( strlen( $buffer ) > 500000 ) {
					// Check if we passed the next post_id (meaning we skipped the meta)
					if ( strpos( substr( $buffer, 100 ), '<wp:post_id>' ) !== false ) {
						break; // Moved to another post, stop
					}
					$buffer = substr( $buffer, -1000 ); // Just keep the end
				}

				if ( strpos( $buffer, $meta_key ) !== false ) {
					// Step 3: Extract the CDATA value
					$val_start = strpos( $buffer, '<wp:meta_value><![CDATA[', strpos( $buffer, $meta_key ) );
					if ( $val_start !== false ) {
						$val_start += strlen( '<wp:meta_value><![CDATA[' );
						
						// Now read until we find the ending ]]>
						while ( strpos( $buffer, ']]></wp:meta_value>', $val_start ) === false && ! feof( $handle ) ) {
							$buffer .= fread( $handle, 32768 );
							if ( strlen( $buffer ) > 5000000 ) break; // Safety break (5MB meta is huge)
						}
						
						$val_end = strpos( $buffer, ']]></wp:meta_value>', $val_start );
						if ( $val_end !== false ) {
							$result = substr( $buffer, $val_start, $val_end - $val_start );
							break;
						}
					}
				}
			}

			// Keep the buffer reasonable (last 100 chars to handle split tags)
			if ( ! $found_post && strlen( $buffer ) > 2000 ) {
				$buffer = substr( $buffer, -100 );
			}
		}
		
		fclose( $handle );
		return $result;
	}

	/**
	 * Parse Elementor from rendered HTML
	 * 
	 * @param string $html HTML content.
	 * @param array  $css  Optional external CSS rules.
	 * @return array
	 */
	private function parse_elementor_from_rendered_html( $html, $css = [] ) {
		if ( empty( $html ) ) {
			return [];
		}

		// Handle UTF-8 properly for DOMDocument
		$html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' );

		// Suppress HTML parsing warnings
		$prev = libxml_use_internal_errors( true );
		$dom  = new DOMDocument();
		// Avoid LIBXML_HTML_NOIMPLIED to ensure we have a valid DOM tree
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
		libxml_clear_errors();
		libxml_use_internal_errors( $prev );

		$xpath = new DOMXPath( $dom );

		// Find all elementor elements (sections, containers, widgets)
		$all_elements = $xpath->query( '//*[@data-id or @data-element_type]' );
		$this->last_parsed_count = $all_elements ? $all_elements->length : 0;
		$this->external_css = $css;

		$elements = [];
		if ( $all_elements ) {
			foreach ( $all_elements as $node ) {
				// A node is top-level if none of its ancestors have data-element_type
				$parent       = $node->parentNode;
				$is_top_level = true;
				while ( $parent && $parent->nodeType === XML_ELEMENT_NODE ) {
					if ( $parent->hasAttribute( 'data-element_type' ) ) {
						$is_top_level = false;
						break;
					}
					$parent = $parent->parentNode;
				}

				if ( $is_top_level ) {
					$element = $this->parse_elementor_node( $node, $xpath, $dom );
					if ( $element ) {
						$elements[] = $element;
					}
				}
			}
		}
		return $elements;
	}

	/**
	 * Apply styles from a DOM node to Elementor settings array.
	 * 
	 * @param DOMElement $node     The node to extract styles from.
	 * @param array      $settings Reference to the settings array.
	 * @param string     $el_type  The element type (section, column, widget).
	 * @param bool       $is_child Whether this is a child node.
	 */
	/**
	 * Apply styles from a DOM node to Elementor settings array.
	 */
	private function apply_styles_to_settings( $node, &$settings, $el_type, $is_child = false, $extra_css_props = [] ) {
		$inline_style = $node->getAttribute( 'style' );
		$props = array_merge( $extra_css_props, $this->parse_css_declarations( $inline_style ) );

		if ( empty( $props ) ) {
			return;
		}

		$widget_type_raw = $node->getAttribute( 'data-widget_type' );
		$class_attr      = $node->getAttribute( 'class' );
		$is_button       = ( strpos( $widget_type_raw, 'button' ) !== false ) || ( strpos( $class_attr, 'elementor-button' ) !== false ) || ( 'widget' === $el_type && strpos( $widget_type_raw, 'button' ) !== false );
		$is_heading      = ( strpos( $widget_type_raw, 'heading' ) !== false ) || ( strpos( $class_attr, 'elementor-heading' ) !== false );

		foreach ( $props as $key => $val ) {
			$clean_val = trim( $val, " \t\n\r\0\x0B;\"'" );

			// Background Handling
			if ( 'background' === $key || 'background-image' === $key || 'background-color' === $key ) {
				if ( preg_match( '/url\([\'"]?([^\'"]+)[\'"]?\)/i', $val, $m ) ) {
					$settings['background_image'] = [ 'url' => $m[1] ];
					$settings['background_background'] = 'classic';
				}
				if ( preg_match( '/(#[a-f0-9]{3,6}|rgba?\([^\)]+\)|[a-z]+)(?![^\(]*\))/i', $val, $m ) && ! strpos( $val, 'url' ) ) {
					$color = $m[1];
					if ( 'none' !== $color && 'transparent' !== $color ) {
						$settings['background_color'] = $color;
						$settings['background_background'] = 'classic';
						if ( $is_button ) {
							$settings['button_background_color'] = $color;
							$settings['background_color'] = $color;
						}
					}
				}
				if ( preg_match( '/(no-repeat|repeat|repeat-x|repeat-y)/i', $val, $m ) ) $settings['background_repeat'] = $m[1];
				if ( preg_match( '/(center|top|bottom|left|right)/i', $val, $m ) ) {
					$pos = $m[1];
					if ( preg_match( '/(center|top|bottom|left|right)\s+(center|top|bottom|left|right)/i', $val, $m2 ) ) {
						$pos = $m2[1] . ' ' . $m2[2];
					}
					$settings['background_position'] = $pos;
				}
				if ( preg_match( '/(cover|contain|[0-9]+%)/i', $val, $m ) ) $settings['background_size'] = $m[1];
			}

			// Background Details
			if ( 'background-size' === $key ) $settings['background_size'] = $val;
			if ( 'background-position' === $key ) $settings['background_position'] = $val;
			if ( 'background-repeat' === $key ) $settings['background_repeat'] = $val;

			// Text Color
			if ( 'color' === $key ) {
				if ( $is_button ) {
					$settings['button_text_color'] = $val;
					$settings['text_color'] = $val;
				} elseif ( $is_heading ) {
					$settings['title_color'] = $val;
				} elseif ( ! $is_child || empty( $settings['text_color'] ) ) {
					$settings['text_color'] = $val;
				}
			}

			// Typography
			if ( 'font-size' === $key || 'font-weight' === $key || 'font-family' === $key || 'line-height' === $key || 'text-transform' === $key || 'font-style' === $key || 'text-decoration' === $key ) {
				$settings['typography_typography'] = 'custom';
				if ( $is_button ) $settings['button_typography_typography'] = 'custom';

				if ( 'font-size' === $key && preg_match( '/([0-9\.]+)(px|em|rem|vh|vw|%)/i', $val, $m ) ) {
					$settings['typography_font_size'] = [ 'unit' => $m[2], 'size' => $m[1] ];
				}
				if ( 'font-weight' === $key ) {
					$settings['typography_font_weight'] = $val;
				}
				if ( 'font-family' === $key ) {
					$settings['typography_font_family'] = trim( $val, " '\"" );
				}
				if ( 'line-height' === $key ) {
					$lh_unit = 'em';
					$lh_val = $val;
					if ( preg_match( '/([0-9\.]+)(px|em|rem|%)/i', $val, $m ) ) {
						$lh_unit = $m[2];
						$lh_val = $m[1];
					}
					$settings['typography_line_height'] = [ 'unit' => $lh_unit, 'size' => $lh_val ];
				}
				if ( 'text-transform' === $key ) $settings['typography_text_transform'] = $val;
				if ( 'font-style' === $key ) $settings['typography_font_style'] = $val;
				if ( 'text-decoration' === $key ) $settings['typography_text_decoration'] = $val;
			}

			if ( 'text-align' === $key || 'justify-content' === $key || 'float' === $key || 'align-items' === $key || 'align-self' === $key ) {
				$align_val = $val;
				// Map flex and other values to Elementor align values
				if ( strpos( $val, 'flex-start' ) !== false || strpos( $val, 'left' ) !== false || strpos( $val, 'start' ) !== false ) $align_val = 'left';
				elseif ( strpos( $val, 'flex-end' ) !== false || strpos( $val, 'right' ) !== false || strpos( $val, 'end' ) !== false ) $align_val = 'right';
				elseif ( strpos( $val, 'center' ) !== false ) $align_val = 'center';
				elseif ( strpos( $val, 'justify' ) !== false ) $align_val = 'justify';

				// Set multiple potential keys for better compatibility
				if ( ! $is_child || empty( $settings['align'] ) ) $settings['align'] = $align_val;
				if ( ! $is_child || empty( $settings['text_align'] ) ) $settings['text_align'] = $align_val;
				if ( $is_button && ( ! $is_child || empty( $settings['button_align'] ) ) ) {
					$settings['button_align'] = $align_val;
				}
			}

			// Spacing
			if ( 'padding' === $key || 'margin' === $key ) {
				// Avoid overwriting from child wraps
				if ( ! $is_child || empty( $settings[$key] ) || $is_button ) {
					$struct = $this->parse_dimensions( $val );
					if ( $is_button && 'padding' === $key ) {
						$settings['button_padding'] = $struct;
						$settings['padding'] = $struct;
					} else {
						$settings[$key] = $struct;
					}
				}
			}

			// Individual Spacing
			if ( preg_match( '/^(padding|margin)-(top|right|bottom|left)$/i', $key, $m_side ) ) {
				$prop = $m_side[1];
				$side = $m_side[2];
				$target = $is_button && 'padding' === $prop ? 'button_padding' : $prop;

				if ( ! $is_child || ! isset( $settings[$target][$side] ) ) { // Check if specific side is not set
					if ( ! isset( $settings[$target] ) ) $settings[$target] = [ 'unit' => 'px', 'isLinked' => false ];
					if ( preg_match( '/([0-9\.]+)/', $val, $m_val ) ) {
						$settings[$target][$side] = $m_val[1];
					}
				}
			}

			// Borders
			if ( 'border' === $key ) {
				if ( preg_match( '/([0-9\.]+)px/i', $val, $m ) ) {
					$w = $m[1];
					$struct = [ 'unit' => 'px', 'top' => $w, 'right' => $w, 'bottom' => $w, 'left' => $w, 'isLinked' => true ];
					if ( $is_button ) {
						$settings['button_border_width'] = $struct;
						$settings['border_width'] = $struct;
						if ( ! isset( $settings['button_border_border'] ) ) $settings['button_border_border'] = 'solid';
						if ( ! isset( $settings['border_border'] ) ) $settings['border_border'] = 'solid';
					} else {
						$settings['border_width'] = $struct;
						if ( ! isset( $settings['border_border'] ) ) $settings['border_border'] = 'solid';
					}
				}
				if ( preg_match( '/(solid|dashed|dotted|double|none)/i', $val, $m ) ) {
					if ( $is_button ) $settings['button_border_border'] = $m[1];
					else $settings['border_border'] = $m[1];
				}
				if ( preg_match( '/(#[a-f0-9]{3,6}|rgba?\([^\)]+\)|[a-z]+)/i', $val, $m ) ) {
					if ( $is_button ) $settings['button_border_color'] = $m[1];
					else $settings['border_color'] = $m[1];
				}
			}
			if ( 'border-radius' === $key ) {
				$struct = $this->parse_dimensions( $val );
				if ( $is_button ) {
					$settings['button_border_radius'] = $struct;
					$settings['border_radius'] = $struct;
				} else {
					$settings['border_radius'] = $struct;
				}
			}
			if ( preg_match( '/^border-(top|bottom)-(left|right)-radius$/i', $key, $m_rad ) ) {
				$v_side = $m_rad[1]; // top or bottom
				$h_side = $m_rad[2]; // left or right
				$target = $is_button ? 'button_border_radius' : 'border_radius';
				if ( ! isset( $settings[$target] ) ) $settings[$target] = [ 'unit' => 'px', 'isLinked' => false ];
				
				$map = [ 'top_left' => 'top', 'top_right' => 'right', 'bottom_right' => 'bottom', 'bottom_left' => 'left' ];
				$corner = $v_side . '_' . $h_side;
				if ( isset( $map[$corner] ) && preg_match( '/([0-9]+)/', $val, $m_val ) ) {
					$settings[$target][$map[$corner]] = (int) $m_val[1];
				}
			}
			if ( 'border-style' === $key ) {
				if ( $is_button ) $settings['button_border_border'] = $val;
				else $settings['border_border'] = $val;
			}
			if ( 'border-width' === $key && preg_match( '/([0-9]+)px/i', $val, $m ) ) {
				$w = (int) $m[1];
				$struct = [ 'unit' => 'px', 'top' => $w, 'right' => $w, 'bottom' => $w, 'left' => $w, 'isLinked' => true ];
				if ( $is_button ) $settings['button_border_width'] = $struct;
				else $settings['border_width'] = $struct;
			}
			if ( 'border-color' === $key ) {
				if ( $is_button ) $settings['button_border_color'] = $val;
				else $settings['border_color'] = $val;
			}

			// Opacity
			if ( 'opacity' === $key ) {
				$settings['opacity'] = [ 'unit' => 'px', 'size' => (float) $val ];
			}
		}

		// Support for background overlay classes
		if ( strpos( $class_attr, 'elementor-background-overlay' ) !== false ) {
			if ( isset( $settings['background_image']['url'] ) ) {
				$settings['background_overlay_image'] = $settings['background_image'];
				$settings['background_overlay_background'] = 'classic';
				unset( $settings['background_image'] );
			}
			if ( isset( $settings['background_color'] ) ) {
				$settings['background_overlay_color'] = $settings['background_color'];
				$settings['background_overlay_background'] = 'classic';
				unset( $settings['background_color'] );
			}
		}
	}


	/**
	 * Recursively parse a DOM node into an Elementor element array.
	 *
	 * @param DOMElement  $node  The DOM node.
	 * @param DOMXPath    $xpath XPath object.
	 * @param DOMDocument $dom   DOMDocument object.
	 * @return array|null The element array or null if invalid.
	 */
	private function parse_elementor_node( $node, $xpath, $dom ) {
		if ( ! ( $node instanceof DOMElement ) ) {
			return null;
		}

		// Generate a new ID to avoid conflicts
		$id = substr( md5( uniqid( rand(), true ) ), 0, 7 );

		$el_type      = $node->getAttribute( 'data-element_type' );
		$raw_settings = $node->getAttribute( 'data-settings' );

		if ( empty( $id ) || empty( $el_type ) ) {
			return null;
		}

		// Decode HTML entity-encoded JSON settings
		$settings = [];
		if ( ! empty( $raw_settings ) ) {
			$decoded = html_entity_decode( $raw_settings, ENT_QUOTES, 'UTF-8' );
			$parsed  = json_decode( $decoded, true );
			if ( is_array( $parsed ) ) {
				$settings = $parsed;
			}
		}

		$element = [
			'id'       => $id,
			'elType'   => $el_type,
			'settings' => $settings,
			'elements' => [],
			'isInner'  => false,
		];

		$widget_type_raw = $node->getAttribute( 'data-widget_type' );
		$class_attr      = $node->getAttribute( 'class' );
		$is_button       = ( strpos( $widget_type_raw, 'button' ) !== false ) || ( strpos( $class_attr, 'elementor-button' ) !== false );
		$is_heading      = ( strpos( $widget_type_raw, 'heading' ) !== false ) || ( strpos( $class_attr, 'elementor-heading' ) !== false );

		// Improved style extraction from 'style' and external CSS
		$data_id = $node->getAttribute( 'data-id' );
		$extra_css_props = [];
		if ( isset( $this->external_css[$data_id] ) ) {
			// Merge 'main' styles and any matching sub-selectors
			$extra_css_props = isset( $this->external_css[$data_id]['main'] ) ? $this->external_css[$data_id]['main'] : [];
			
			// For buttons, prioritize button sub-selector
			if ( $is_button && isset( $this->external_css[$data_id]['button'] ) ) {
				$extra_css_props = array_merge( $extra_css_props, $this->external_css[$data_id]['button'] );
			}
			// For headings, honor .elementor-heading-title
			if ( $is_heading && isset( $this->external_css[$data_id]['heading'] ) ) {
				$extra_css_props = array_merge( $extra_css_props, $this->external_css[$data_id]['heading'] );
			}
			// Generic widget container fallback
			if ( ! empty( $widget_type_raw ) && isset( $this->external_css[$data_id]['container'] ) ) {
				$extra_css_props = array_merge( $extra_css_props, $this->external_css[$data_id]['container'] );
			}
			// Wrap styles (common for column/section backgrounds)
			if ( isset( $this->external_css[$data_id]['wrap'] ) ) {
				$extra_css_props = array_merge( $extra_css_props, $this->external_css[$data_id]['wrap'] );
			}
			if ( isset( $this->external_css[$data_id]['column_wrap'] ) ) {
				$extra_css_props = array_merge( $extra_css_props, $this->external_css[$data_id]['column_wrap'] );
			}

			// Apply overlay styles if found
			if ( isset( $this->external_css[$data_id]['overlay'] ) ) {
				$overlay_settings = [];
				$this->apply_styles_to_settings( $node, $overlay_settings, $el_type, true, $this->external_css[$data_id]['overlay'] );
				foreach ( $overlay_settings as $ok => $ov ) {
					$settings['background_overlay_' . $ok] = $ov;
				}
				$settings['background_overlay_background'] = 'classic';
			}

			// Final fallback background for the section/column if still empty
			if ( empty( $settings['background_image']['url'] ) && isset( $this->external_css[$data_id]['fallback_background'] ) ) {
				if ( preg_match( '/url\([\'"]?([^\'"]+)[\'"]?\)/i', $this->external_css[$data_id]['fallback_background'], $fb_m ) ) {
					$settings['background_image'] = [ 'url' => $fb_m[1] ];
					$settings['background_background'] = 'classic';
				}
			}
		}
		
		$this->apply_styles_to_settings( $node, $element['settings'], $el_type, false, $extra_css_props );

		// Also check key descendant wraps for backgrounds/overlays
		$wrap_nodes = $xpath->query( './/*[contains(@class, "-wrap") or contains(@class, "elementor-background") or contains(@class, "elementor-widget-container")]', $node );
		foreach ( $wrap_nodes as $wrap ) {
			$this->apply_styles_to_settings( $wrap, $element['settings'], $el_type, true );
		}


		// Try to extract background image from settings if set in data-settings
		if ( isset( $element['settings']['background_image'] ) && is_array( $element['settings']['background_image'] ) && isset( $element['settings']['background_image']['url'] ) ) {
			// Already has it
		} elseif ( isset( $element['settings']['background_image'] ) && is_string( $element['settings']['background_image'] ) ) {
			$element['settings']['background_image'] = [ 'url' => $element['settings']['background_image'] ];
		}

		// Detect if it is an inner section
		if ( 'section' === $el_type ) {
			$parent = $node->parentNode;
			while ( $parent && $parent->nodeType === XML_ELEMENT_NODE ) {
				if ( $parent->hasAttribute( 'data-element_type' ) && 'column' === $parent->getAttribute( 'data-element_type' ) ) {
					$element['isInner'] = true;
					break;
				}
				$parent = $parent->parentNode;
			}
		}

		// Extract column width for columns
		if ( 'column' === $el_type ) {
			$class = $node->getAttribute( 'class' );
			if ( preg_match( '/elementor-col-([0-9]+)/', $class, $matches ) ) {
				$element['settings']['_column_size'] = (int) $matches[1];
				$element['settings']['_inline_size'] = (int) $matches[1];
			} elseif ( preg_match( '/elementor-element-populate/', $class ) ) {
				// Often columns are 100% if they are the only ones
				$element['settings']['_column_size'] = 100;
				$element['settings']['_inline_size'] = 100;
			}
		}

		if ( 'widget' === $el_type ) {
			$widget_type_raw   = $node->getAttribute( 'data-widget_type' );
			$widget_type_parts = explode( '.', $widget_type_raw );
			$element['widgetType'] = isset( $widget_type_parts[0] ) ? $widget_type_parts[0] : 'text-editor';

			$containers = $xpath->query( './/div[contains(@class,"elementor-widget-container")]', $node );
			if ( $containers->length > 0 ) {
				$inner_html = '';
				foreach ( $containers->item( 0 )->childNodes as $child ) {
					$inner_html .= $dom->saveHTML( $child );
				}
				$inner_html = trim( $inner_html );

				switch ( $element['widgetType'] ) {
					case 'heading':
					case 'woostify-heading':
						$heading_els = $xpath->query( './/*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6 or contains(@class, "elementor-heading-title")]', $containers->item( 0 ) );
						if ( $heading_els->length > 0 ) {
							$h_node = $heading_els->item( 0 );
							$element['settings']['title'] = trim( $h_node->textContent );
							$this->apply_styles_to_settings( $h_node, $element['settings'], 'widget' );
						}
						break;
					case 'text-editor':
					case 'editor':
						$element['settings']['editor'] = $inner_html;
						break;
					case 'image-box':
						$img_els = $xpath->query( './/img', $containers->item( 0 ) );
						if ( $img_els->length > 0 ) {
							$element['settings']['image'] = [ 'url' => $img_els->item( 0 )->getAttribute( 'src' ) ];
						}
						$title_els = $xpath->query( './/*[contains(@class, "elementor-image-box-title")]', $containers->item( 0 ) );
						if ( $title_els->length > 0 ) {
							$element['settings']['title_text'] = trim( $title_els->item( 0 )->textContent );
						}
						$desc_els = $xpath->query( './/*[contains(@class, "elementor-image-box-description")]', $containers->item( 0 ) );
						if ( $desc_els->length > 0 ) {
							$element['settings']['description_text'] = trim( $desc_els->item( 0 )->textContent );
						}
						break;
					case 'icon-box':
						$icon_els = $xpath->query( './/*[contains(@class, "elementor-icon-box-icon")]//i', $containers->item( 0 ) );
						if ( $icon_els->length > 0 ) {
							$element['settings']['icon'] = str_replace( 'fa fa-', '', $icon_els->item( 0 )->getAttribute( 'class' ) );
						}
						$title_els = $xpath->query( './/*[contains(@class, "elementor-icon-box-title")]', $containers->item( 0 ) );
						if ( $title_els->length > 0 ) {
							$element['settings']['title_text'] = trim( $title_els->item( 0 )->textContent );
						}
						$desc_els = $xpath->query( './/*[contains(@class, "elementor-icon-box-description")]', $containers->item( 0 ) );
						if ( $desc_els->length > 0 ) {
							$element['settings']['description_text'] = trim( $desc_els->item( 0 )->textContent );
						}
						break;
					case 'button':
						$btn_els = $xpath->query( './/span[contains(@class,"elementor-button-text")] | .//a[contains(@class, "elementor-button")]', $containers->item( 0 ) );
						if ( $btn_els->length > 0 ) {
							$element['settings']['text'] = trim( $btn_els->item( 0 )->textContent );
							// Extract style from the actual button anchor if possible
							$btn_node = $xpath->query( './/a[contains(@class, "elementor-button")]', $containers->item( 0 ) );
							if ( $btn_node->length > 0 ) {
								$this->apply_styles_to_settings( $btn_node->item(0), $element['settings'], 'widget' );
							}
						}
						$link_els = $xpath->query( './/a', $containers->item( 0 ) );
						if ( $link_els->length > 0 ) {
							$element['settings']['link'] = [ 'url' => $link_els->item( 0 )->getAttribute( 'href' ) ];
						}
						break;
					case 'image':
						$img_els = $xpath->query( './/img', $containers->item( 0 ) );
						if ( $img_els->length > 0 ) {
							$element['settings']['image'] = [
								'url' => $img_els->item( 0 )->getAttribute( 'src' ),
								'alt' => $img_els->item( 0 )->getAttribute( 'alt' ),
							];
						}
						break;
					case 'icon':
						$icon_els = $xpath->query( './/i', $containers->item( 0 ) );
						if ( $icon_els->length > 0 ) {
							$element['settings']['icon'] = str_replace( 'fa fa-', '', $icon_els->item( 0 )->getAttribute( 'class' ) );
							$element['settings']['selected_icon'] = [
								'value' => $icon_els->item( 0 )->getAttribute( 'class' ),
								'library' => 'fa-solid',
							];
						}
						break;
					case 'spacer':
						$spacer_els = $xpath->query( './/div[contains(@class, "elementor-spacer-inner")]', $containers->item( 0 ) );
						// Settings usually already has space size from data-settings
						break;
					default:
						// Try to extract content from widget-container if no specific logic
						if ( empty( $element['settings'] ) && ! empty( $inner_html ) ) {
							$element['settings']['content'] = $inner_html;
						}
						break;
				}
			}
			// Important: Ensure widgetType is correctly formatted for Elementor
			$element['widgetType'] = $element['widgetType'];
			
			// Widgets have no child elements
			return $element;
		}

		// Find child nodes
		$child_nodes = null;
		if ( 'section' === $el_type ) {
			// For sections: find child columns
			$child_nodes = $xpath->query( './/*[@data-element_type="column"]', $node );
		} elseif ( 'column' === $el_type || 'container' === $el_type ) {
			// For Columns and Flexbox Containers: find direct Elementor children
			$child_nodes = $xpath->query( './/*[@data-id]', $node );
		}

		if ( $child_nodes ) {
			foreach ( $child_nodes as $child ) {
				// We only want DIRECT children that have a data-element_type or data-id
				$parent_el = $child->parentNode;
				$is_direct = false;
				
				// Traverse up to find the first ancestor with a data-id or data-element_type
				while ( $parent_el && $parent_el->nodeType === XML_ELEMENT_NODE ) {
					if ( $parent_el->hasAttribute( 'data-element_type' ) ) {
						// If the first Elementor ancestor is NOT our current node, then this is not a direct child
						if ( $parent_el->isSameNode( $node ) ) {
							$is_direct = true;
						}
						break;
					}
					$parent_el = $parent_el->parentNode;
				}

				if ( $is_direct ) {
					$child_element = $this->parse_elementor_node( $child, $xpath, $dom );
					if ( $child_element ) {
						$element['elements'][] = $child_element;
					}
				}
			}
		}

		return $element;
	}

	public function select_demo_type() {
		check_ajax_referer( 'woostify_nonce_field' );
		$template_type = $_POST['template_type'];
		$demo_type     = $_POST['demo_type'];
		$demos         = woostify_sites_local_import_files();
		$license_key   = get_option( 'woostify_pro_license_key_status', 'invalid' );
		$demos = $this->get_demo( $template_type );
		$list_demo = [];
		foreach ($demos as $demo) {
			if ( $demo['type'] == $demo_type || '' == $demo_type ) {
				array_push($list_demo, $demo);
			}
		}
		$user_id     = get_current_user_id();
		$usermeta    = get_user_meta( $user_id, 'woostify-favorite-template' );
		$usermeta    = unserialize( $usermeta[0] ); //phpcs:ignore
		$favorite    = array();
		if ( ! empty( $usermeta ) && array_key_exists( $template_type, $usermeta ) ) {
			$favorite = $usermeta[ $template_type ];
		}
		?>

			<?php foreach ( $list_demo as $demo ) : ?>
				<?php
				$types = explode( '__', $demo['type']);

				$class = 'woostify-tempalte-item template-builder-elementor elementor-template-library-template-remote elementor-template-library-template-' . $template_type;
				$class .= ( end( $types ) == 'pro' || $demo_type == 'shop' ) ? ' elementor-template-library-pro-template' : '';
				$class .= $template_type == 'pages' ? ' elementor-template-library-template-page' : '';
				$checked = '';
				$favorite_class = 'eicon-heart-o';
				if ( ! empty( $favorite ) && in_array( $demo['id'], $favorite ) ) {
					$checked = 'checked';
					$favorite_class = 'eicon-heart';
				}
				?>
				<div class="<?php echo esc_attr( $class ); ?>" data-id="<?php echo esc_attr( $demo['id'] ); ?>" data-type="<?php echo esc_attr( $template_type ); ?>">
					<div class="elementor-template-library-template-body">
						<div class="template-screenshot elementor-template-library-template-screenshot" style="background-image: url();">
							<img src="<?php echo esc_url( $demo['import_preview_image_url'] ); ?>" alt="<?php echo esc_attr( $demo['import_file_name'] ); ?>">
							<div class="elementor-template-library-template-preview woostify-template-library-template-preview">
								<i class="eicon-zoom-in" aria-hidden="true"></i>
							</div>
						</div>
					</div>
					<div class="elementor-template-library-template-footer theme-id-container">
						<span class="theme-name"><?php echo esc_html( $demo['import_file_name'] ); ?></span>
						<div class="woostify-template-library-favorite">
							<input type="checkbox" name="favorite" value="<?php echo $template_type . '-' . $demo['id']; ?>" class="woostify-favorite-template-input">
						</div>

					</div>
				</div>
			<?php endforeach ?>

			<?php if ( 0 == count( $list_demo ) ): ?>
				<div class="no-result">
					<span class="alert-text"><?php echo esc_html__( 'No template found', 'woostify-sites-library' ) ?></span>
				</div>
			<?php endif ?>

		<?php

		die();
	}

	public function list_child_page() {
		check_ajax_referer( 'woostify_nonce_field' );
		$id = $_POST['id'];
		$type = $_POST['type'];
		$all_demo = woostify_sites_local_import_files();
		if ( 'blocks' == $type ) {
			$all_demo = woostify_sites_section();
		}
		$demo = $all_demo[$id];
		$types = $this->modal_header_tab();
		?>
			<div class="dialog-header dialog-lightbox-header">
				<div class="elementor-templates-modal__header">
					<div class="elementor-templates-modal__header__logo-area">
						<div id="woostify-template-library-header-preview-back" step="2">
							<i class="eicon-arrow-left" aria-hidden="true"></i>
							<span><?php echo esc_html__( 'Back to Library', 'woostify-sites-library' ) ?></span>
						</div>
					</div>
					<div class="elementor-templates-modal__header__menu-area">
						<div id="woostify-template-library-header-menu" class="woostify-template-library-header-menu">
							<?php
								foreach ($types as $key => $value):
									$active = '';
									if ( $key == $type ):
										$active = ' elementor-active';
									endif;
									?>
								<div class="elementor-component-tab elementor-template-library-menu-item woostify-template-library-menu-item<?php echo esc_attr__( $active ); ?>" data-tab="<?php echo esc_attr( $key ) ?>"><?php echo esc_html($value) ?></div>
							<?php endforeach ?>

						</div>
					</div>
					<div class="elementor-templates-modal__header__items-area">

						<div class="elementor-templates-modal__header__close elementor-templates-modal__header__close--normal elementor-templates-modal__header__item woostify-close-button">

							<i class="eicon-close" aria-hidden="true" title="Close"></i>
							<span class="elementor-screen-only"><?php echo esc_html__( 'Close', 'woostify-sites-library' ) ?></span>
						</div>

					</div>
				</div>

			</div>
			<div id="wooostify-template-library-templates-container" class="wooostify-template-library-templates-container">
				<div class="woostify-template-wrapper">
					<?php foreach ( $demo['page'] as $page ) : ?>
						<?php $is_pro = ( 'pro' == $demo['type'] || $type == 'shop' ) ?>
						<div class="woostify-tempalte-item template-builder-elementor elementor-template-library-template-page elementor-template-library-template-remote <?php echo esc_attr( $is_pro ); ?>" data-page="<?php echo esc_attr( $page['id'] ); ?>" data-id="<?php echo esc_attr( $id ); ?>" data-type="<?php echo esc_attr( $type ); ?>">
							<div class="elementor-template-library-template-body">
								<div class="template-screenshot elementor-template-library-template-screenshot" >
									<img src="<?php echo esc_url( $page['preview'] ); ?>" alt="<?php echo esc_attr( $demo['import_file_name'] ); ?>">
									<div class="elementor-template-library-template-preview woostify-template-library-template-preview">
										<i class="eicon-zoom-in" aria-hidden="true"></i>
									</div>
								</div>
							</div>
							<div class="elementor-template-library-template-footer theme-id-container">
								<span class="theme-name"><?php echo esc_html( $page['title'] ); ?></span>
							</div>
						</div>
					<?php endforeach ?>
				</div>
			</div>
			<?php

			die();
	}

	public function modal_header_tab() {
		return array(
			'header' => __('Header', 'woostify-sites-library'),
			'footer' => __('Footer', 'woostify-sites-library'),
			'shop'   => __('Shop', 'woostify-sites-library'),
			'blocks' => __('Blocks', 'woostify-sites-library'),
			'pages'  => __('Pages', 'woostify-sites-library'),
		);
	}

	public function favorite_template() {
		check_ajax_referer( 'woostify_nonce_field' );
		$data     = $_GET['value'];
		$data     = explode('-', $data);
		$type     = $data[0];
		$id       = $data[1];
		$user_id  = get_current_user_id();
		$usermeta = get_user_meta( $user_id, 'woostify-favorite-template' );
		if ( empty( $usermeta) ) {
			$meta_value = array(
				$type => array( $id ),
			);
			$meta_value = serialize( $meta_value );
			$favorite = add_user_meta( $user_id, 'woostify-favorite-template', $meta_value );
		} else {
			$meta_value = unserialize( $usermeta[0] );
			if ( $meta_value && array_key_exists( $type, $meta_value ) ) {
				if ( in_array( $id, $meta_value[$type]) ) {
					$key = array_search( $id, $meta_value[$type] );
					unset($meta_value[$type][$key]);
				} else {
					array_push( $meta_value[$type], $id );
				}
			} else {
				$meta_value[$type] = array( $id );
			}
			$meta_value = serialize( $meta_value );
			$favorite = update_user_meta( $user_id, 'woostify-favorite-template', $meta_value );
		}
		wp_send_json_success( $favorite );
	}

	public function list_favorite() {
		check_ajax_referer( 'woostify_nonce_field' );
		$user_id   = get_current_user_id();
		$usermeta  = get_user_meta( $user_id, 'woostify-favorite-template' );
		if ( $usermeta ) {
			$usermeta  = unserialize( $usermeta[0] );
		}
		$section   = woostify_sites_section();
		$header    = woostify_sites_header();
		$footer    = woostify_sites_footer();
		$shop      = woostify_sites_shop();
		$pages     = woostify_sites_local_import_files();
		$favorites = array();
		$types     = $this->modal_header_tab();
		?>

		<div class="dialog-widget-content woostify-widget-content dialog-lightbox-widget-content">
			<div class="dialog-header dialog-lightbox-header">
				<div class="elementor-templates-modal__header">
					<div class="elementor-templates-modal__header__logo-area">
						<div class="elementor-templates-modal__header__logo">
							<span class="elementor-templates-modal__header__logo__icon-wrapper e-logo-wrapper" style="background: #4744b7;">
								<i class="eicon-elementor"></i>
							</span>
							<span class="elementor-templates-modal__header__logo__title"><?php echo esc_html__( 'Library', 'woostify-sites-library' ) ?></span>
						</div>

					</div>
					<div class="elementor-templates-modal__header__menu-area">
						<div id="woostify-template-library-header-menu" class="woostify-template-library-header-menu">
							<?php
								foreach ($types as $key => $value):
									$active = '';
									if ( $key == 'pages' ):
										$active = ' elementor-active';
									endif;
									?>
								<div class="elementor-component-tab elementor-template-library-menu-item woostify-template-library-menu-item<?php echo esc_attr__( $active ); ?>" data-tab="<?php echo esc_attr( $key ) ?>"><?php echo esc_html($value) ?></div>
							<?php endforeach ?>

						</div>
					</div>
					<div class="elementor-templates-modal__header__items-area">

						<div class="elementor-templates-modal__header__close elementor-templates-modal__header__close--normal elementor-templates-modal__header__item woostify-close-button">

							<i class="eicon-close" aria-hidden="true" title="Close"></i>
							<span class="elementor-screen-only"><?php echo esc_html__( 'Close', 'woostify-sites-library' ) ?></span>
						</div>

					</div>
				</div>

			</div>
			<div id="wooostify-template-library-templates-container" class="wooostify-template-library-templates-container">
				<div class="woostify-template-library-toolbar" style="display: flex;">
					<div class="elementor-template-library-filter-toolbar">

						<div class="woostify-template-favorite">
							<a href="#" class="woostify-link-favorite"><?php echo esc_html__( 'My Favorites', 'woostify-sites-library' ); ?></a>
						</div>
					</div>

				</div>
				<div class="woostify-template-wrapper">

						<div class="woostify-list-favorite-wrapper">
							<?php if ( ! empty( $usermeta ) ): ?>
								<?php foreach ($usermeta as $type => $data) : ?>
									<?php
									$demo_type = ( 'pages' == $type ) ? ' elementor-type-pages' : ' woostify-template-library-template-preview elementor-type-blocks';
											switch ( $type ) {
												case 'section':
													$favorites = $section;
													break;

												case 'header':
													$favorites = $header;
													break;

												case 'footer':
													$favorites = $footer;
													break;

												case 'shop':
													$favorites = $shop;
													break;

												default:
													$favorites = $pages;
													break;
											}
									?>
									<div class="woostify-favorite-item">
										<div class="item-header">
											<h5 class="type-title"><?php echo esc_html( $type ); ?></h5>
										</div>
										<div class="list-favorite">
											<?php foreach ( $data as $id ) : ?>
												<?php
													$demo = $favorites[$id];
													$types = explode( '__', $demo['type'] );
													$class = 'woostify-tempalte-item template-builder-elementor elementor-template-library-template-remote elementor-template-library-template-' . $type;
													$class .= ( end( $types ) == 'pro' || $type == 'shop' ) ? ' elementor-template-library-pro-template' : '';
													$class .= $type == 'pages' ? ' elementor-template-library-template-page' : '';

													$checked = '';
												?>
												<div class="<?php echo esc_attr( $class ); ?>" data-id="<?php echo esc_attr( $demo['id'] ); ?>" data-type="<?php echo esc_attr( $type ); ?>">
													<div class="elementor-template-library-template-body">
														<div class="template-screenshot elementor-template-library-template-screenshot" >
															<img src="<?php echo esc_url( $demo['import_preview_image_url'] ); ?>" alt="">
															<div class="elementor-template-library-template-preview <?php echo esc_attr( $demo_type ); ?>">
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
								<?php endforeach; ?>
							<?php else : ?>
								<span class="no-favorite">
									<?php echo esc_html__( 'No template found!', 'woostify-sites-library' ) ?>
								</span>
							<?php endif ?>


						</div>

				</div>
			</div>
		</div>

		<?php
		die();
	}

}
/**
 * Kicking this off by calling 'get_instance()' method
 */
Woostify_Sites_Elementor::get_instance();
