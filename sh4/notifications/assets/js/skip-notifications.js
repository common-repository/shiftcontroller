(function($) {

jQuery(document).on( 'click', '.sh4-skip-notifications input[type=checkbox]', function(event)
{
	var $this = jQuery(this);
	var $form = $this.closest('form');
	var action = $form.attr('action');

	hc2_set_loader( $form );

	jQuery.ajax({
		type: 'POST',
		url: action,
		data: $form.serialize(),
		success: function(data, textStatus){
			hc2_unset_loader( $form );
		}
		})
		.fail( function(jqXHR, textStatus, errorThrown){
			hc2_unset_loader( $form );
			alert( 'Ajax Error' );
			console.log( 'Ajax Error: ' + errorThrown + "\n" + jqXHR.responseText );
			location.reload();
			})
		;
});

}());
