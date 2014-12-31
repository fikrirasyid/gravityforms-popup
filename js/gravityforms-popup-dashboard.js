jQuery(document).ready(function($){
	
	// Select image
	$('.button-select-image, .button-change-image').click(function(e){
		e.preventDefault();

		// Variables, variables
		var send_attachment_bkp = wp.media.editor.send.attachment;
		var button = $(this);
		var name = button.attr( 'data-name' );

		// Setup Media Uploader Properties
		media_frame = wp.media.frames.customHeader = wp.media({
			multiple : false,
			library : { type : 'image' },
		});		

		// Open Media Uploader
		media_frame.open();

		// Select Media
		media_frame.on('select', function(){
			var attachment = media_frame.state().get('selection').first().toJSON();

			// Update preview
			$('.preview-image[data-name="'+name+'"]').html('<img src="'+attachment.url+'" style="width:100%;">');

			// Update media ID input
			$('#image_id_' + name ).val( attachment.id );

			// Update media src input
			$('#image_src_' + name ).val( attachment.url );

			// Hiding appropriate button
			button.hide();
			$('.button-change-image[data-name="'+name+'"], .button-delete-image[data-name="'+name+'"]').show();
		});		
	});

	// Delete image
	$('.button-delete-image').click( function(e){
		e.preventDefault();

		var button = $(this);
		var name = button.attr( 'data-name' );	

		// Empty preview
		$('.preview-image[data-name="'+name+'"]').html('');

		// Empty media ID input
		$('#image_id_' + name ).val( '' );

		// Empty media src input
		$('#image_src_' + name ).val( '' );

		// Hiding / showing appropriate buttons
		$('.button-select-image[data-name="'+name+'"]').show();
		$('.button-change-image[data-name="'+name+'"], .button-delete-image[data-name="'+name+'"]').hide();

	});

});