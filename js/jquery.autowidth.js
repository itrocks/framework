(function($)
{

	//------------------------------------------------------------------------------------- addMargin
	/**
	 * Add text margin that matches $element to the text
	 *
	 * @param $element jQuery
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

	//----------------------------------------------------------------------------------- blockColumn
	/**
	 * @param settings       object
	 * @param $block         jQuery
	 * @param $cell          jQuery
	 * @param cell_position  integer
	 * @param input_position integer
	 * @returns jQuery
	 */
	var blockColumn = function(settings, $block, $cell, cell_position, input_position)
	{
		var table   = $block.is('table');
		var child   = (cell_position || table) ? ':nth-child(' + cell_position + ')' : '';
		var descend = (cell_position && !table) ? ' > ol > li' : '';
		// the element was the widest element : grow or shorten
		var $input = $block.find((table ? 'tr > td' : '> li:not(:first-child)') + descend + child)
			.find(
				'> input:nth-child(' + input_position + '), > textarea:nth-child(' + input_position + ')'
			);
		var width = Math.max(
			getTextWidth(
				settings,
				$block.find((table ? 'tr > th' : '> li:first-child') + descend + child)
			),
			getTextWidth(settings, $input)
		);
		blockColumnWidth(settings, $cell, width);
		return this;
	};

	//------------------------------------------------------------------------------ blockColumnWidth
	/**
	 * @param settings object
	 * @param $cell    jQuery
	 * @param width    number
	 */
	var blockColumnWidth = function(settings, $cell, width)
	{
		if ($cell.hasClass('no-autowidth')) return;
		$cell.data('max-width', width);
		var calc = width + parseInt($cell.css('padding-left')) + parseInt($cell.css('padding-right'));
		var setting = $cell.parent().hasClass('auto_width') ? 'simple' : 'multiple';
		width = Math.min(Math.max(settings[setting].minimum, calc), settings[setting].maximum);
		$cell.css({ 'max-width': width + 'px', 'min-width': width + 'px', 'width': width + 'px'	});
	};

	//-------------------------------------------------------------------------------- calculateEvent
	/**
	 * This method calculates automatically the width of a DOM element
	 * This must be fired by an event
	 *
	 * @param now             boolean
	 * @param additional_text string
	 */
	var calculateEvent = function(now, additional_text)
	{
		if (additional_text === undefined) {
			additional_text = '';
		}
		if (now === undefined) {
			now = true;
		}
		var $element = $(this);
		var settings = $element.data('settings');
		var calculate = function()
		{
			var previous_width = parseInt($element.data('text-width'));
			var new_width      = getTextWidth(settings, $element, false, true, additional_text);
			if (new_width !== previous_width) {
				$element.data('text-width', new_width);
				var $block = $element.parent().closest('.auto_width');
				// single element
				if (!$block.length) {
					$element.width(
						Math.min(Math.max(settings.simple.minimum, new_width), settings.simple.maximum)
					);
				}
				// element into an autowidth block
				else {
					// calculate first cell of the column previous max width
					var $cell;
					var position;
					if ($element.closest('td, li').parent().hasClass('auto_width')) {
						position = -1;
						$cell    = $element.closest('li').prevAll('li').first();
					}
					else {
						position = $element.closest('td, li').prevAll('td, li').length;
						$cell    = $(firstRowCells(firstRowsGroup($block))[position]);
					}
					var previous_max_width = $cell.data('max-width');
					if (previous_max_width === undefined) {
						blockColumn(settings, $block, $cell, position + 1, $element.prevAll().length + 1);
					}
					if (new_width > previous_max_width) {
						// the element became wider than the widest element
						blockColumnWidth(settings, $cell, new_width);
					}
					else if (previous_width === previous_max_width) {
						blockColumn(settings, $block, $cell, position + 1, $element.prevAll().length + 1);
					}
				}
			}
		};
		// patched with setTimeout to allow moved controls on right of the input to be clicked
		// eg combo's down arrow won't work sometimes if I do not do that.
		now ? calculate() : setTimeout(calculate, 100);
	};

	//------------------------------------------------------------------------------- calculateMargin
	/**
	 * Calculates the margin of a jquery object
	 *
	 * All margins which selector comply the object are added
	 *
	 * @param $element jQuery a jquery object
	 * @param margins  object margins settings { string jquery_selector: integer margin }
	 * @returns number
	 */
	var calculateMargin = function($element, margins)
	{
		if ($.isNumeric(margins)) {
			return margins;
		}
		var found_margin = false;
		var margin       = 0;
		for (var selector in margins) if (margins.hasOwnProperty(selector)) {
			if ((typeof margins[selector] !== 'string') && $element.is(selector)) {
				margin += margins[selector];
				found_margin = true;
			}
		}
		if ((margins.default !== undefined) && !found_margin) {
			margin = margins.default;
		}
		return margin;
	};

	//--------------------------------------------------------------------------------------- cssCopy
	/**
	 * Copy css from a jquery object to another one
	 *
	 * @param $from jQuery
	 * @param $to   jQuery
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

	//--------------------------------------------------------------------------------- firstRowCells
	/**
	 * Gets the cells of the first row of a <thead>, <tbody>, <colgroup>, <ul>
	 *
	 * @param $group jQuery a jquery groups object : matches <thead>, <tbody> or <colgroup>
	 * @return object[] a set of jquery <td> / <th> objects
	 */
	var firstRowCells = function($group)
	{
		return $group.is('ul')
			? $group.find('> li:first > ol > li')
			: $group.find('tr:first th, tr:first td');
	};

	//-------------------------------------------------------------------------------- firstRowsGroup
	/**
	 * Gets the first group object of a <table>
	 * If there is no group object, returns the <table>
	 *
	 * @param $block jQuery a jquery .auto_width block object
	 * @returns object the first <thead>, <tbody>, <colgroup> object into the table, or the <table>
	 */
	var firstRowsGroup = function($block)
	{
		var $group = $block.is('table')
			? $block.find('thead:not(:empty), tbody:not(:empty), colgroup:not(:empty)').first()
			: $block;
		return $group.length ? $group : $block;
	};

	//---------------------------------------------------------------------------------- getTextWidth
	/**
	 * Calculates the width for the widest of a set of jquery objects
	 *
	 * @param settings         object
	 * @param $elements        jQuery
	 * @param [read_cache]     boolean default = true
	 * @param [write_cache]    boolean default = true
	 * @param additional_text string
	 * @returns number
	 */
	var getTextWidth = function(settings, $elements, read_cache, write_cache, additional_text)
	{
		if (additional_text === undefined) {
			additional_text = '';
		}
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
					if (val === undefined) {
						val = '';
					}
				}
				$span.text(addMargin($element, val + additional_text, settings.margin_right));
				width = $span.width();
				if (write_cache) {
					$element.data('text-width', width);
				}
			}
			if (width !== 'auto') {
				width    += calculateMargin($element, settings.margin_right);
				max_width = Math.max(max_width, width);
			}
		});
		$span.remove();
		return max_width;
	};

	//------------------------------------------------------------------------------------- autoWidth
	$.fn.autoWidth = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			margin_right: {
				'input':     0,
				'textarea':  16,
				'.combo':    26,
				'.datetime': 26
			},
			multiple: {
				maximum: 300,
				minimum: 40
			},
			simple: {
				maximum: 1000,
				minimum: 100
			}
		}, options);
		this.data('settings', settings);

		//------------------------------------------------------------------------- autoWidth on events
		this.blur(calculateEvent);
		this.change(calculateEvent);
		this.focus(calculateEvent);
		this.keyup(calculateEvent);

		this.keypress(function(event)
		{
			if (event.keyCode >= 32) {
				calculateEvent.call(this, true, String.fromCharCode(event.charCode));
			}
		});

		//------------------------------------------------------------------------------ autoWidth init
		this.each(function() {
			calculateEvent.call(this, true);
		});

		return this;
	};

})( jQuery );
