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
		var seconds = gravityforms_popup_params.second_until_appearance * 1000;

		setTimeout(
			function(){
				display_gravityforms_popup();
			},
			seconds
		);
	}
	display_gravityforms_popup_after_seconds();


	// Close Popup
	$('#gravityforms-popup-close, #gravityforms-popup-modal').click(function(e){
		e.preventDefault();

		hide_gravityforms_popup();
	});
});