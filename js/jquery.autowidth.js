(function($)
{

	// TODO all functions here are private and should be private and not set as jQuery plugins

	//----------------------------------------------------------------------------------------- cells
	$.fn.cells = function()
	{
		return this.find('tr:first th, tr:first td');
	};

	//----------------------------------------------------------------------------------- csscopyfrom
	$.fn.csscopyfrom = function(from)
	{
		from.csscopyto(this);
		return this;
	};

	//------------------------------------------------------------------------------------- csscopyto
	$.fn.csscopyto = function(to)
	{
		var tab = [
			'font', 'font-family', 'font-size', 'font-weight',
			'letter-spacing', 'line-height',
			'border', 'border-bottom-width', 'border-left-width', 'border-top-width', 'border-right-width',
			'margin', 'margin-bottom', 'margin-left', 'margin-right', 'margin-top',
			'text-rendering', 'word-spacing', 'word-wrap'
		];
		for (var i = 0; i < tab.length; i++) {
			to.css(tab[i], this.css(tab[i]));
		}
		return this;
	};

	//--------------------------------------------------------------------------------- firstcolgroup
	$.fn.firstcolgroup = function()
	{
		var $colgroup = this.find('thead:not(:empty), tbody:not(:empty), colgroup:not(:empty)').first();
		return $colgroup.length ? $colgroup : this;
	};

	//---------------------------------------------------------------------------------- gettextwidth
	/**
	 * @param [read_cache]  boolean default = true
	 * @param [write_cache] boolean default = true
	 * @returns number
	 */
	$.fn.gettextwidth = function(read_cache, write_cache)
	{
		read_cache  = (read_cache  == undefined) || read_cache;
		write_cache = (write_cache == undefined) || write_cache;
		var max_width = 0;
		var $span = $('<span>').css('position', 'absolute').css({left: 0, top: 0}).csscopyfrom(this);
		$span.appendTo('body');
		this.each(function() {
			var $this = $(this);
			var width = read_cache ? $this.data('text-width') : undefined;
			if (width == undefined) {
				var val = $this.val();
				if (!val.length) {
					val = $this.text();
				}
				$span.text(val.replace(' ', '_').split("\n").join('<br>'));
				width = $span.width();
				if (write_cache) {
					$this.data('text-width', width);
				}
			}
			max_width = Math.max(max_width, width);
		});
		$span.remove();
		return max_width;
	};

	//---------------------------------------------------------------------------------- lastcolgroup
	$.fn.lastcolgroup = function()
	{
		var $colgroup = this.find('thead:not(:empty), tbody:not(:empty), colgroup:not(:empty)').last();
		return $colgroup.length ? $colgroup : this;
	};

	//-------------------------------------------------------------------------- autowidthTableColumn
	$.fn.autowidthTableColumn = function($td, td_position, input_position, settings)
	{
		// the element was the widest element : grow or shorten
		var $this = $(this);
		var $input = $this.find(
			'tr>td:nth-child(' + td_position + ')>input:nth-child(' + input_position + '), '
			+ 'tr>td:nth-child(' + td_position + ')>textarea:nth-child(' + input_position + ')'
		);
		var width = Math.max(
			$this.find('tr>th:nth-child(' + td_position + ')').gettextwidth(),
			$input.gettextwidth() + calcMargin.call($input, settings.margin_right)
		);
		$td.autowidthTableColumnWidth(width, settings);
		return this;
	};

	//--------------------------------------------------------------------- autowidthTableColumnWidth
	/**
	 * @param width number
	 * @param settings object
	 */
	$.fn.autowidthTableColumnWidth = function(width, settings)
	{
		var $td = $(this);
		$td.data('max-width', width);
		width = Math.max(
			settings.minimum, width + parseInt($td.css('padding-left')) + parseInt($td.css('padding-right'))
		) + 10;
		$td.width(width).css({'max-width': width + 'px', 'min-width': width + 'px'});
	};

	//------------------------------------------------------------------------------------ calcMargin
	/**
	 * @param margins object
	 * @returns number
	 */
	var calcMargin = function(margins)
	{
		var margin = 0;
		for (var selector in margins) if (margins.hasOwnProperty(selector)) {
			if (this.is(selector)) {
				margin += margins[selector];
			}
		}
		return margin;
	};

	//------------------------------------------------------------------------------------- autowidth
	$.fn.autowidth = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			maximum: 1024,
			minimum: 40,
			margin_right: {
				'*':        0,
				'textarea': 20,
				':focus':   20,
				'.combo':   13,
				'.combo:focus': -10
			}
		}, options);

		//----------------------------------------------------------------------------------- calculate
		var calculate = function()
		{
			var $this = $(this);
			var previous_width = parseInt($this.data('text-width'));
			var margin_right = calcMargin.call($this, settings.margin_right);
			var new_width = $this.gettextwidth(false) + margin_right;
			if (new_width != previous_width) {
				$this.data('text-width', new_width);
				var tag_name = $this.parent().prop('tagName').toLowerCase();
				var $table = (tag_name == 'td') ? $this.closest('table') : undefined;
				if ($table == undefined) {
					// single element
					$this.width(Math.min(Math.max(settings.minimum, new_width), settings.maximum));
				}
				else {
					// element into a collection / map
					// is element not named and next to a named element ? next_input = true
					var name = $this.attr('name');
					if (name == undefined) {
						name = $this.prev('input, textarea').attr('name');
					}
					// calculate th's previous max width
					var position = $this.parent().prevAll('td').length;
					var $td = $($table.firstcolgroup().cells()[position]);
					var previous_max_width = $td.data('max-width');
					if (new_width > previous_max_width) {
						// the element became wider than the widest element
						$td.autowidthTableColumnWidth(new_width, settings);
					}
					else if (previous_width == previous_max_width) {
						$table.autowidthTableColumn($td, position + 1, $this.prevAll().length + 1, settings);
					}
				}
			}
		};

		//----------------------------------------------------------------------------- autowidth keyup
		this.blur(calculate);
		this.focus(calculate);
		this.keyup(calculate);

		//------------------------------------------------------------------------------ autowidth init
		this.not('td>input').each(function() { calculate.call($(this)); });
		this.filter('td>input').closest('table').each(function() {
			var $table = $(this);
			var $first_cells = $table.firstcolgroup().cells();
			var $last_cells = $table.lastcolgroup().cells();
			for (var cell_position = 0; cell_position < $first_cells.length; cell_position++) {
				var $input = $($last_cells[cell_position]).children(
					'input:not([type=checkbox]):visible:first, textarea:visible:first'
				);
				if ($input.length) {
					$table.autowidthTableColumn(
						$($first_cells[cell_position]), cell_position + 1, $input.prevAll().length + 1, settings
					);
				}
			}
		});

		return this;
	};

})( jQuery );
