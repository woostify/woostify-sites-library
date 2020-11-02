
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
			console.log(actionForAddSection);
			actionForAddSection = actionForAddSection.replace( '<div class="elementor-add-section-drag-title', stylesheet + '<div class="elementor-add-section-area-button elementor-add-woostify-site-button"> <i class="eicon-folder"></i> </div><div class="elementor-add-section-drag-title' );
			addSectionTmpl.text( actionForAddSection );
		}

		elementor.on( "preview:loaded", function() {
			console.log(elementor);
			console.log(elementor.$previewContents[0].body);

			$(elementor.$previewContents[0].body).on( 'click', '.elementor-add-woostify-site-button', function(e) {
				alert('ggg');
			} );
		});
	}



})(jQuery);