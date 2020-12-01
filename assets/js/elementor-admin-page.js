
(function($) {
	'use strict';
	// Add Section Area Button Elementor Editer
	if ( elementorCommon ) {
		var addSectionTmpl = $( "#tmpl-elementor-add-section" );
		var listButton = $('.elementor-add-section-area-button');
		var index = 0;
		if ( addSectionTmpl.length > 0 ) {
			let actionForAddSection = addSectionTmpl.text();
			let stylesheet = '';
			actionForAddSection = actionForAddSection.replace( '<div class="elementor-add-section-drag-title', stylesheet + '<div class="elementor-add-section-area-button elementor-add-woostify-site-button" style="background-color: #333333;"> <i class="eicon-folder"></i> </div><div class="elementor-add-section-drag-title' );
			addSectionTmpl.text( actionForAddSection );
		}


		elementor.on( "preview:loaded", function() {
			var editer = elementor.$previewContents[0].body;
			$(editer).on( 'click', '.elementor-add-woostify-site-button', function(e) {
				showTemplate('pages');
				let add_section = $( this ).closest( '.elementor-add-section' );
				if ( add_section.hasClass( 'elementor-add-section-inline' ) ) {

					index = add_section.prevAll().length;
				} else {
					index = add_section.prev().children().length;
				}
			} );


			//Import template
			$('body').on( 'click', '#woostify-template-library-header-import', function (e) {
				var id = $( '#woostify-demo-data' ).val();
				var type = $('#woostify-demo-type').val();
				console.log( index );

				var btn = $(this);
				var data     = {
					action: 'woostify_import_template',//woostify_modal_template
					id: id,
					type: type,
					post_id: admin.post_id,
					_ajax_nonce: admin.nonce,
				};

				let templateModel = new Backbone.Model({
					getTitle() {
						return data['title']
					},
				});
				let page_settings = '';
				let api_url = '';

				$.ajax(
					{
						type: 'POST',
						url: admin.url,
						data: data,
						beforeSend: function (response) {
							btn.addClass('loading');
						},
						success: function (response) {
							console.log( response );
							console.log(index);
							btn.removeClass('loading');
							var page_content = response.data;
							if ( undefined !== page_content && '' !== page_content ) {
								if ( undefined != $e && 'undefined' != typeof $e.internal ) {
									elementor.channels.data.trigger('template:before:insert', templateModel);
									elementor.getPreviewView().addChildModel( page_content, { at : index } || {} );
									elementor.channels.data.trigger('template:after:insert', {});
									$e.internal( 'document/save/set-is-modified', { status: true } )
								} else {
									elementor.channels.data.trigger('template:before:insert', templateModel);
									elementor.getPreviewView().addChildModel( page_content, { at : index } || {} );
									elementor.channels.data.trigger('template:after:insert', {});
									elementor.saver.setFlagEditorChange(true);
								}
							};
							$( '#woostify-sites-modal' ).css('display', 'none');
						},
						error: function(e){
							console.log(e);
							alert( 'Something went wrong, try logging in!' );
						}
					}
				);
			} );

			$('body').on( 'click', '.woostify-template-library-menu-item', function (e) {
				e.preventDefault();
				var tab = $(this);
				var type = tab.data('tab');
				showTemplate(type, tab);
			} );

		});


		// Show Preview template
		$('body').on('click', '.woostify-template-library-template-preview', function (e) {
			var theme = $(this);
			var btnBack = $('body').find('#woostify-template-library-header-preview-back');
			var action = $('body').find('#woostify-template-library-header-tools');
			btnBack.css( 'display', 'block' );
			btnBack.siblings().css( 'display', 'none' );
			action.css( 'display', 'block' );
			$('.woostify-sites-modal').find('.elementor-templates-modal__header__menu-area').css('display', 'none');
			var id = theme.parents('.woostify-tempalte-item').attr('data-id');
			var type = theme.parents('.woostify-tempalte-item').attr('data-type');
			var data     = {
				action: 'woostify_get_template',//woostify_modal_template
				id: id,
				type: type,
				_ajax_nonce: admin.nonce,
			};

			$.ajax(
				{
					type: 'GET',
					url: admin.url,
					data: data,
					beforeSend: function (response) {
						$( action ).find('.elementor-templates-modal__header__item').prop("disabled",true);
					},
					success: function (response) {
						$('body').find('#wooostify-template-library-templates-container').html(response);
						$( action ).find('.elementor-templates-modal__header__item').prop("disabled",false);
					},
				}
			);
		});



		// Close modal template
		$('body').on( 'click', '.woostify-close-button', function (e) {
			$( '#woostify-sites-modal' ).css('display', 'none');
		} );


		function showTemplate(type, tab = null) {
			var data     = {
				action: 'woostify_modal_template',//woostify_modal_template
				type: type,
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
						if ( tab ) {
							console.log(tab.parent().find('.elementor-active'));
							tab.parent().find('.elementor-active').removeClass('active');
							tab.addClass('active');
						}
					},
				}
			);
		}

		function sectionTemplate() {
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
		}
	}

})(jQuery);