(function($)
{

	//------------------------------------------------------------------------------------- autoWidth
	$.fn.autoWidth = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			margin_right: {
				textarea:       20,
				':focus':       'WW',
				'.combo':       10,
				'.combo:focus': -10
			},
			multiple: {
				maximum: 300,
				simple:  40
			},
			simple: {
				maximum: 1000,
				minimum: 100
			}
		}, options);

		//----------------------------------------------------------------------------------- addMargin
		/**
		 * Add text margin that matches $element to the text
		 *
		 * @param $element object a jquery object
		 * @param text     string
		 * @param margins  object margins settings { string jquery_selector: integer margin }
		 * @returns string
		 */
		var addMargin = function($element, text, margins)
		{
			text = text.split("\n");
			for (var selector in margins) if (margins.hasOwnProperty(selector)) {
				if ((typeof margins[selector] === 'string') && $element.is(selector)) {
					for (var i = 0; i < text.length; i ++) {
						text[i] = text[i] + margins[selector];
					}
				}
			}
			return text.join("\n");
		};

		//------------------------------------------------------------------------------ calculateEvent
		/**
		 * This method calculates automatically the width of a DOM element
		 * This must be fired by an event
		 */
		var calculateEvent = function()
		{
			var $element       = $(this);
			// patched with setTimeout to allow moved controls on right of the input to be clicked
			// eg combo's down arrow won't work sometimes if I do not do that.
			setTimeout(function() {var previous_width = parseInt($element.data('text-width'));
			var new_width      = getTextWidth($element, false);
			if (new_width !== previous_width) {
				$element.data('text-width', new_width);
				var tag_name = $element.parent().prop('tagName').toLowerCase();
				var $table   = (tag_name === 'td') ? $element.closest('table') : undefined;
				// single elementif ($table === undefined) {

					$element.width(Math.min(Math.max(settings.simple.minimum, new_width), settings.simple.maximum)
				);
				}
					// element into a collection / mapelse {
					// is element not named and next to a named element ? next_input = true
					var name = $element.attr('name');
					if (name === undefined) {
						name = $element.prev('input, textarea').attr('name');
					}
					// calculate th's previous max width
					var position           = $element.parent().prevAll('td').length;
					var $td                = $(cells(firstColGroup($table))[position]);
					var previous_max_width = $td.data('max-width');
					if (new_width > previous_max_width) {
						// the element became wider than the widest element
						tableColumnWidth($td, new_width);
					}
					else if (previous_width === previous_max_width) {
						tableColumn($table, $td, position + 1, $element.prevAll().length + 1);
					}
				}
			}}, 50);
		};

		//----------------------------------------------------------------------------- calculateMargin
		/**
		 * Calculates the margin of a jquery object
		 *
		 * All margins which selector comply the object are added
		 *
		 * @param $element object a jquery object
		 * @param margins  object margins settings { string jquery_selector: integer margin }
		 * @returns number
		 */
		var calculateMargin = function($element, margins)
		{
			var margin = 0;
			for (var selector in margins) if (margins.hasOwnProperty(selector)) {
				if ((typeof margins[selector] !== 'string') && $element.is(selector)) {
					margin += margins[selector];
				}
			}
			return margin;
		};

		//--------------------------------------------------------------------------------------- cells
		/**
		 * Gets the cells of the first line of a <thead>, <tbody> or <colgroup>
		 *
		 * @param $table_group object a jquery groups object : matches <thead>, <tbody> or <colgroup>
		 * @return object[] a set of jquery <td> / <th> objects
		 */
		var cells = function($table_group)
		{
			return $table_group.find('tr:first th, tr:first td');
		};

		//------------------------------------------------------------------------------------- cssCopy
		/**
		 * Copy css from a jquery object to another one
		 *
		 * @param $from object
		 * @param $to   object
		 * @returns object $from
		 */
		var cssCopy = function($from, $to)
		{
			var tab = [
				'font', 'font-family', 'font-size', 'font-weight',
				'letter-spacing', 'line-height',
				'border', 'border-bottom-width', 'border-left-width', 'border-top-width', 'border-right-width',
				'margin', 'margin-bottom', 'margin-left', 'margin-right', 'margin-top',
				'text-rendering', 'word-spacing', 'word-wrap'
			];
			for (var i = 0; i < tab.length; i++) {
				$to.css(tab[i], $from.css(tab[i]));
			}
			return $from;
		};

		//------------------------------------------------------------------------------- firstColGroup
		/**
		 * Gets the first group object of a <table>
		 * If there is no group object, returns the <table>
		 *
		 * @param $table object a jquery <table> object
		 * @returns object the first <thead>, <tbody>, <colgroup> object into the table, or the <table>
		 */
		var firstColGroup = function($table)
		{
			var $col_group = $table
				.find('thead:not(:empty), tbody:not(:empty), colgroup:not(:empty)')
				.first();
			return $col_group.length ? $col_group : $table;
		};

		//-------------------------------------------------------------------------------- getTextWidth
		/**
		 * Calculates the width for the widest of a set of jquery objects
		 *
		 * @param $elements     jquery objects
		 * @param [read_cache]  boolean default = true
		 * @param [write_cache] boolean default = true
		 * @returns number
		 */
		var getTextWidth = function($elements, read_cache, write_cache)
		{
			read_cache  = (read_cache  === undefined) || read_cache;
			write_cache = (write_cache === undefined) || write_cache;
			var max_width = 0;
			var $span = $('<span>').css({ left: 0, position: 'absolute', top: 0, 'white-space': 'pre' });
			cssCopy($elements, $span);
			$span.appendTo('body');
			$elements.each(function() {
				var $element = $(this);
				var width    = read_cache ? $element.data('text-width') : undefined;
				if (width === undefined) {
					var val = $element.val();
					if (!val.length) {
						val = $element.text();
					}
					if (!val.length) {
						val = $element.attr('placeholder');
						if (val === undefined) val = '';
					}
					$span.text(addMargin($element, val, settings.margin_right));
					width = $span.width();
					if (write_cache) {
						$element.data('text-width', width);
					}
				}
				if (width !== 'auto') {
					width     += calculateMargin($element, settings.margin_right);
					max_width  = Math.max(max_width, Number(width));
				}
			});
			$span.remove();
			return max_width;
		};

		//-------------------------------------------------------------------------------- lastColGroup
		/**
		 * Gets the last group object of a <table>
		 * If there is no group object, returns the <table>
		 *
		 * @param $table object a jquery <table> object
		 * @returns object the last <thead>, <tbody>, <colgroup> object into the table, or the <table>
		 */
		var lastColGroup = function($table)
		{
			var $col_group = $table
				.find('thead:not(:empty), tbody:not(:empty), colgroup:not(:empty)')
				.last();
			return $col_group.length ? $col_group : $table;
		};

		//--------------------------------------------------------------------------------- tableColumn
		/**
		 * @param $table         object
		 * @param $td            object
		 * @param td_position    integer
		 * @param input_position integer
		 * @returns object
		 */
		var tableColumn = function($table, $td, td_position, input_position)
		{
			// the element was the widest element : grow or shorten
			var $input = $table.find(
				'tr>td:nth-child(' + td_position + ')>input:nth-child(' + input_position + '), '
				+ 'tr>td:nth-child(' + td_position + ')>textarea:nth-child(' + input_position + ')'
			);
			var width = Math.max(
				getTextWidth($table.find('tr>th:nth-child(' + td_position + ')')),
				getTextWidth($input)
			);
			tableColumnWidth($td, width);
			return this;
		};

		//---------------------------------------------------------------------------- tableColumnWidth
		/**
		 * @param $td   object
		 * @param width number
		 */
		var tableColumnWidth = function($td, width)
		{
			$td.data('max-width', width);
			var calc = width + parseInt($td.css('padding-left')) + parseInt($td.css('padding-right'));
			width = Math.min(Math.max(settings.multiple.minimum, calc), settings.multiple.maximum);
			$td.width(width).css({ 'max-width': width + 'px', 'min-width': width + 'px' });
		};

		//----------------------------------------------------------------------------- autoWidth keyup
		this.blur(calculateEvent);
		this.change(calculateEvent);
		this.focus(calculateEvent);
		this.keyup(calculateEvent);

		//------------------------------------------------------------------------------ autoWidth init
		this.not('td>input').each(function() {
			calculateEvent.call(this);
		});

		this.filter('td>input').closest('table').each(function() {
			var $table       = $(this);
			var $first_cells = cells(firstColGroup($table));
			var $last_cells  = cells(lastColGroup($table));
			for (var cell_position = 0; cell_position < $first_cells.length; cell_position++) {
				//noinspection JSValidateTypes PhpStorm inspector bug
				var $input = $($last_cells[cell_position]).children(
					'input:not([type=checkbox]):visible:first, textarea:visible:first'
				);
				if ($input.length) {
					tableColumn(
						$table, $($first_cells[cell_position]), cell_position + 1, $input.prevAll().length + 1
					);
				}
			}
		});

		return this;
	};

	$.fn.autowidth = $.fn.autoWidth;

})( jQuery );
