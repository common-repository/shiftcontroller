(function($) {
var self = this;

var $calendar;
var $details;
var $detailsContent;
var $detailsCloser;

document.addEventListener('DOMContentLoaded', function()
{
	$calendar = jQuery('#sh4-shifts-calendar');
	$details = jQuery('#sh4-shifts-details');
	$detailsContent = jQuery('#sh4-shifts-details-content');
	$detailsCloser = jQuery('#sh4-shifts-details-closer');

	var $toFlash = $calendar.find('.hc-flash');
	$toFlash.each( function(){
		var $el = jQuery(this);
		jQuery('html, body').animate({
			scrollTop: $el.offset().top - 40,
		});
		for( var ii = 1; ii <= 3; ii++ ){
			$el.fadeOut(200).fadeIn(200);
		}
	});

	$calendar.on( 'click', '.sh4-shift-widget a.sh4-widget-loader', function(e)
	{
		$this = jQuery(this);

		var $waiter = $this.first();
		hc2_set_loader( $waiter );

		var href = hc2MakeAjaxHref( $this.attr('href') );

		jQuery.ajax({
			type: 'GET',
			url: href,
			// data: $form.serialize(),
			success: function(data, textStatus){
				hc2_unset_loader( $waiter );

				$calendar.hide();
				$detailsContent.html( data );
				$detailsContent.trigger('loaded');
				$details.show();

				jQuery('html, body').animate({
					scrollTop: $details.offset().top - 40,
				});
			}
			})
			.fail( function(jqXHR, textStatus, errorThrown){
				hc2_unset_loader( $waiter );
				alert( 'Ajax Error' );
				console.log( 'Ajax Get: ' + href );
				console.log( 'Ajax Error: ' + errorThrown + "\n" + "status: " + textStatus + "\n" + "response: " + jqXHR.responseText );
				// location.reload();
				})
			;
		return false;
	});

// links within ajax loaded content
	$detailsContent.on( 'click', 'a:not(.hcj2-ajax-loader)', function(e)
	{
		$this = jQuery(this);
		var href = $this.attr('href');
		if( '#' == href ){
			return false;
		}

	// kind of trick, if it doesn't contain our 'hca=' part then skip
		if( ! href.includes('hca=') ){
			return true;
		}

		var $waiter = $details;
		hc2_set_loader( $waiter );

		href = hc2MakeAjaxHref( href );

		jQuery.ajax({
			type: 'GET',
			url: href,
			// data: $form.serialize(),
			success: function(data, textStatus){
				hc2_unset_loader( $waiter );
				$detailsContent.html( data );
				$detailsContent.trigger('loaded');
			}
			})
			.fail( function(jqXHR, textStatus, errorThrown){
				hc2_unset_loader( $waiter );
				alert( 'Ajax Error' );
				console.log( 'Ajax Error: ' + errorThrown + "\n" + "status: " + textStatus + "\n" + "response: " + jqXHR.responseText );
				// location.reload();
				})
			;
		return false;
	});

// post forms
	$detailsContent.on( 'click', 'input[type=submit],button[type=submit]', function(e)
	{
		var $btn = jQuery(this);
		var formAction = $btn.attr('formaction');

		if( (typeof formAction === "undefined") || (formAction === '') ){
			// do nothing
		}
		else {
			// e.preventDefault();
			var $form = $btn.closest('form');
			$form.attr('action', formAction);
		}
	});

	$detailsContent.on( 'submit', 'form', function(e)
	{
		/* stop form from submitting normally */
		e.preventDefault(); 

		var $thisForm = jQuery(this);

		var $waiter = $details;
		hc2_set_loader( $waiter );
		var href = $thisForm.attr('action');
		var formData = $thisForm.serialize();

// console.log( 'POST:' + href );

		jQuery.ajax({
			type: 'POST',
			url: href,
			data: formData,
			success: function(data, textStatus){
				var calendarHref = $calendar.data('src');
				calendarHref = hc2MakeAjaxHref( calendarHref );

				if( calendarHref ){
					jQuery.ajax({
						type: 'GET',
						url: calendarHref,
						// data: $form.serialize(),
						success: function(data, textStatus){
							hc2_unset_loader( $waiter );
							$calendar.html( data );
							$calendar.trigger('loaded');
							$details.hide();
							$calendar.show();
						}
						})
						.fail( function(jqXHR, textStatus, errorThrown){
							hc2_unset_loader( $waiter );
							alert( 'Ajax Error' );
							console.log( 'Ajax Error: ' + errorThrown + "\n" + "status: " + textStatus + "\n" + "response: " + jqXHR.responseText );
							// location.reload();
							})
						;
				}
				else {
					hc2_unset_loader( $waiter );
					location.reload();
				}
			}
			})
			.fail( function(jqXHR, textStatus, errorThrown){
				hc2_unset_loader( $waiter );
				alert( 'Ajax Error' );
				console.log( 'Ajax Error: ' + errorThrown + "\n" + "post to: " + href + "\n" + "status: " + textStatus + "\n" + "response: " + jqXHR.responseText );
				// location.reload();
				})
			;
		return false;
	});

	$detailsContent.on('loaded', function(e)
	{
		var $timeRangeInputs = jQuery(this).find( '.hcj-timerange-input' );
		$timeRangeInputs.each( function(index){
			var thisInput = new hcapp.html.timeRangeInput( jQuery(this) );
			thisInput.init();
			thisInput.render();
		});
	});

	$detailsCloser.on('click', function(e)
	{
		$details.hide();
		$calendar.show();
		return false;
	});
});

}());
