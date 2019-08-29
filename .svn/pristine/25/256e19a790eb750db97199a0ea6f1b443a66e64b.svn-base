<?php

function woostify_site_plugin($selected_import_index)
{
		$default = array(
			array(
				'name'     => 'WooCommerce',
				'slug'     => 'woocommerce',
				'required' => true,
			),
			array(
				'name'     => 'Elementor',
				'slug'     => 'elementor',
			),
			array(
				'name'     => 'Contact Form 7',
				'slug'     => 'contact-form-7',
			),
			array(
				'name'     => 'Variation Swatches',
				'slug'     => 'variation-swatches-for-woocommerce',
				'required' => false,
			),
		);
		$added = array();

		// This required Plugin Demmo 1 Fashion, Yoga, Vogue, Lenvision
		if ( $selected_import_index == 0 || $selected_import_index == 1 || $selected_import_index == 2 || $selected_import_index == 3) {
			$added = array(
				array(
					'name'     => 'Instagram Feed',
					'slug'     => 'instagram-feed',
					'required' => false,
				),
			);
		}

		if ( ! empty($added) ) {
			$plugins = array_merge($default, $added);
		} else {
			$plugins = $default;
		}

		$config = array(
			'id'           => 'woostify-sites',                 // Unique ID for hashing notices for multiple instances of TGMPA.
			'default_path' => '',                      // Default absolute path to bundled plugins.
			'menu'         => 'tgmpa-install-plugins', // Menu slug.
			'parent_slug'  => 'plugins.php',            // Parent menu slug.
			'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true,                    // Show admin notices or not.
			'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => false,                   // Automatically activate plugins after installation or not.
			'message'      => '',                      // Message to output right before the plugins table.
		);

		tgmpa( $plugins, $config );
}