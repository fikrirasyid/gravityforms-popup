jQuery(document).ready(function($){
	// Cookie creation
	function set_cookie( name, value, days ) {
	    var expires;

	    if (days) {
	        var date = new Date();
	        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
	        expires = "; expires=" + date.toGMTString();
	    } else {
	        expires = "";
	    }
	    
	    document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/";
	}

	// Display
	function display_gravityforms_popup(){
		$('.gravityforms-popup-block.undisplayed').fadeIn();

		// Remove undisplayed form should be displayed only once
		$('.gravityforms-popup-block').removeClass( 'undisplayed' );

		// Set cookie
		set_cookie( gravityforms_popup_params.cookie_name, gravityforms_popup_params.cookie_value, gravityforms_popup_params.cookie_days );
	}

	// Hide
	function hide_gravityforms_popup(){
		$('.gravityforms-popup-block').fadeOut();
	}	

	// Automatically display after time limit
	function display_gravityforms_popup_after_seconds(){
		var seconds = gravityforms_popup_params.seconds_to_appearance * 1000;

		setTimeout(
			function(){
				display_gravityforms_popup();
			},
			seconds
		);
	}
	display_gravityforms_popup_after_seconds();

	// Initial image UX
	$('#gravityforms-popup .initial-image').click(function(e){
		e.preventDefault();

		$(this).fadeOut( 'fast', function(){

			$('#gravityforms-popup').removeClass( 'has-initial-image' );

		});
	});

	// Close Popup
	$('#gravityforms-popup-close, #gravityforms-popup-modal').click(function(e){
		e.preventDefault();

		hide_gravityforms_popup();
	});

	// Display popup at the bottom of page
	if( gravityforms_popup_params.display_on_bottom_page == 1 ){

		// Execute on scroll
		$(window).scroll(function(){
			// Calculate the height of the page
			var page_height 		= $(document).height();
			var window_height 		= $(window).height();
			var display_on_height 	= page_height - window_height;
			var popup_displayed		= false;
			var window_to_top 		= $(window).scrollTop();

			// Adding more height, assuming the footer ID is #footer
			if( $('#footer').length > 0 ){
				var display_on_height = display_on_height - $('#footer').height();
			}

			if( window_to_top > display_on_height && popup_displayed == false ){
				popup_displayed = true;
				display_gravityforms_popup();
			}
		});
	}

	// Store long-term cookie after form submission
	if( gravityforms_popup_params.form_id > 0 ){
		$(document).on('gform_confirmation_loaded', function(e, form_id){
		   if( form_id == gravityforms_popup_params.form_id ) {

		   		// Set long-term cookie (2 years)
		   		set_cookie( gravityforms_popup_params.cookie_name + '_submitted_' + gravityforms_popup_params.form_id, gravityforms_popup_params.form_id, 730 );
		   }
		});		
	}
});