
(function($) {
	'use strict';
	// Add Section Area Button Elementor Editer
	if ( elementorCommon ) {
		var addSectionTmpl = $( "#tmpl-elementor-add-section" );
		var listButton = $('.elementor-add-section-area-button');
		console.log( addSectionTmpl );
		console.log( elementorCommon );
		if ( addSectionTmpl.length > 0 ) {
			let actionForAddSection = addSectionTmpl.text();
			let stylesheet = '';
			actionForAddSection = actionForAddSection.replace( '<div class="elementor-add-section-drag-title', stylesheet + '<div class="elementor-add-section-area-button elementor-add-woostify-site-button" style="background-color: #333333;"> <i class="eicon-folder"></i> </div><div class="elementor-add-section-drag-title' );
			addSectionTmpl.text( actionForAddSection );
		}

		elementor.on( "preview:loaded", function() {
			$(elementor.$previewContents[0].body).on( 'click', '.elementor-add-woostify-site-button', function(e) {
				var data     = {
					action: 'woostify_modal_template',//woostify_modal_template
					_ajax_nonce: admin.nonce,
				};

				$.ajax(
					{
						type: 'GET',
						url: admin.url,
						data: data,
						beforeSend: function (response) {
						},
						success: function (response) {
							
							$( '#woostify-sites-modal' ).css('display', 'block');
							$( '#woostify-sites-modal' ).html( response );
						},
					}
				);
			} );
		});

		$('body').on('click', '.woostify-template-library-template-preview', function (e) {
			var theme = $(this);
			var btnBack = $('body').find('#woostify-template-library-header-preview-back');
			var action = $('body').find('#woostify-template-library-header-tools');
			btnBack.css( 'display', 'block' );
			btnBack.siblings().css( 'display', 'none' );
			action.css( 'display', 'block' );
			var id = theme.parents('.woostify-tempalte-item').attr('data-id');

			var data     = {
				action: 'woostify_get_template',//woostify_modal_template
				id: id,
				_ajax_nonce: admin.nonce,
			};

			$.ajax(
				{
					type: 'GET',
					url: admin.url,
					data: data,
					success: function (response) {
						$('body').find('#wooostify-template-library-templates-container').html(response);
					},
				}
			);
		});

		$('body').on( 'click', '#woostify-template-library-header-import', function (e) {
			var id = $( '#woostify-demo-data' ).val();
			var data     = {
				action: 'woostify_import_template',//woostify_modal_template
				id: id,
				_ajax_nonce: admin.nonce,
			};

			$.ajax(
				{
					type: 'POST',
					url: admin.url,
					data: data,
					success: function (response) {
						console.log( response );
					},
				}
			);
		} );

		$('body').on( 'click', '.woostify-close-button', function (e) {
			$( '#woostify-sites-modal' ).css('display', 'none');
		} );
	}

})(jQuery);