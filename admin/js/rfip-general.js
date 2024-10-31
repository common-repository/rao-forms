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
     $(function() {
		$( ".nav-tab" ).hover(
			function() {   
			 var title = $(this).attr("data-title");
			 if(title == "")
			 return false;
			  $('<div/>', {
				  text: title,
				  class: 'box'
			  }).appendTo(this); 
			}, function() {
			  $(document).find("div.box").remove();
			}
		  );
		  
        $('#rfb_authorize_api').on("click",function(e){
            $("#rfb_authorize_api").attr("disabled",true);
            $(".status").removeClass("success error");
            $(".status").html("");
            e.preventDefault();
            var ajax_form_data = $('#authorize_api').serialize();
            $.ajax({
                url:ajaxurl,
                type: "GET",
                data: ajax_form_data
            })
            .done( function( response ) {
                var response_data = JSON.parse(response.data);
                $("#rfb_authorize_api").attr("disabled",false);
                if(response_data.status) {
                    $(".status").addClass("success");
                } else {
                    $(".status").addClass("error");
                }
                $(".status").html(response_data.message);
            })
        });
    });
})( jQuery );
