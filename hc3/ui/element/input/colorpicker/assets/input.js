(function($) {

jQuery(document).on('click', '.hcj-colorpicker-selector', function(event)
{
	var $this = jQuery(this);

	var my_value = jQuery(this).data('color');
	var $container = $this.closest('.hcj-colorpicker-input');

	var $hidden = $container.find('input[type=hidden]');
	var $display = $container.find('.hcj-colorpicker-display');

	$hidden.val( my_value );
	$display.css('background-color', my_value);

	/* close collapse */
	var $collapse_toggler = $container.find('.hc-collapse-toggler');
	if( $collapse_toggler.length ){
		$collapse_toggler.prop('checked', false);
	}

	return false;
});

}());
