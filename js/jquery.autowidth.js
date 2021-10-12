(function($)
{

	//------------------------------------------------------------------------------------- addMargin
	/**
	 * Add text margin that matches $element to the text
	 *
	 * @param $element jQuery
	 * @param text     string
	 * @param margins  object margins settings { string jquery_selector: integer margin }
	 * @return string
	 */
	const addMargin = function($element, text, margins)
	{
		text = text.split("\n")
		for (const selector in margins) if (margins.hasOwnProperty(selector)) {
			if ((typeof margins[selector] === 'string') && $element.is(selector)) {
				for (let i = 0; i < text.length; i ++) {
					text[i] = text[i] + margins[selector]
				}
			}
		}
		return text.join("\n")
	}

	//----------------------------------------------------------------------------------- blockColumn
	/**
	 * @param settings       object  autoWidth settings
	 * @param $block         jQuery  the block element : table.auto_width, ul.auto_width
	 * @param $cell          jQuery  the head cell for the column
	 * @param cell_position  integer the position of the column cell
	 * @param input_position integer the position of the input element into each data cell
	 * @return jQuery
	 */
	const blockColumn = function(settings, $block, $cell, cell_position, input_position)
	{
		const table   = $block.is('table')
		const child   = (cell_position || table) ? ':nth-child(' + cell_position + ')' : ''
		const descend = (cell_position && !table) ? ' > ol > li' : ''
		// the element was the widest element : grow or shorten
		const $input = $block.find((table ? 'tr > td' : '> li:not(.head)') + descend + child)
			.find(
				'> input:nth-child(' + input_position + '), > textarea:nth-child(' + input_position + ')'
			)
		const width = Math.max(
			getTextWidth(settings, $block.find((table ? 'tr > th' : '> li.head') + descend + child)),
			getTextWidth(settings, $input)
		)
		blockColumnWidth(settings, $cell, width)
		return this
	}

	//------------------------------------------------------------------------------ blockColumnWidth
	/**
	 * @param settings object autoWidth settings
	 * @param $cell    jQuery the head cell for the column
	 * @param width    number the size to set
	 */
	const blockColumnWidth = function(settings, $cell, width)
	{
		if ($cell.hasClass('no-autowidth')) {
			return
		}
		$cell.data('max-width', width)
		const calc = width + parseInt($cell.css('padding-left')) + parseInt($cell.css('padding-right'))
		const setting = $cell.parent().hasClass('auto_width') ? 'simple' : 'multiple'
		if (!$cell.data('max-width') || !$cell.data('min-width')) {
			$cell.data('max-width', $cell.css('max-width'))
			$cell.data('min-width', $cell.css('min-width'))
		}
		width = limitWidth($cell, calc, settings, setting)
		$cell.css({ 'max-width': width + 'px', 'min-width': width + 'px', 'width': width + 'px'	})
	}

	//-------------------------------------------------------------------------------- calculateEvent
	/**
	 * This method calculates automatically the width of a DOM element
	 * This must be fired by an event
	 *
	 * @param now             boolean
	 * @param additional_text string
	 */
	const calculateEvent = function(now, additional_text)
	{
		if (additional_text === undefined) {
			additional_text = ''
		}
		if (now === undefined) {
			now = true
		}
		const $element = $(this)
		const settings = $element.data('settings')
		const calculate = function()
		{
			const previous_width = parseInt($element.data('text-width'))
			const new_width      = getTextWidth(settings, $element, false, true, additional_text)
				+ parseInt($element.css('padding-left')) + parseInt($element.css('padding-right'))
			if (new_width !== previous_width) {
				$element.data('text-width', new_width)
				const $parent = $element.parent()
				const $block  = $parent.closest('.auto_width')
				// single element
				if (!$block.length) {
					$element.width(limitWidth($element, new_width, settings, 'simple'))
				}
				// element into an autowidth block
				else {
					// calculate first cell of the column previous max width
					let $cell
					let position
					if ($element.closest('li').parent().is('.auto_width')) {
						position = -1
						$cell    = $element.closest('ul').children().first()
					}
					else {
						position = $element.closest('li, td, th').prevAll('li, td, th').length
						$cell    = $(firstRowCells(firstRowsGroup($block))[position])
					}
					const previous_max_width = $cell.data('max-width')
					if (previous_max_width === undefined) {
						blockColumn(settings, $block, $cell, position + 1, $element.prevAll().length + 1)
					}
					if (new_width > previous_max_width) {
						// the element became wider than the widest element
						blockColumnWidth(settings, $cell, new_width)
					}
					else if (previous_width === previous_max_width) {
						blockColumn(settings, $block, $cell, position + 1, $element.prevAll().length + 1)
					}
					else {
						blockColumn(settings, $block, $cell, position + 1, $element.prevAll().length + 1)
					}
				}
			}
		}
		// patched with setTimeout to allow moved controls on right of the input to be clicked
		// eg combo's down arrow won't work sometimes if I do not do that.
		now ? calculate() : setTimeout(calculate, 100)
	}

	//------------------------------------------------------------------------------- calculateMargin
	/**
	 * Calculates the margin of a jquery object
	 *
	 * All margins which selector comply the object are added
	 *
	 * @param $element jQuery a jquery object
	 * @param margins  object margins settings { string jquery_selector: integer margin }
	 * @return number
	 */
	const calculateMargin = function($element, margins)
	{
		if ($.isNumeric(margins)) {
			return margins
		}
		let found_margin = false
		let margin       = 0
		for (const selector in margins) if (margins.hasOwnProperty(selector)) {
			if ((typeof margins[selector] !== 'string') && $element.is(selector)) {
				margin += margins[selector]
				found_margin = true
			}
		}
		if ((margins.default !== undefined) && !found_margin) {
			margin = margins.default
		}
		return margin
	}

	//--------------------------------------------------------------------------------------- cssCopy
	/**
	 * Copy css from a jquery object to another one
	 *
	 * @param $from jQuery
	 * @param $to   jQuery
	 * @return object $from
	 */
	const cssCopy = function($from, $to)
	{
		const tab = [
			'font', 'font-family', 'font-size', 'font-weight',
			'letter-spacing', 'line-height',
			'border', 'border-bottom-width', 'border-left-width', 'border-top-width', 'border-right-width',
			'margin', 'margin-bottom', 'margin-left', 'margin-right', 'margin-top',
			'text-rendering', 'word-spacing', 'word-wrap'
		]
		for (let i = 0; i < tab.length; i++) {
			$to.css(tab[i], $from.css(tab[i]))
		}
		return $from
	}

	//--------------------------------------------------------------------------------- firstRowCells
	/**
	 * Gets the cells of the first row of a <thead>, <tbody>, <colgroup>, <ul>
	 *
	 * @param $group jQuery a jquery groups object : matches <thead>, <tbody> or <colgroup>
	 * @return object[] a set of jquery <td> / <th> objects
	 */
	const firstRowCells = function($group)
	{
		let $row_cells = $group.is('ul')
			? $group.find('> li.head > ol > li')
			: $group.find('tr:first th, tr:first td')
		if (!$row_cells.length && $group.is('ul')) {
			$row_cells = $group.children().first()
		}
		return $row_cells
	}

	//-------------------------------------------------------------------------------- firstRowsGroup
	/**
	 * Gets the first group object of a <table>
	 * If there is no group object, returns the <table>
	 *
	 * @param $block jQuery a jquery .auto_width block object
	 * @return object the first <thead>, <tbody>, <colgroup> object into the table, or the <table>
	 */
	const firstRowsGroup = function($block)
	{
		const $group = $block.is('table')
			? $block.find('thead:not(:empty), tbody:not(:empty), colgroup:not(:empty)').first()
			: $block
		return $group.length ? $group : $block
	}

	//---------------------------------------------------------------------------------- getTextWidth
	/**
	 * Calculates the width for the widest of a set of jquery objects
	 *
	 * @param settings         object
	 * @param $elements        jQuery
	 * @param [read_cache]     boolean default = true
	 * @param [write_cache]    boolean default = true
	 * @param additional_text string
	 * @return number
	 */
	const getTextWidth = function(settings, $elements, read_cache, write_cache, additional_text)
	{
		if (additional_text === undefined) {
			additional_text = ''
		}
		read_cache  = (read_cache  === undefined) || read_cache
		write_cache = (write_cache === undefined) || write_cache
		const $span = $('<span>').css({ left: 0, position: 'absolute', top: 0, 'white-space': 'pre' })
		let   max_width = 0
		cssCopy($elements, $span)
		$span.appendTo('body')
		$elements.each(function() {
			const $element = $(this)
			let   width    = read_cache ? $element.data('text-width') : undefined
			if (width === undefined) {
				let val = $element.val()
				if (!val.length) {
					val = $element.text()
				}
				if (!val.length) {
					val = $element.attr('placeholder')
					if (val === undefined) {
						val = ''
					}
				}
				$span.text(addMargin($element, val + additional_text, settings.margin_right))
				width = $span.width()
				if (write_cache) {
					$element.data('text-width', width)
				}
			}
			if (width !== 'auto') {
				width    += calculateMargin($element, settings.margin_right)
				max_width = Math.max(max_width, width)
			}
		})
		$span.remove()
		return max_width
	}

	//------------------------------------------------------------------------------------ limitWidth
	/**
	 * Read max-width and min-width from $element's data (if set) or css
	 * If defined, replace the max-width / min-width coming from the settings by the css / data value
	 *
	 * @const $element jQuery
	 * @const width    number
	 * @const settings array
	 * @const context  string @values multiple, simple
	 * @return number
	 */
	const limitWidth = function($element, width, settings, context)
	{
		let max_width = Math.min(settings[context].maximum, $element.data('max-calculated-width'))
		let min_width = settings[context].minimum
		if (settings[context].use_max_width) {
			max_width = limitWidthRead($element, 'max-width', max_width)
		}
		if (settings[context].use_min_width) {
			min_width = limitWidthRead($element, 'min-width', min_width)
		}
		return Math.min(Math.max(min_width, width), max_width)
	}

	//-------------------------------------------------------------------------------- limitWidthRead
	/**
	 * Read max-width/min-width from $element's data (if set) or css
	 * If defined, replace the max-width / min-width coming from the settings by the css / data value
	 *
	 * @param $element       jQuery
	 * @param css_width_name string @values max-width, min-width
	 * @param width          number
	 * @return number
	 */
	const limitWidthRead = function($element, css_width_name, width)
	{
		let css_width = parseInt($element.data(css_width_name))
		if (!css_width) {
			css_width = parseInt($element.css(css_width_name))
		}
		if (css_width) {
			width = css_width
		}
		return width
	}

	//-------------------------------------------------------------------------------------- maxWidth
	/**
	 * Calculate max width for an input, and store it into its data('max-calculated-width')
	 */
	const maxWidth = function()
	{
		let $element = $(this)
		if (!$element.is('input')) {
			$element.data('max-calculated-width', 9999)
			return
		}
		const $input = $element
		let   min    = parseInt(
			$element.css('padding-left') + $element.css('border-left')
			+ $element.css('padding-right') + $element.css('border-right')
		)
		while ($element.css('overflow').lParse(SP).toString() !== 'hidden') {
			$element = $element.parent()
			if ($element.is(document) || !$element.length) {
				$input.data('max-calculated-width', 9999)
				return
			}
			min += parseInt(
				$element.css('padding-left') + $element.css('border-left')
				+ $element.css('padding-right') + $element.css('border-right')
			)
		}
		const max_width = $element.innerWidth() - ($input.offset().left - $element.offset().left) - min

		$input.data('max-calculated-width', max_width)
	}

	//------------------------------------------------------------------------------------- autoWidth
	$.fn.autoWidth = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		const settings = $.extend({
			margin_right: {
				'input':     0,
				'textarea':  16,
				'.combo':    26,
				'.datetime': 26
			},
			multiple: {
				maximum: 300,
				minimum: 40,
				use_max_width: true,
				use_min_width: true
			},
			simple: {
				maximum: 1000,
				minimum: 100,
				use_max_width: true,
				use_min_width: true
			}
		}, options)
		this.data('settings', settings)

		//------------------------------------------------------------------------- autoWidth on events
		maxWidth.call(this)
		this.blur(calculateEvent)
		this.change(calculateEvent)
		this.focus(calculateEvent)
		this.keyup(calculateEvent)

		this.keypress(function(event)
		{
			if (event.keyCode >= 32) {
				calculateEvent.call(this, true, String.fromCharCode(event.charCode))
			}
		})

		//------------------------------------------------------------------------------ autoWidth init
		this.each(function() {
			calculateEvent.call(this, true)
		})

		return this
	}

	//------------------------------------------------------------------------------ $(window).resize
	$(window).resize(function()
	{
		$('.auto_width:visible').each(function() { $(this).keyup() })
	})

})( jQuery )
