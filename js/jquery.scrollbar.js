
//-------------------------------------------------------------------------------- jQuery.scrollbar
(function($)
{

	//------------------------------------------------------------------- plugin common data & events
	let elements        = {}
	let moving          = null
	let next_identifier = 0

	//--------------------------------------------------------------------------- createScrollBarsDOM
	/**
	 * @param $element jQuery
	 */
	const createScrollBarsDOM = function($element)
	{
		const scrollbar = $element.data('scrollbar')
		const settings  = scrollbar.settings
		const arrows    = settings.arrows
		const direction = settings.direction
		scrollbar.$angle = (direction === 'both')
			? $('<div class="scrollbar angle"/>').appendTo($element)
			: null
		scrollbar.$horizontal = (['both', 'horizontal'].indexOf(direction) > -1)
			? createEvents(scrollBar('horizontal', arrows).appendTo($element))
			: null
		scrollbar.$vertical = (['both', 'vertical'].indexOf(direction) > -1)
			? createEvents(scrollBar('vertical', arrows).appendTo($element))
			: null
		scrollbar.$scrollbars = (direction === 'both')
			? scrollbar.$horizontal.add(scrollbar.$vertical)
			: ((direction === 'horizontal') ? scrollbar.$horizontal : scrollbar.$vertical)
	}

	//---------------------------------------------------------------------------------- createEvents
	const createEvents = function($scrollbar)
	{
		return $scrollbar.mousedown(mouseDown).mouseout(mouseOutStyle).mousemove(mouseMoveStyle)
	}

	//------------------------------------------------------------------------------------------ draw
	/**
	 * @param $element jQuery
	 */
	const draw = function($element)
	{
		const scrollbar = $element.data('scrollbar')
		drawVisibleBars($element)
		drawBar(scrollbar, 'vertical', 'top', 'height')
		if (drawBar(scrollbar, 'horizontal', 'left', 'width') && scrollbar.is_table) {
			drawFixedColumnHeaders($element)
		}
		if (scrollbar.settings.draw) {
			scrollbar.settings.draw.call($element)
		}
	}

	//--------------------------------------------------------------------------------------- drawBar
	/**
	 * @param scrollbar object
	 * @param direction string @values horizontal, vertical
	 * @param position  string @values left, top
	 * @param size      string @values height, width
	 * @return boolean
	 */
	const drawBar = function(scrollbar, direction, position, size)
	{
		const $scrollbar = scrollbar['$' + direction]
		if (!$scrollbar) {
			return false
		}
		const $bar     = $scrollbar.find('.bar')
		const $content = (size === 'width') ? scrollbar.$content : scrollbar.$body
		const $scroll  = $bar.parent()
		// sizes
		const bar_size            = $bar[size].call($bar)
		const content_scroll_size = maxScroll($content, size)
		const content_size        = $content[size].call($content)
		const scroll_size         = $scroll[size].call($scroll)
		// percentage bar size calculation
		const percentage = Math.round(1000 * content_size / content_scroll_size) / 10
		$bar.css(size, percentage.toString() + '%')
		if (scrollbar.dont_move) {
			return true
		}
		// bar position
		const max_position = scroll_size - bar_size
		let   new_position = drawBarGetPosition($scroll, $bar, direction, position, size, max_position)
		if (!new_position && !scrollbar.initialized) {
			new_position = 0
		}
		if (new_position !== null) {
			$bar.css(position, new_position.toString() + 'px')
			const content_max_position = content_scroll_size - content_size
			drawContentScrollPosition(
				scrollbar, position, new_position, max_position, content_max_position
			)
		}
		return true
	}

	//---------------------------------------------------------------------------- drawBarGetPosition
	/**
	 * @param $scroll      jQuery
	 * @param $bar         jQuery
	 * @param direction    string @values horizontal, vertical
	 * @param position     string @values left, top
	 * @param size         string @values height, width
	 * @param max_position integer
	 * @return integer|number|null
	 */
	const drawBarGetPosition = function($scroll, $bar, direction, position, size, max_position)
	{
		const bar_position          = $bar.offset()[position]
		const scroll_position       = $scroll.offset()[position]
		const border_position_width = 'border' + position.ucfirst() + 'Width'
		const scroll_style          = window.getComputedStyle($scroll[0])
		const border_width          = parseInt(scroll_style[border_position_width])
		const old_top               = bar_position - scroll_position - border_width
		if (!moving || (moving.direction !== direction)) {
			return (old_top > max_position) ? max_position : null
		}
		const difference         = moving.mouse[position] - moving.from[position]
		moving.from[position] += difference
		return Math.max(0, Math.min(max_position, old_top + difference))
	}

	//--------------------------------------------------------------------- drawContentScrollPosition
	/**
	 * @param scrollbar         object
	 * @param position          string @values left, top
	 * @param new_position      integer
	 * @param max_position      integer
	 * @param body_max_position integer
	 */
	const drawContentScrollPosition = function(
		scrollbar, position, new_position, max_position, body_max_position
	) {
		const body_position         = Math.round(body_max_position * new_position / max_position)
		const contentScrollPosition = scrollbar.$content['scroll' + position.ucfirst()]
		contentScrollPosition.call(scrollbar.$content, body_position)
	}

	//------------------------------------------------------------------------ drawFixedColumnHeaders
	/**
	 * @param $table jQuery
	 */
	const drawFixedColumnHeaders = function($table)
	{
		const scrollbar = $table.data('scrollbar')
		const $head     = scrollbar.$head
		const columns   = scrollbar.columns
		let   left      = $head.scrollLeft()
		let   previous  = 0
		let   right     = left + $head.width() - maxScroll($head, 'width')
		for (const index in columns) if (columns.hasOwnProperty(index)) {
			const $column = columns[index]
			// left
			if (parseInt(index) === ++previous) {
				$column.css('transform', 'translateX(' + left + 'px)')
				left += $column.width()
			}
			// right
			else {
				$column.css('transform', 'translateX(' + right + 'px')
				right -= $column.width()
			}
		}
	}

	//------------------------------------------------------------------------------- drawVisibleBars
	/**
	 * Calculate if scrollbars are visible or not, change css to draw them or not
	 *
	 * @param $element jQuery
	 */
	const drawVisibleBars = function($element)
	{
		const scrollbar = $element.data('scrollbar')

		const visibility = isVisible(scrollbar.$horizontal).toString()
			+ isVisible(scrollbar.$vertical).toString()
		visibleVertical($element)
		const horizontal = visibleHorizontal($element)
		const vertical   = visibleVertical($element)
		visibleAngle($element)
		if (scrollbar.initialized && ((horizontal.toString() + vertical.toString()) === visibility)) {
			return
		}

		const columns  = scrollbar.grid.columns.slice(0)
		const rows     = scrollbar.grid.rows.slice(0)
		const template = scrollbar.grid.template.slice(0)
		if (!horizontal) {
			rows.pop()
			template.pop()
		}
		if (!vertical) {
			columns.pop()
			for (const index in template) if (template.hasOwnProperty(index)) {
				template[index] = template[index].lParse(SP)
			}
		}
		$element.css({
			'grid-template-areas':   Q + template.join(Q + SP + Q) + Q,
			'grid-template-columns': columns.join(SP),
			'grid-template-rows':    rows.join(SP)
		})
	}

	//----------------------------------------------------------------------------- fixedColumnsWidth
	/**
	 * Calculates the cumulative width of fixed columns
	 *
	 * @param columns jQuery[]
	 * @return integer|number
	 */
	const fixedColumnsWidth = function(columns)
	{
		let total_width = 0
		for (const index in columns) if (columns.hasOwnProperty(index)) {
			total_width += columns[index].width()
		}
		return total_width
	}

	//---------------------------------------------------------------------------------- gridTemplate
	/**
	 * @param $element jQuery
	 * @return object
	 */
	const gridTemplate = function($element)
	{
		const scrollbar = $element.data('scrollbar')
		const weight    = parseInt(scrollbar.$scrollbars.css('--weight'))
		return {
			columns:  ['1fr', weight.toString() + 'px'],
			rows:     ['1fr', weight.toString() + 'px'],
			template: [
				'body vertical',
				'horizontal angle'
			]
		}
	}

	//----------------------------------------------------------------------------- gridTemplateTable
	/**
	 * @param $element jQuery
	 * @return object
	 */
	const gridTemplateTable = function($element)
	{
		const scrollbar = $element.data('scrollbar')
		const near      = scrollbar.settings.vertical_scrollbar_near
		const near_foot = (['both', 'foot'].indexOf(near) > -1) ? 'vertical' : 'foot'
		const near_head = (['both', 'head'].indexOf(near) > -1) ? 'vertical' : 'head'
		const weight    = parseInt(scrollbar.$scrollbars.css('--weight'))

		return {
			columns: ['1fr', weight.toString() + 'px'],
			rows: [
				scrollbar.$head ? (scrollbar.$head.height().toString() + 'px') : 0,
				'1fr',
				scrollbar.$foot ? (scrollbar.$foot.height().toString() + 'px') : 0,
				weight.toString() + 'px'
			],
			template: [
				'head ' + near_head,
				'body vertical',
				'foot ' + near_foot,
				'horizontal angle'
			]
		}
	}

	//------------------------------------------------------------------------------------------ init
	/**
	 * @param $element jQuery
	 */
	const init = function($element)
	{
		const scrollbar  = $element.data('scrollbar')
		scrollbar.grid = $element.is('table')
			? gridTemplateTable($element, scrollbar.settings)
			: gridTemplate($element)

		$element.css({ display: 'grid', position: 'relative' })
		scrollbar.$body.css('grid-area', 'body')
		if (scrollbar.$foot) {
			scrollbar.$foot.css('grid-area', 'foot')
		}
		if (scrollbar.$head) {
			scrollbar.$head.css('grid-area', 'head')
		}
	}

	//-------------------------------------------------------------------------------- initTableAfter
	/**
	 * @param $table jQuery
	 */
	const initTableAfter = function($table)
	{
		initTableFixedColumns($table)
		$table.children('tbody, tfoot, thead').css({ display: 'block', overflow: 'hidden' })
	}

	//------------------------------------------------------------------------------- initTableBefore
	/**
	 * @param $table jQuery
	 */
	const initTableBefore = function($table)
	{
		initTableColumnWidths($table)
	}

	//------------------------------------------------------------------------- initTableColumnWidths
	/**
	 * @param $table jQuery
	 */
	const initTableColumnWidths = function($table)
	{
		const $tbody    = $table.children('tbody')
		const $tfoot    = $table.children('tfoot')
		const $thead    = $table.children('thead')
		const $tbody_tr = $tbody.children('tr:first-child')
		const $tfoot_tr = $tfoot.children('tr:first-child')
		const $thead_tr = $thead.children('tr:first-child')
		const $tr       = $thead_tr.length ? $thead_tr : ($tbody_tr.length ? $tbody_tr : $tfoot_tr)
		const trs       = [$tbody_tr, $tfoot_tr, $thead_tr]
		const widths    = []

		$tr.children().each(function() {
			widths.push($(this).width())
		})
		for (const tr of trs) {
			let key = 0
			tr.children().each(function() {
				$(this).css('min-width', widths[key++])
			})
		}
	}

	//------------------------------------------------------------------------- initTableFixedColumns
	/**
	 * @param $table jQuery
	 */
	const initTableFixedColumns = function($table)
	{
		const scrollbar     = $table.data('scrollbar')
		const settings      = scrollbar.settings
		const fixed_columns = settings.fixed_columns
		const tr_selector   = 'tr' + settings.tr_filter
		scrollbar.columns   = {}
		if (fixed_columns) {
			$table.find(fixed_columns).each(function() {
				const $fixed = $(this)
				const index  = $fixed.prevAll().length + 1
				if (scrollbar.columns[index] === undefined) {
					scrollbar.columns[index] = $table.find(tr_selector + ' > :nth-child(' + index + ')')
				}
			})
		}
	}

	//------------------------------------------------------------------------------------- isVisible
	/**
	 * @param $element jQuery|null
	 * @return boolean
	 */
	const isVisible = function($element)
	{
		return $element && $element.is(':visible')
	}

	//------------------------------------------------------------------------------------- maxScroll
	/**
	 * Returns the maximum value for scrollHeight or scrollWidth of $elements
	 *
	 * @param $elements jQuery
	 * @param size      string @values height, width
	 * @return float|number
	 */
	const maxScroll = function($elements, size)
	{
		let max_scroll = 0
		$elements.each(function() {
			max_scroll = Math.max(max_scroll, this['scroll' + size.ucfirst()])
		})
		return max_scroll
	}

	//-------------------------------------------------------------------------------- mouseClickMove
	/**
	 * @param event      object
	 * @param $scrollbar jQuery
	 * @return boolean
	 */
	const mouseClickMove = function(event, $scrollbar)
	{
		const $element  = $scrollbar.parent()
		const scrollbar = $element.data('scrollbar')

		let direction, fixed_columns_size, mouse_position, position, size
		if ($scrollbar.is('.horizontal')) {
			direction          = 'horizontal'
			fixed_columns_size = fixedColumnsWidth(scrollbar.columns)
			mouse_position     = event.pageX
			position           = 'left'
			size               = 'width'
		}
		else {
			direction          = 'vertical'
			fixed_columns_size = 0
			mouse_position     = event.pageY
			position           = 'top'
			size               = 'height'
		}

		const $bar     = $scrollbar.find('.bar')
		const bar_size = $bar[size].call($bar)
		const offset   = $bar.offset()
		const start    = offset[position]
		const stop     = start + bar_size - 1
		if ((mouse_position >= start) && (mouse_position <= stop)) {
			return false
		}

		const $body     = scrollbar.$body
		const body_size = $body[size].call($body)
		let   body_move = body_size - fixed_columns_size

		if (mouse_position < start) {
			body_move = -body_move
		}
		scroll($element, direction, body_move)

		return true
	}

	//------------------------------------------------------------------------------------- mouseDown
	/**
	 * @param event object
	 */
	const mouseDown = function(event)
	{
		if (moving) {
			return
		}
		const $scrollbar = $(this)
		const $bar       = $scrollbar.find('.bar')
		$bar.addClass('moving')
		if (mouseClickMove(event, $scrollbar)) {
			setTimeout(function() { $bar.removeClass('moving') }, 100)
			return
		}
		moving = {
			direction:  $scrollbar.is('.horizontal') ? 'horizontal' : 'vertical',
			from:       { left: event.pageX, top: event.pageY },
			$element:   $scrollbar.parent(),
			$scrollbar: $scrollbar
		}
		$(document).mousemove(mouseMove).mouseup(mouseUp)
		event.preventDefault()
	}

	//------------------------------------------------------------------------------------- mouseMove
	/**
	 * @param event object
	 */
	const mouseMove = function(event)
	{
		if (!event.which) {
			return window.scrollbar.mouseup(event)
		}
		moving.mouse = { left: event.pageX, top: event.pageY }
		draw(moving.$element)
	}

	//-------------------------------------------------------------------------------- mouseMoveStyle
	/**
	 * @param event object
	 */
	const mouseMoveStyle = function(event)
	{
		const $scrollbar = $(this)
		const $bar       = $scrollbar.find('.bar')
		let   mouse, start, stop
		if ($scrollbar.is('.horizontal')) {
			mouse = event.pageX
			start = $bar.offset().left
			stop  = start + $bar.width() - 1
		}
		else {
			mouse = event.pageY
			start = $bar.offset().top
			stop  = start + $bar.height() - 1
		}
		if ((mouse < start) || (mouse > stop)) {
			mouseOutStyle.call($scrollbar, event)
			return
		}
		if (moving) {
			return
		}
		$bar.addClass('hover')
	}

	//--------------------------------------------------------------------------------- mouseOutStyle
	const mouseOutStyle = function()
	{
		const $scrollbar = $(this)
		if (moving) {
			return
		}
		$scrollbar.find('.bar').removeClass('hover')
	}

	//--------------------------------------------------------------------------------------- mouseUp
	const mouseUp = function()
	{
		if (!moving) {
			return
		}
		$(document).off('mousemove', mouseMove).off('mouseup', mouseUp)
		moving.$scrollbar.find('.bar').removeClass('moving')
		moving = null
	}

	//------------------------------------------------------------------------------------ mouseWheel
	const mouseWheel = function(event)
	{
		const $element = $(this)
		// noinspection JSUnresolvedVariable deltaFactor exists
		let until = Math.abs(event.deltaFactor * (event.deltaY ? event.deltaY : event.deltaX))
		let speed = Math.round(until / 12)
		event.deltaX /= -Math.abs(event.deltaX)
		event.deltaY /= -Math.abs(event.deltaY)
		const animate  = function()
		{
			speed = Math.min(speed, until)
			if (event.deltaX) {
				scroll($element, 'horizontal', speed * event.deltaX)
			}
			if (event.deltaY) {
				scroll($element, 'vertical', speed * event.deltaY)
			}
			until -= speed
			if (until > 0) {
				setTimeout(animate, 10)
			}
		}
		animate()
	}

	//---------------------------------------------------------------------------------------- remove
	const remove = function()
	{
		const element_identifier = $(this).data('scrollbar').identifier.toString()
		const old_elements       = elements
		elements               = {}
		for (const identifier in old_elements) if (old_elements.hasOwnProperty(identifier)) {
			if (identifier !== element_identifier) {
				elements[identifier] = old_elements[identifier]
			}
		}
	}

	//---------------------------------------------------------------------------------------- resize
	const resize = function()
	{
		for (const $element of Object.values(elements)) {
			const scrollbar = $element.data('scrollbar')
			const distance  = parseInt(scrollbar.$horizontal.children('div').css('left'))
			scroll($element, 'horizontal', -distance)
			draw($element)
			scroll($element, 'horizontal', distance)
		}
	}

	//---------------------------------------------------------------------------------------- scroll
	/**
	 * Scroll content
	 *
	 * @param $element  jQuery
	 * @param direction string @values horizontal, vertical
	 * @param distance  integer
	 */
	const scroll = function($element, direction, distance)
	{
		let position, size
		if (direction === 'horizontal') {
			position = 'left'
			size     = 'width'
		}
		else {
			position = 'top'
			size     = 'height'
		}

		const scrollbar        = $element.data('scrollbar')
		const $scrollbar       = scrollbar['$' + direction]
		const $bar             = $scrollbar.find('.bar')
		const $body            = scrollbar.$body
		const $content         = scrollbar.$content
		const bar_size         = $bar[size].call($bar)
		let   body_position    = maxScroll($body, position)
		const body_size        = $body[size].call($body)
		const body_scroll_size = maxScroll($body, size)
		const scrollPosition   = $body['scroll' + position.ucfirst()]

		body_position  = (distance < 0)
			? Math.max(body_position + distance, 0)
			: Math.min(body_position + distance, body_scroll_size - body_size)
		const bar_left = Math.round(body_position * bar_size / body_size)
		scrollPosition.call($content, body_position)
		$bar.css(position, bar_left.toString() + 'px')

		if (scrollbar.is_table && (direction === 'horizontal')) {
			drawFixedColumnHeaders($element)
		}

		if (scrollbar.settings.draw) {
			scrollbar.settings.draw.call($element)
		}
	}

	//--------------------------------------------------------------- horizontal / vertical scrollbar
	/**
	 * @param direction string @values horizontal, vertical
	 * @param arrows    boolean
	 * @return jQuery
	 */
	const scrollBar = function(direction, arrows)
	{
		return $(
			'<div class="' + (arrows ? 'arrows ' : '') + direction + ' scrollbar">'
			+ (arrows ? '<div class="previous"/><div class="scroll">' : '')
			+ '<div class="bar"/>'
			+ (arrows ? '</div><div class="next"/>' : '')
			+ '</div>'
		)
	}

	//---------------------------------------------------------------------------------- visibleAngle
	/**
	 * Must be called after visibleHorizontal() and visibleVertical()
	 *
	 * @param $element jQuery
	 * @return boolean
	 */
	const visibleAngle = function($element)
	{
		const scrollbar  = $element.data('scrollbar')
		const $scrollbar = scrollbar.$angle
		if (!$scrollbar) {
			return false
		}
		const is_visible = (scrollbar.$horizontal.is(':visible') && scrollbar.$vertical.is(':visible'))
		visibleToCss($scrollbar, is_visible)
		return is_visible
	}

	//---------------------------------------------------------------------------------- visibleToCss
	/**
	 * @param $scrollbar jQuery
	 * @param is_visible boolean
	 */
	const visibleToCss = function($scrollbar, is_visible)
	{
		const was_visible = $scrollbar.is(':visible')
		if (is_visible && !was_visible) {
			$scrollbar.show()
		}
		if (was_visible && !is_visible) {
			$scrollbar.hide()
		}
	}

	//------------------------------------------------------------------------------------ visibleBar
	/**
	 * @param $scrollbar   jQuery|null
	 * @param total_size   integer
	 * @param visible_size integer
	 * @return boolean
	 */
	const visibleBar = function($scrollbar, total_size, visible_size)
	{
		if (!$scrollbar) {
			return false
		}
		const is_visible = (total_size > Math.ceil(visible_size))
		visibleToCss($scrollbar, is_visible)
		return is_visible
	}

	//----------------------------------------------------------------------------- visibleHorizontal
	/**
	 * @param $element jQuery
	 * @return boolean
	 */
	const visibleHorizontal = function($element)
	{
		const scrollbar = $element.data('scrollbar')
		const $content  = scrollbar.$content
		return visibleBar(scrollbar.$horizontal, maxScroll($content, 'width'), $content.width())
	}

	//------------------------------------------------------------------------------- visibleVertical
	/**
	 * @param $element jQuery
	 * @return boolean
	 */
	const visibleVertical = function($element)
	{
		const scrollbar = $element.data('scrollbar')
		const $body     = scrollbar.$body
		return visibleBar(scrollbar.$vertical, maxScroll($body, 'height'), $body.height())
	}

	$(window).resize(resize)

	//----------------------------------------------------------------------- jQuery scrollBar plugin
	/**
	 * @param settings object
	 * @return jQuery
	 */
	$.fn.scrollBar = function(settings)
	{
		if (settings === 'draw') {
			this.data('scrollbar').dont_move = true
			draw(this)
			this.data('scrollbar').dont_move = false
			scroll(this, 'horizontal', 0)
			scroll(this, 'vertical',   0)
			return this
		}
		if (settings === 'refreshFixedColumns') {
			initTableFixedColumns(this)
			return this
		}
		if (settings === 'scroll') {
			if (arguments[1]) scroll(this, 'horizontal', arguments[1])
			if (arguments[2]) scroll(this, 'vertical',   arguments[1])
			return this
		}

		settings = $.extend({
			arrows:                  false,  // false, true
			draw:                    null,   // event called after redraw
			direction:               'both', // both, horizontal, vertical
			fixed_columns:           '.fix', // jQuery selector for fixed columns
			tr_filter:               '',     // can filter body lines to translate position
			vertical_scrollbar_near: 'both'  // both, foot, head
		}, settings)

		const is_table  = this.is('table')
		const scrollbar = {
			$body:      is_table ? this.children('tbody') : this,
			$content:   is_table ? this.children('tbody, tfoot, thead') : this,
			$foot:      (is_table && this.children('tfoot').length) ? this.children('tfoot') : null,
			$head:      (is_table && this.children('thead').length) ? this.children('thead') : null,
			identifier: next_identifier ++,
			is_table:   is_table,
			settings:   settings
		}
		this.data('scrollbar', scrollbar)

		createScrollBarsDOM(this)
		if (is_table) {
			initTableBefore(this)
		}
		init(this)
		if (is_table) {
			initTableAfter(this)
		}
		draw(this)
		scrollbar.initialized = true

		elements[scrollbar.identifier.toString()] = this
		this.mousewheel(mouseWheel).on('remove', remove)

		return this
	}

})( jQuery )

//-------------------------------------------------------------------------------- window.scrollbar
window.scrollbar = {

	//------------------------------------------------------------------------- window.scrollbar.left
	/**
	 * @param set_left integer
	 * @return integer|number
	 */
	left: function(set_left = undefined)
	{
		const $body = $('body')
		if (set_left !== undefined) {
			document.documentElement.scrollLeft = set_left
			$body.scrollLeft(set_left)
		}
		return document.documentElement.scrollLeft
			? document.documentElement.scrollLeft
			: $body.scrollLeft()
	},

	//-------------------------------------------------------------------------- window.scrollbar.top
	/**
	 * @param set_top integer
	 * @return integer|number
	 */
	top: function(set_top = undefined)
	{
		const $body = $('body')
		if (set_top !== undefined) {
			document.documentElement.scrollTop = set_top
			$body.scrollTop(set_top)
		}
		return document.documentElement.scrollTop
			? document.documentElement.scrollTop
			: $body.scrollTop()
	}

}

/**
 * scrollbar object class structure :
 *
 * @property $angle      jQuery the angle between horizontal and vertical scrollbar
 * @property $body       jQuery the table body
 * @property $content    jQuery the table content, cumulating thead, tbody and tfoot
 * @property $foot       jQuery the table foot (fixed rows)
 * @property $head       jQuery the table head (fixed rows)
 * @property $horizontal jQuery the horizontal scrollbar
 * @property $scrollbars jQuery the horizontal + vertical scrollbars
 * @property $vertical   jQuery the vertical scrollbar
 * @property columns     jQuery[] the table head column cells (td or th)
 */
