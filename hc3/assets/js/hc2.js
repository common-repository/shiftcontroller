var hcapp = {};

document.addEventListener('DOMContentLoaded', function()
{
	if( ! hcapp.hasOwnProperty('app') ){
		hcapp['app'] = 'hc3';
	}
});

function hc2MakeAjaxHref( href )
{
	href += ( href.split('?')[1] ? '&':'?' ) + 'hcj=' + hcapp['app'];
console.log( href );
	return href;
}

function hc2_set_loader( $el )
{
	var loader = '<div class="hc-loader"></div';
	var shader = '<div class="hc-loader-shader"></div';
	$el.css('position', 'relative'); 
	$el.append( shader );
	$el.append( loader );
}

function hc2_unset_loader( $el )
{
	$el.find('[class="hc-loader-shader"]').remove();
	$el.find('[class="hc-loader"]').remove();
}

jQuery(document).on( 'click', '.hcj2-ajax-loader', function(event)
{
	var $this = jQuery(this);

	hc2_set_loader( $this );
	var href = hc2MakeAjaxHref( $this.attr('href') );

	jQuery.ajax({
		type: 'GET',
		url: href,
		// dataType: "json",
		success: function( data, textStatus ){
			$this.replaceWith( data );
		}
		})
		.fail( function(jqXHR, textStatus, errorThrown){
			hc2_unset_loader( $this );
			alert( 'Ajax Error' );
			console.log( 'Ajax Error: ' + errorThrown + "\n" + "status: " + textStatus + "\n" + "response: " + jqXHR.responseText );
			})
		;

	return false;
});

jQuery(document).on( 'click', '.hcj2-confirm', function(event)
{
	if( window.confirm("Are you sure?") ){
		return true;
	}
	else {
		event.preventDefault();
		event.stopPropagation();
		return false;
	}
});

jQuery(document).on( 'submit', '.hcj2-alert-dismisser', function(e)
{
	jQuery(this).closest('.hcj2-alert').hide();
	return false;
});

document.addEventListener('DOMContentLoaded', function()
{
	/* auto dismiss alerts */
	jQuery('.hcj2-auto-dismiss').delay(3000).slideUp(200, function(){
		// jQuery('.hcj2-auto-dismiss .alert').alert('close');
	});

	var timeRangeInputs = jQuery( '.hcj-timerange-input' );
	timeRangeInputs.each( function(index){
		var this_input = new hcapp.html.timeRangeInput( jQuery(this) );
		this_input.init();
		this_input.render();
	});
});

var hc2 = {};

var hc2_spinner = '<span class="hc-m0 hc-p0 hc-fs5 hc-spin hc-inline-block">&#9788;</span>';
var hc2_absolute_spinner = '<div class="hc-fs5 hc-spin hc-inline-block hc-m0 hc-p0" style="position: absolute; top: 45%;"><span class="hc-m0 hc-p0">&#9788;</span></div>';
var hc2_full_spinner = '<div class="hcj2-full-spinner hc-bg-silver hc-muted-2 hc-align-center" style="z-index: 1000; width: 100%; height: 100%; position: absolute; top: 0; left: 0;">' + hc2_absolute_spinner + '</div>';

// html
hcapp.html = {};

hcapp.html.timeRangeInput = function( $el )
{
	var self = this;
	self.input_value = [0, 0];

	var $container = $el.find('.hcj-display:first');
	var $hidden = $el.find('input[type=hidden]');
	var $input_start = jQuery('<select>', {
		class:	'hc-field hc-block',
		});
	var $input_end = jQuery('<select>', {
		class:	'hc-field hc-block',
		});

	var $allday_input = $el.find(':checkbox');

	if( $allday_input.length ){
		$allday_input.on('change', function(e){
			var is_all_day = jQuery(this)[0].checked;
			if( is_all_day ){
				$container.hide();
			}
			else {
				$container.show();
			}
			$hidden.val( self.get_value() );
			$hidden.trigger('change');
		});
	}

	this.init = function()
	{
		// parse default value
		var this_value = $hidden.val();
		self.default_duration = 5 * 60;
		self.input_value = [];

		if( this_value.length ){
			var this_times = this_value.split('-');
			for( var jj = 0; jj < this_times.length; jj++ ){
				self.input_value.push( parseInt(this_times[jj]) );
			}

			if( 2 == this_times.length ){
				self.default_duration = parseInt(this_times[1]) - parseInt(this_times[0]);
			}
		}
		else {
			var time_format = $el.data('time-format');
			var time_unit = 5 * 60;
			var end_day = 24 * 60 * 60;
			var start_from = 0;

			for( var ts = start_from; ts <= end_day; ts += time_unit ){
				if( ! time_format.hasOwnProperty(ts) ){
					continue;
				}

				self.input_value.push( parseInt(ts) );
				break;
			}
		}
	}

	this.get_value = function()
	{
		var times = [];
		var is_all_day = $allday_input.length ? $allday_input[0].checked : false;

		if( is_all_day ){
			times.push( 0 );
			times.push( 24*60*60 );
		}
		else {
			times.push( $input_start.val() );
			times.push( $input_end.val() );
		}

		var out = times.join('-');
		return out;
	}

	this.render = function()
	{
		var time_unit = 5 * 60;
		var end_day = 24 * 60 * 60;
		var time_format = $el.data('time-format');

	// start
		var start_from = 0;

		$input_start.empty();
		for( var ts = start_from; ts <= end_day; ts += time_unit ){
			if( ! time_format.hasOwnProperty(ts) ){
				continue;
			}

			$input_start.append(
				jQuery('<option>', {
					value: ts,
					text : time_format[ts]
					})
				);
		}

	// update end depending on start
		$input_start.on('change', function(e){
			$input_end.trigger('listen');
		});

		$input_end.on('listen', function(e){
			var start = parseInt( $input_start.val() );

			var current_end = self.input_value[1] ? self.input_value[1] : $input_end.val();
			current_end = start + self.default_duration;
			current_end = parseInt( current_end );

			var current_end_exists = false;

			var end_from = start + time_unit;
			var end_to = start + end_day;
			var last_seen_end = 0;

			$input_end.empty();
			for( var ts = end_from; ts <= end_to; ts += time_unit ){
				var ts_key = ( ts > end_day ) ? ts % end_day : ts;

				if( ! time_format.hasOwnProperty(ts_key) ){
					continue;
				}

				if( (! last_seen_end) && (ts > end_from) ){
					last_seen_end = ts;
				}

				if( ts == current_end ){
					current_end_exists = true;
				}

				var ts_view = time_format[ts_key];
				if( ts > end_day ){
					var ts_view = ' > ' + ts_view;
				}

				$input_end.append(
					jQuery('<option>', {
						value: ts,
						text : ts_view
						})
					);
			}

			if( ! current_end_exists ){
				current_end = last_seen_end;
			}

			$input_end.val(current_end);
			self.input_value[1] = current_end;
			return false;
		});

		$input_start.val( self.input_value[0] );
		$input_end.val( self.input_value[1] );

	// end
		$input_end.trigger('listen');

	// update hidden value
		$input_start.on('change', function(e){
			$hidden.val( self.get_value() );
			$hidden.trigger('change');
		});

		$input_end.on('change', function(e){
			$hidden.val( self.get_value() );
			$hidden.trigger('change');
		});

	// display
		var out = new hcapp.html.List_Inline()
			.set_mobile(true)
			.set_gutter(2)
			.add( $input_start )
			.add( '-' )
			.add( $input_end )
			;
		out = out.render();

		$container
			.empty()
			.append( out )
			;

		$hidden.val( self.get_value() );
		$hidden.trigger('change');
	}
}

hcapp.html.List_Inline = function()
{
	var self = this;

	self.items = [];
	self.gutter = 2;
	self.is_mobile = false;

	this.add = function( item )
	{
		self.items.push( item );
		return this;
	}

	this.set_gutter = function( gutter )
	{
		self.gutter = gutter;
		return this;
	}

	this.set_mobile = function( mobile )
	{
		self.mobile = mobile;
		return this;
	}

	this.render = function()
	{
		var $out = jQuery('<div>');

		for( var ii = 0; ii < self.items.length; ii++ ){
			var $out_item = jQuery('<div>')
				.addClass('hc-valign-middle')
				;

			if( self.mobile ){
				$out_item.addClass('hc-inline-block');
			}
			else {
				$out_item.addClass('hc-lg-inline-block');
			}

			if( self.gutter && ii < (self.items.length - 1) ){
				$out_item.addClass( 'hc-mr' + self.gutter );
				if( ! self.mobile ){
					$out_item
						.addClass( 'hc-xs-mr0' )
						.addClass( 'hc-xs-mb' + self.gutter )
						;
				}
			}

			$out_item.append( self.items[ii] );
			$out.append( $out_item );
		}

		return $out;
	}
}

hcapp.html.List = function()
{
	var self = this;

	self.items = [];
	self.gutter = 2;

	this.add = function( item )
	{
		self.items.push( item );
		return this;
	}

	this.set_gutter = function( gutter )
	{
		self.gutter = gutter;
		return this;
	}

	this.render = function()
	{
		var $out = jQuery('<div>');

		for( var ii = 0; ii < self.items.length; ii++ ){
			var $out_item = jQuery('<div>')
				.addClass('hc-block')
				;

			if( self.gutter && ii ){
				$out_item
					.addClass( 'hc-mt' + self.gutter )
					;
			}

			$out_item.append( self.items[ii] );
			$out.append( $out_item );
		}

		return $out;
	}
}

hcapp.html.Month_Calendar = function()
{
	var self = this;
	self.dates = [];
	self.lang = [];

	self._selected_date = null;
	self._cells = {};

	var $this = jQuery({});
	this.on = function( e, callback ){
		$this.on( e, callback );
	}
	this.trigger = function( e, params ){
		$this.trigger( e, params );
	}

	this.get_selected_date = function()
	{
		if( ! (self._selected_date == null) ){
			return self._selected_date;
		}

		var this_date = null
		for( var ii = 0; ii < self.dates.length; ii++ ){
			if( this_date ){
				break;
			}
			for( var jj = 0; jj < self.dates[ii].length; jj++ ){
				var this_date = self.dates[ii][jj];
				if( this_date ){
					break;
				}
			}
		}

		self._selected_date = this_date;
		return self._selected_date;
	}

	this.select_date = function( date )
	{
		self._selected_date = date;
		self.trigger('select-date', date);
		return this;
	}

	this.set_dates = function( dates )
	{
		self.dates = dates;
		return this;
	}

	this.render = function()
	{
		// alert('render cal' + self.dates.length);
		var $out = jQuery('<div>', {
			class:	'hc-block',
			});

		var out = new hcapp.html.List()
			.set_gutter(0)
			;

	// labels
		if( self.lang && self.lang.length ){
			var label_row = new hcapp.html.Grid()
				.set_gutter(0)
				;
			for( var jj = 0; jj < 7; jj++ ){
				var $this_cell = jQuery('<div>', {
					class:	'hc-align-center hc-nowrap hc-fs1',
					})
					.append( self.lang[jj] )
					.attr('title', self.lang[jj])
					;
				label_row.add( $this_cell, '1-7', '1-7' );
			}
			out.add( label_row.render() );
		}

		if( ! self.dates ){
			self.dates = [];
		}
		for( var ii = 0; ii < self.dates.length; ii++ ){
			var row = new hcapp.html.Grid()
				.set_gutter(0)
				;

			for( var jj = 0; jj < self.dates[ii].length; jj++ ){
				var this_date = self.dates[ii][jj];

				var $this_cell = jQuery('<div>', {
					class:	'hc-align-center hc-nowrap',
					});

				self.trigger( 'render-date', {date: this_date, cell: $this_cell} );

				row.add( $this_cell, '1-7', '1-7' );
				self._cells[ this_date ] = $this_cell;
			}

			out.add( row.render() );
		}

		return out.render();
	}

	this.get_date_cell = function( date )
	{
		return self._cells[date];
	}
}

hcapp.html.Grid = function()
{
	this.items = [];
	this.gutter = 2;

	this.add = function( item, width, mobile_width )
	{
		if( ! mobile_width ){
			mobile_width = 12;
		}
		this.items.push( {'item': item, 'width': width, 'mobile_width': mobile_width} );
		return this;
	}

	this.set_gutter = function( gutter )
	{
		this.gutter = gutter;
		return this;
	}

	this.render = function()
	{
		var rows = [];

		var full_width = 12;
		var current_row = [];
		var taken_width = 0;

		for( var ii = 0; ii < this.items.length; ii++ ){
			var this_width  = this.items[ii].width;
			// this_width = 0;

			if( (taken_width + this_width) > full_width ){
				rows.push( current_row );
				taken_width = 0;
				current_row = [];
			}

			current_row.push( this.items[ii] );
			taken_width += this_width;
		}

		if( current_row.length ){
			rows.push( current_row );
			taken_width = 0;
			current_row = [];
		}

		var $out = jQuery('<div>', {
			})
			;

		for( var ii = 0; ii < rows.length; ii++ ){
			var $out_row = jQuery('<div>', {
				class:	'hc-clearfix',
				});

			if( this.gutter ){
				$out_row.addClass( 'hc-mxn' + this.gutter );

				if( (rows.length > 1) && (ii != (rows.length - 1)) ){
					$out_row.addClass( 'hc-mb' + this.gutter );
				}
			}

			for( var jj = 0; jj < rows[ii].length; jj++ ){
				var this_width = rows[ii][jj].width;
				var this_mobile_width = rows[ii][jj].mobile_width;

				var cell_classes = [];

				if( this_mobile_width != 12 ){
					cell_classes.push( 'hc-xs-col' );
					cell_classes.push( 'hc-xs-col-' + this_mobile_width );
				}

				cell_classes.push( 'hc-col' );
				cell_classes.push( 'hc-col-' + this_width );
				if( this.gutter ){
					cell_classes.push( 'hc-xs-mb' + this.gutter );
				}

				var $out_cell = jQuery('<div>');
				for( var kk = 0; kk < cell_classes.length; kk++ ){
					$out_cell.addClass( cell_classes[kk] );
				}

				var this_item = rows[ii][jj].item;
				if( ! Array.isArray(this_item) ){
					this_item = [this_item];
				}

				for( var kk = 0; kk < this_item.length; kk++ ){
					$out_cell.append( this_item[kk] );
				}

				if( this.gutter ){
					$out_cell.addClass( 'hc-px' + this.gutter );
				}

				$out_row.append( $out_cell );
			}

			$out.append( $out_row );
		}
		return $out;
	}
}
