<?php
/**
 * Define the demo import files (local files).
 *
 * You have to use the same filter as in above example,
 * but with a slightly different array keys: local_*.
 * The values have to be absolute paths (not URLs) to your import files.
 * To use local import files, that reside in your theme folder,
 * please use the below code.
 * Note: make sure your import files are readable!
 */
function woostify_sites_local_import_files() {
	return array(
		array(
			'id'                           => 0,
			'import_file_name'             => 'Fashion',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-1/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-1/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-1/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-1/demo-11.jpg',
			'import_notice'                => __( 'After you import this demo, you will have to setup the slider separately.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/fashion1/',
			'homepage'                     => 'Fashion',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Header Primary',
			'footer_menu'                  => 'Footer',
			'type'                         => 'free',
			'page_builder'                 => 'elementor',
			'font_page'                    => 13,
			'page'                         => array(
				'13' => array(
					'title'   => 'Home',
					'id'      => 13,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-1/demo-11.jpg',

				),
				'201' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-1/contact.png',
				),
			),
		),
		array(
			'id'                           => 1,
			'import_file_name'             => 'Yoga',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-2/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-2/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-2/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-2/demo-2.jpg',
			'import_notice'                => __( 'After you import this demo, you will have to setup the slider separately.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/fashion2/',
			'homepage'                     => 'Woostify',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'footer_menu'                  => 'Footer Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 11,
			'page'                         => array(
				'11' => array(
					'title'   => 'Home',
					'id'      => 11,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-2/demo-2.jpg',

				),
				'201' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-2/contact.jpg',
				),
				'199' => array(
					'title'   => 'About Us',
					'id'      => 199,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-2/about.jpg',
				),
			),
		),
		array(
			'id'                           => 2,
			'import_file_name'             => 'Voge - Fashion',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-3/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-3/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-3/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-3/demo-3.jpg',
			'import_notice'                => __( 'After you import this demo, you will have to setup the slider separately.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/fashion3/',
			'homepage'                     => 'Woostify',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'footer_menu'                  => 'Footer Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 11,
			'page'                         => array(
				'11' => array(
					'title'   => 'Home',
					'id'      => 11,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-3/demo-3.jpg',

				),
				'201' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-3/contact.jpg',
				),
				'199' => array(
					'title'   => 'About Us',
					'id'      => 199,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-3/about.jpg',
				),
			),
		),
		array(
			'id'                           => 3,
			'import_file_name'             => 'Lensvision - sunglasses',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-4/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-4/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-4/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-4/demo-4.jpg',
			'import_notice'                => __( 'After you import this demo, you will have to setup the slider separately.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/fashion4/',
			'homepage'                     => 'Woostify',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'footer_menu'                  => 'Footer Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 11,
			'page'                         => array(
				'11' => array(
					'title'   => 'Home',
					'id'      => 11,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-4/demo4.png',

				),
				'201' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-4/contact.jpg',
				),
				'199' => array(
					'title'   => 'About Us',
					'id'      => 199,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-4/about.jpg',
				),
			),
		),
		array(
			'id'                           => 4,
			'import_file_name'             => 'Shoebox',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-5/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-5/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-5/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-5/demo-5.jpg',
			'import_notice'                => __( 'After you import this demo, you will have to setup the slider separately.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/',
			'homepage'                     => 'Woostify',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'footer_menu'                  => 'Footer Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 886,
			'page'                         => array(
				'886' => array(
					'title'   => 'Home',
					'id'      => 886,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-5/demo-5.jpg',

				),
				'201' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-5/contact.jpg',
				),
				'199' => array(
					'title'   => 'About Us',
					'id'      => 199,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-5/about.jpg',
				),
			),
		),
		array(
			'id'                           => 5,
			'import_file_name'             => 'Cosmetica - Cosmetic',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-6/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-6/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-6/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-6/demo-6.png',
			'import_notice'                => __( 'After you import this demo, you will have to setup the slider separately.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/cosmetica/',
			'homepage'                     => 'Woostify',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'footer_menu'                  => 'Footer Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 11,
			'page'                         => array(
				'11' => array(
					'title'   => 'Home',
					'id'      => 11,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-6/demo-6.png',

				),
				'201' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-6/contact.jpg',
				),
				'199' => array(
					'title'   => 'About Us',
					'id'      => 199,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-6/about.jpg',
				),
			),
		),
		array(
			'id'                           => 6,
			'import_file_name'             => 'Ankle - Fashion',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-7/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-7/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-7/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-7/demo-7.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/ankle/',
			'homepage'                     => 'Homepage',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'main menu',
			'footer_menu'                  => '',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 896,
			'page'                         => array(
				'896' => array(
					'title'   => 'Home',
					'id'      => 896,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-7/demo-7.png',

				),
				'790' => array(
					'title'   => 'Contact',
					'id'      => 790,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-7/contact.jpg',
				),
				'740' => array(
					'title'   => 'About Us',
					'id'      => 740,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-7/about.jpg',
				),
			),
		),
		array(
			'id'                           => 7,
			'import_file_name'             => 'Pezshop - Pet Care',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-8/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-8/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-8/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-8/demo-7.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/pezshop/',
			'homepage'                     => 'Woostify',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'footer_menu'                  => 'Footer Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 11,
			'page'                         => array(
				'1' => array(
					'title'   => 'Home',
					'id'      => 11,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-8/demo-7.png',

				),
				'2' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-8/contact.jpg',
				),
				'3' => array(
					'title'   => 'About Us',
					'id'      => 199,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-8/about.jpg',
				),
			),
		),
		array(
			'id'                           => 8,
			'import_file_name'             => 'Watch - Watch Shop',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-9/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-9/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-9/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-9/demo-9.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/watch/',
			'homepage'                     => 'Homepage',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Main Menu',
			'footer_menu'                  => '',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 1403,
			'page'                         => array(
				'1' => array(
					'title'   => 'Home',
					'id'      => 1403,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-9/demo-9.png',

				),
				'2' => array(
					'title'   => 'Contact',
					'id'      => 790,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-9/contact.jpg',
				),
				'3' => array(
					'title'   => 'About Us',
					'id'      => 740,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-9/about.jpg',
				),
			),
		),
		array(
			'id'                           => 9,
			'import_file_name'             => 'Hippine - Jewelry ',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-10/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-10/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-10/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-10/demo-10.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/hippine/',
			'homepage'                     => 'Homepage',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'main menu',
			'footer_menu'                  => '',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 1600,
			'page'                         => array(
				'1' => array(
					'title'   => 'Home',
					'id'      => 1600,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-10/demo-10.png',

				),
				'2' => array(
					'title'   => 'Contact',
					'id'      => 790,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-10/contact.jpg',
				),
				'3' => array(
					'title'   => 'About Us',
					'id'      => 740,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-10/about.jpg',
				),
			),
		),
		array(
			'id'                           => 10,
			'import_file_name'             => 'Furnito - Furniture',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-11/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-11/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-11/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-11/demo-11.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/furnito/',
			'homepage'                     => 'Woostify',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'footer_menu'                  => 'Footer Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 11,
			'page'                         => array(
				'1' => array(
					'title'   => 'Home',
					'id'      => 11,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-11/demo-11.png',

				),
				'2' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-11/contact.jpg',
				),
				'3' => array(
					'title'   => 'About Us',
					'id'      => 199,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-11/about.jpg',
				),
			),
		),
		array(
			'id'                           => 11,
			'import_file_name'             => 'Pizza',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-12/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-12/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-12/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-12/demo-12.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/pizza/',
			'homepage'                     => 'Woostify',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'footer_menu'                  => 'Footer Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 11,
			'page'                         => array(
				'1' => array(
					'title'   => 'Home',
					'id'      => 11,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-12/demo-12.png',

				),
				'2' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-12/contact.jpg',
				),
				'3' => array(
					'title'   => 'About Us',
					'id'      => 199,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-12/about.jpg',
				),
			),
		),
		array(
			'id'                           => 12,
			'import_file_name'             => 'Urbanstyle',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-13/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-13/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-13/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-13/demo-13.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/urbanstyle/',
			'homepage'                     => 'Fashion',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Header Primary',
			'footer_menu'                  => 'Footer',
			'type'                         => 'free',
			'page_builder'                 => 'elementor',
			'font_page'                    => 13,
			'page'                         => array(
				'13' => array(
					'title'   => 'Home',
					'id'      => 13,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-13/demo-13.png',

				),
				'201' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-13/contact.jpg',
				),
			),
		),
		array(
			'id'                           => 13,
			'import_file_name'             => 'Randy',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-14/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-14/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-14/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-14/demo-14.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/randy/',
			'homepage'                     => 'Woostify',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'footer_menu'                  => 'Footer Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 11,
			'page'                         => array(
				'1' => array(
					'title'   => 'Home',
					'id'      => 11,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-14/demo-14.png',

				),
				'2' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-14/contact.jpg',
				),
				'3' => array(
					'title'   => 'About Us',
					'id'      => 1347,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-14/about.jpg',
				),
			),

		),
		array(
			'id'                           => 14,
			'import_file_name'             => 'Mega Shop',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-15/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-15/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-15/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-15/demo-15.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/megashop/',
			'homepage'                     => 'Woostify',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'vertical_menu'                => 'Menu Vertical',
			'footer_menu'                  => 'Footer Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 11,
			'page'                         => array(
				'1' => array(
					'title'   => 'Home',
					'id'      => 11,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-15/demo-15.png',

				),
				'2' => array(
					'title'   => 'Contact',
					'id'      => 201,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-15/contact.jpg',
				),
				'3' => array(
					'title'   => 'About Us',
					'id'      => 1347,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-15/about.jpg',
				),
			),
		),
		array(
			'id'                           => 15,
			'import_file_name'             => 'Auto Car',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-16/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-16/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-16/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-16/demo-16.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/autocar/',
			'homepage'                     => 'Home',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'vertical_menu'                => 'Menu Vertical',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 556,
			'page'                         => array(
				'1' => array(
					'title'   => 'Home',
					'id'      => 556,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-16/demo-16.png',

				),
				'2' => array(
					'title'   => 'Contact',
					'id'      => 473,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-16/contact.jpg',
				),
				'3' => array(
					'title'   => 'About Us',
					'id'      => 398,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-16/about.jpg',
				),
			),
		),
		array(
			'id'                           => 16,
			'import_file_name'             => 'Meins',
			'local_import_file'            => WOOSTIFY_SITES_DIR . 'demos/demo-17/demo-content.xml',
			'local_import_widget_file'     => WOOSTIFY_SITES_DIR . 'demos/demo-17/widgets.wie',
			'local_import_customizer_file' => WOOSTIFY_SITES_DIR . 'demos/demo-17/customizer.dat',
			'import_preview_image_url'     => WOOSTIFY_SITES_URI . 'demos/demo-17/demo-17.png',
			'import_notice'                => __( 'After you import this demo, you should update permalink.', 'woostify' ),
			'preview_url'                  => 'https://demo.woostify.com/meins/',
			'homepage'                     => 'Home',
			'blog_page'                    => 'Blog',
			'primary_menu'                 => 'Primary Menu',
			'type'                         => 'pro',
			'page_builder'                 => 'elementor',
			'font_page'                    => 182,
			'page'                         => array(
				'1' => array(
					'title'   => 'Home',
					'id'      => 182,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-17/demo-17.png',

				),
				'2' => array(
					'title'   => 'Contact',
					'id'      => 305,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-17/contact.jpg',
				),
				'3' => array(
					'title'   => 'About Us',
					'id'      => 232,
					'preview' => WOOSTIFY_SITES_URI . 'demos/demo-17/about.jpg',
				),
			),
		),

	);
}
add_filter( 'woostify_sites_import_files', 'woostify_sites_local_import_files' );



function woostify_register_query_vars( $vars ) {
	$vars[] = 'p';
	return $vars;
}
add_filter( 'query_vars', 'woostify_register_query_vars' );


function woostify_sites_section() {
	return array(
		array(
			'id'                       => 0,
			'import_file_name'         => 'Test Section',
			'import_preview_image_url' => WOOSTIFY_SITES_URI . 'demos/demo-1/demo-11.jpg',
			'preview_url'              => 'https://travelcation.boostifythemes.com/',
			'homepage'                 => 'Fashion',
			'type'                     => 'free',
			'page_builder'             => 'elementor',
			'font_page'                => 2000,
		),
	);
}

function woostify_sites_footer() {
	return array(
		array(
			'id'                       => 0,
			'import_file_name'         => 'Test footer',
			'import_preview_image_url' => WOOSTIFY_SITES_URI . 'demos/demo-1/demo-11.jpg',
			'preview_url'              => 'https://travelcation.boostifythemes.com/',
			'homepage'                 => 'Fashion',
			'type'                     => 'free',
			'page_builder'             => 'elementor',
			'font_page'                => 2000,
		),
	);
}



function woostify_sites_header() {
	return array(
		array(
			'id'                       => 0,
			'import_file_name'         => 'Test Header',
			'import_preview_image_url' => WOOSTIFY_SITES_URI . 'demos/demo-1/demo-11.jpg',
			'preview_url'              => 'https://travelcation.boostifythemes.com/',
			'homepage'                 => 'Fashion',
			'type'                     => 'free',
			'page_builder'             => 'elementor',
			'font_page'                => 2000,
		),
	);
}

function woostify_sites_shop() {
	return array(
		array(
			'id'                       => 0,
			'import_file_name'         => 'Test shop',
			'import_preview_image_url' => WOOSTIFY_SITES_URI . 'demos/demo-1/demo-11.jpg',
			'preview_url'              => 'https://travelcation.boostifythemes.com/',
			'homepage'                 => 'Fashion',
			'type'                     => 'free',
			'page_builder'             => 'elementor',
			'font_page'                => 149,
			'contact_form'             => WOOSTIFY_SITES_URI . 'demos/test/wpcf7.csv',
		),
	);
}


add_action( 'rest_api_init', 'woostify_create_api_posts_meta_field' );

function woostify_create_api_posts_meta_field() {

	// register_rest_field ( 'name-of-post-type', 'name-of-field-to-return', array-of-callbacks-and-schema() )
	register_rest_field( 'page', 'post-meta', array(
		'get_callback' => 'woostify_get_post_meta_for_api',
		'schema'       => null,
		)
	);

	register_rest_field( 'btf_builder', 'post-meta', array(
	   'get_callback'    => 'woostify_get_post_meta_for_api',
	   'schema'          => null,
		)
	);
}

function woostify_get_post_meta_for_api( $object ) {
	//get the id of the post object array
	$post_id = $object['id'];

	//return the post meta
	return get_post_meta($post_id);
}

add_action( 'template_redirect', 'woostify_collect_post_id' );

function woostify_collect_post_id()
{
	static $id = 0;

	if ( 'template_redirect' === current_filter() && is_singular() )
		$id = get_the_ID();

	return $id;
}


function woostify_demo_template() {
	# code...
}