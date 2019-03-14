(function( $ ) {
	
	/*
	 * SINGLE POST SCREEN
	 */

	// taxonomy metaboxes
	$('.radio-buttons-for-taxonomies').each( function(){

		let this_id = $(this).attr('id'), taxonomyParts, taxonomy;
		taxonomyParts = this_id.split('-');
		taxonomyParts.shift();
		taxonomy = taxonomyParts.join('-');
		let radio_buttons_selectors = $(`#${taxonomy}-all li :radio, #${taxonomy}-pop li :radio`);

		//fix for radio buttons- if click on popular select on all and vice versa
		radio_buttons_selectors.on('change', function(){
				// If a new term is not selected, don't do anything
				if( ! $(this).is(':checked') ){
					return;
				}

				let term_id = $(this).val();
				let current_value_element = `[value=${term_id}]`;
				radio_buttons_selectors.prop('checked',false);
				$(`#${taxonomy}-all li :radio${current_value_element}, #${taxonomy}-pop li :radio${current_value_element}`).prop('checked', true);

		});  //end on radio click

	}); // end taxonomy metaboxes

})( jQuery );
