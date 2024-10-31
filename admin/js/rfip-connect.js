(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

     $(document).ready(function(){
		$(document).on('change','.rao-options-selected',function(e){
			var connection_id = $(this).parents(".connection-data").attr("id");
			$('#'+connection_id).find('.edit-icon').attr("disabled","disabled");
			$('#'+connection_id).find('.edit-icon svg').css("background","#636c7f");
			var provider_form_id = $('#'+connection_id).find('.provider-form-id').attr("data-provider-form-id");
			var rao_form_id = $('#'+connection_id).find('.rao-options-selected').val();
			var form_provider = $("#form_provider").val();
			
			$.ajax({
				type: "POST",
				data: {
					action: "edit_form_connection",
					"id": connection_id,
					"rao_form_id": rao_form_id,
					"provider_form_id": provider_form_id,
					"form_provider":form_provider
				},
				dataType: "json",
				url: raoforms.ajaxurl,
				success: function(ret_data) {
					$('#'+connection_id).find('.edit-icon').removeAttr("disabled");
					$('#'+connection_id).find('.edit-icon svg').removeAttr("style");
					$('#'+connection_id).find('.rao-options-selected').attr("disabled","disabled");
					if(ret_data.success){
						var success_msg = ret_data.data;
						$('.raoforms-success-notice').html('<p>'+success_msg+'</p>').removeAttr('style');
					} else {
						var err_msg = ret_data.data;
						$('.raoforms-error-notice').html('<p>'+err_msg+'</p>').removeAttr('style');
					}
				}
			});
		});
		$(document).on('click', '.edit-icon',function(e){
			e.preventDefault();
			var connection_id = $(this).parents(".connection-data").attr("id");
			$('.raoforms-error-notice').fadeOut();
			$('.raoforms-success-notice').fadeOut();
			$('#'+connection_id).find('.rao-options-selected').removeAttr("disabled");
			
		});

		$(document).on('click', '.minus-icon', function(e){
			e.preventDefault();
			var connection_id = $(this).parents(".connection-data").attr("id");
			var provider_form_id = $('#'+connection_id).find('.provider-form-id').attr("data-provider-form-id");
			
			var form_provider = $("#form_provider").val();
			$('.raoforms-error-notice').fadeOut();
			$('.raoforms-success-notice').fadeOut();
			$.ajax({
				type: "POST",
				data: {
					action: "remove_form_connection",
					'id': connection_id,
					"provider_form_id": provider_form_id,
					"form_provider": form_provider
				},
				dataType: "json",
				url: raoforms.ajaxurl,
				success: function(ret_data) {
					if(ret_data.success){
						$('#'+connection_id).remove();
					} else {
						var err_msg = ret_data.data;
						$('.raoforms-error-notice').html('<p>Some error while remmoving the connections</p>').fadeIn();
					}

					if ( $.trim( $('.connected-forms').text() ).length == 0 ) {
						$('.page-title').fadeOut();
					}
					
				}
			});
		});
        $(document).on('click', '.plus-icon', function(e){
            e.preventDefault();
			$('.raoforms-error-notice').fadeOut();
			$('.raoforms-success-notice').fadeOut();
            var provider_form_id = $('#provider-options').val();
            var rao_form_id = $('#rao-options').val();
			var provider_form_title = $('#provider-options option:selected').text();
			var rao_form_title = $('#rao-options option:selected').text();
			var form_provider = $("#form_provider").val();
            $.ajax({
                type: "POST",
                data: {
                    action: "add_form_connection",
                    provider_form_id: provider_form_id,
                    rao_form_id: rao_form_id,
					form_provider: form_provider
                },
                dataType: "json",
                url: raoforms.ajaxurl,
                success: function(ret_data){
					$('#provider-options').val("");
					$('#rao-options').val("");
                    if(ret_data.success) {
					$('.page-title').fadeIn();
                    var new_insert = $('.dummy')
									.clone()
									.attr('id','connection-'+ret_data.data)
									.removeAttr('style')
									.removeClass('dummy')
									//$(new_insert).find('.rao-form-id').val(rao_form_title).trigger('change');			
									$(new_insert).find('.provider-form-id').val(provider_form_title);
									$(new_insert).find('.provider-form-id').attr("data-provider-form-id",provider_form_id);
									//$(new_insert).find('.provider-options-selected option[value="'+provider_form_id+'"]').attr("selected","selected");
									$(new_insert).find('.rao-options-selected option[value="'+rao_form_id+'"]').attr("selected","selected");

									

					$('.connected-forms').append(new_insert);
					} else {
						var err_msg = ret_data.data;
						$('.raoforms-error-notice').html('<p>'+err_msg+'</p>').removeAttr('style');
					}
                }
            });
        }); 
    });

})( jQuery );
