
//-------------------------------------------------------------------------------- jQuery.scrollbar
(function($)
{

	//--------------------------------------------------------------------------- createScrollBarsDOM
	/**
	 * @param $element jQuery
	 */
	let createScrollBarsDOM = function($element)
	{
		let scrollbar = $element.data('scrollbar')
		let settings  = scrollbar.settings
		let arrows    = settings.arrows
		let direction = settings.direction
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
	let createEvents = function($scrollbar)
	{
		return $scrollbar.mousedown(mouseDown).mouseout(mouseOutStyle).mousemove(mouseMoveStyle)
	}

	//------------------------------------------------------------------------------------------ draw
	/**
	 * @param $element jQuery
	 */
	let draw = function($element)
	{
		let scrollbar = $element.data('scrollbar')
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
	let drawBar = function(scrollbar, direction, position, size)
	{
		let $scrollbar = scrollbar['$' + direction]
		if (!$scrollbar) {
			return false
		}
		let $bar     = $scrollbar.find('.bar')
		let $content = (size === 'width') ? scrollbar.$content : scrollbar.$body
		let $scroll  = $bar.parent()
		// sizes
		let bar_size            = $bar[size].call($bar)
		let content_scroll_size = maxScroll($content, size)
		let content_size        = $content[size].call($content)
		let scroll_size         = $scroll[size].call($scroll)
		// percentage bar size calculation
		let percentage = Math.round(1000 * content_size / content_scroll_size) / 10
		$bar.css(size, percentage.toString() + '%')
		if (scrollbar.dont_move) {
			return true
		}
		// bar position
		let max_position = scroll_size - bar_size
		let new_position = drawBarGetPosition($scroll, $bar, direction, position, size, max_position)
		if (!new_position && !scrollbar.initialized) {
			new_position = 0
		}
		if (new_position !== null) {
			$bar.css(position, new_position.toString() + 'px')
			let content_max_position = content_scroll_size - content_size
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
	let drawBarGetPosition = function($scroll, $bar, direction, position, size, max_position)
	{
		let bar_position          = $bar.offset()[position]
		let scroll_position       = $scroll.offset()[position]
		let border_position_width = 'border' + position.ucfirst() + 'Width'
		let scroll_style          = window.getComputedStyle($scroll[0])
		let border_width          = parseInt(scroll_style[border_position_width])
		let old_top               = bar_position - scroll_position - border_width
		if (!moving || (moving.direction !== direction)) {
			return (old_top > max_position) ? max_position : null
		}
		let difference         = moving.mouse[position] - moving.from[position]
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
	let drawContentScrollPosition = function(
		scrollbar, position, new_position, max_position, body_max_position
	) {
		let body_position         = Math.round(body_max_position * new_position / max_position)
		let contentScrollPosition = scrollbar.$content['scroll' + position.ucfirst()]
		contentScrollPosition.call(scrollbar.$content, body_position)
	}

	//------------------------------------------------------------------------ drawFixedColumnHeaders
	/**
	 * @param $table jQuery
	 */
	let drawFixedColumnHeaders = function($table)
	{
		let scrollbar = $table.data('scrollbar')
		let $head     = scrollbar.$head
		let columns   = scrollbar.columns
		let left      = $head.scrollLeft()
		let previous  = 0
		let right     = left + $head.width() - maxScroll($head, 'width')
		for (let index in columns) if (columns.hasOwnProperty(index)) {
			let $column = columns[index]
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
	let drawVisibleBars = function($element)
	{
		let scrollbar = $element.data('scrollbar')

		let visibility = isVisible(scrollbar.$horizontal).toString()
			+ isVisible(scrollbar.$vertical).toString()
		visibleVertical($element)
		let horizontal = visibleHorizontal($element)
		let vertical   = visibleVertical($element)
		visibleAngle($element)
		if (scrollbar.initialized && ((horizontal.toString() + vertical.toString()) === visibility)) {
			return
		}

		let columns  = scrollbar.grid.columns.slice(0)
		let rows     = scrollbar.grid.rows.slice(0)
		let template = scrollbar.grid.template.slice(0)
		if (!horizontal) {
			rows.pop()
			template.pop()
		}
		if (!vertical) {
			columns.pop()
			for (let index in template) if (template.hasOwnProperty(index)) {
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
	let fixedColumnsWidth = function(columns)
	{
		let total_width = 0
		for (let index in columns) if (columns.hasOwnProperty(index)) {
			let $column = columns[index]
			total_width += $column.width()
		}
		return total_width
	}

	//---------------------------------------------------------------------------------- gridTemplate
	/**
	 * @param $element jQuery
	 * @return object
	 */
	let gridTemplate = function($element)
	{
		let scrollbar = $element.data('scrollbar')
		let weight    = parseInt(scrollbar.$scrollbars.css('--weight'))
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
	let gridTemplateTable = function($element)
	{
		let scrollbar = $element.data('scrollbar')
		let near      = scrollbar.settings.vertical_scrollbar_near
		let near_foot = (['both', 'foot'].indexOf(near) > -1) ? 'vertical' : 'foot'
		let near_head = (['both', 'head'].indexOf(near) > -1) ? 'vertical' : 'head'
		let weight    = parseInt(scrollbar.$scrollbars.css('--weight'))

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
	let init = function($element)
	{
		let scrollbar  = $element.data('scrollbar')
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
	let initTableAfter = function($table)
	{
		initTableFixedColumns($table)
		$table.children('tbody, tfoot, thead').css({ display: 'block', overflow: 'hidden' })
	}

	//------------------------------------------------------------------------------- initTableBefore
	/**
	 * @param $table jQuery
	 */
	let initTableBefore = function($table)
	{
		initTableColumnWidths($table)
	}

	//------------------------------------------------------------------------- initTableColumnWidths
	/**
	 * @param $table jQuery
	 */
	let initTableColumnWidths = function($table)
	{
		let $tbody    = $table.children('tbody')
		let $tfoot    = $table.children('tfoot')
		let $thead    = $table.children('thead')
		let $tbody_tr = $tbody.children('tr:first-child')
		let $tfoot_tr = $tfoot.children('tr:first-child')
		let $thead_tr = $thead.children('tr:first-child')
		let $tr       = $thead_tr.length ? $thead_tr : ($tbody_tr.length ? $tbody_tr : $tfoot_tr)
		let trs       = [$tbody_tr, $tfoot_tr, $thead_tr]
		let widths    = []

		$tr.children().each(function() {
			widths.push($(this).width())
		})
		for (let tr in trs) if (trs.hasOwnProperty(tr)) {
			let key = 0
			trs[tr].children().each(function() {
				$(this).css('min-width', widths[key++])
			})
		}
	}

	//------------------------------------------------------------------------- initTableFixedColumns
	/**
	 * @param $table jQuery
	 */
	let initTableFixedColumns = function($table)
	{
		let scrollbar     = $table.data('scrollbar')
		let settings      = scrollbar.settings
		let fixed_columns = settings.fixed_columns
		let tr_selector   = 'tr' + settings.tr_filter
		scrollbar.columns = {}
		if (fixed_columns) {
			$table.find(fixed_columns).each(function() {
				let $fixed = $(this)
				let index  = $fixed.prevAll().length + 1
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
	let isVisible = function($element)
	{
		return $element && $element.is(':visible')
	}

	//------------------------------------------------------------------------------------- maxScroll
	/**
	 * Returns the maximum value for scrollHeight or scrollWidth of $elements
	 *
	 * @param $elements jQuery
	 * @param size      string @values height, width
	 * @returns float|number
	 */
	let maxScroll = function($elements, size)
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
	let mouseClickMove = function(event, $scrollbar)
	{
		let $element  = $scrollbar.parent()
		let scrollbar = $element.data('scrollbar')

		let direction,  fixed_columns_size, mouse_position, position, size
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

		let $bar     = $scrollbar.find('.bar')
		let bar_size = $bar[size].call($bar)
		let offset   = $bar.offset()
		let start    = offset[position]
		let stop     = start + bar_size - 1
		if ((mouse_position >= start) && (mouse_position <= stop)) {
			return false
		}

		let $body     = scrollbar.$body
		let body_size = $body[size].call($body)
		let body_move = body_size - fixed_columns_size

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
	let mouseDown = function(event)
	{
		if (moving) {
			return
		}
		let $scrollbar = $(this)
		let $bar       = $scrollbar.find('.bar')
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
	let mouseMove = function(event)
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
	let mouseMoveStyle = function(event)
	{
		let $scrollbar = $(this)
		let $bar       = $scrollbar.find('.bar')
		let mouse, start, stop
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
	let mouseOutStyle = function()
	{
		let $scrollbar = $(this)
		if (moving) {
			return
		}
		$scrollbar.find('.bar').removeClass('hover')
	}

	//--------------------------------------------------------------------------------------- mouseUp
	let mouseUp = function()
	{
		if (!moving) {
			return
		}
		$(document).off('mousemove', mouseMove).off('mouseup', mouseUp)
		moving.$scrollbar.find('.bar').removeClass('moving')
		moving = null
	}

	//------------------------------------------------------------------------------------ mouseWheel
	let mouseWheel = function(event)
	{
		let $element = $(this)
		// noinspection JSUnresolvedVariable deltaFactor exists
		let until = Math.abs(event.deltaFactor * (event.deltaY ? event.deltaY : event.deltaX))
		let speed = Math.round(until / 12)
		event.deltaX /= -Math.abs(event.deltaX)
		event.deltaY /= -Math.abs(event.deltaY)
		let animate  = function()
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
	let remove = function()
	{
		let element_identifier = $(this).data('scrollbar').identifier.toString()
		let old_elements       = elements
		elements               = {}
		for (let identifier in old_elements) if (old_elements.hasOwnProperty(identifier)) {
			if (identifier !== element_identifier) {
				elements[identifier] = old_elements[identifier]
			}
		}
	}

	//---------------------------------------------------------------------------------------- resize
	let resize = function()
	{
		for(let index in elements) if (elements.hasOwnProperty(index)) {
			let $element  = elements[index]
			let scrollbar = $element.data('scrollbar')
			let distance  = parseInt(scrollbar.$horizontal.children('div').css('left'))
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
	let scroll = function($element, direction, distance)
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

		let scrollbar        = $element.data('scrollbar')
		let $scrollbar       = scrollbar['$' + direction]
		let $bar             = $scrollbar.find('.bar')
		let $body            = scrollbar.$body
		let $content         = scrollbar.$content
		let bar_size         = $bar[size].call($bar)
		let body_position    = maxScroll($body, position)
		let body_size        = $body[size].call($body)
		let body_scroll_size = maxScroll($body, size)
		let scrollPosition   = $body['scroll' + position.ucfirst()]

		body_position  = (distance < 0)
			? Math.max(body_position + distance, 0)
			: Math.min(body_position + distance, body_scroll_size - body_size)
		let bar_left = Math.round(body_position * bar_size / body_size)
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
	let scrollBar = function(direction, arrows)
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
	let visibleAngle = function($element)
	{
		let scrollbar  = $element.data('scrollbar')
		let $scrollbar = scrollbar.$angle
		if (!$scrollbar) {
			return false
		}
		let is_visible = (scrollbar.$horizontal.is(':visible') && scrollbar.$vertical.is(':visible'))
		visibleToCss($scrollbar, is_visible)
		return is_visible
	}

	//---------------------------------------------------------------------------------- visibleToCss
	/**
	 * @param $scrollbar jQuery
	 * @param is_visible boolean
	 */
	let visibleToCss = function($scrollbar, is_visible)
	{
		let was_visible = $scrollbar.is(':visible')
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
	let visibleBar = function($scrollbar, total_size, visible_size)
	{
		if (!$scrollbar) {
			return false
		}
		let is_visible = (total_size > Math.ceil(visible_size))
		visibleToCss($scrollbar, is_visible)
		return is_visible
	}

	//----------------------------------------------------------------------------- visibleHorizontal
	/**
	 * @param $element jQuery
	 * @return boolean
	 */
	let visibleHorizontal = function($element)
	{
		let scrollbar = $element.data('scrollbar')
		let $content  = scrollbar.$content
		return visibleBar(scrollbar.$horizontal, maxScroll($content, 'width'), $content.width())
	}

	//------------------------------------------------------------------------------- visibleVertical
	/**
	 * @param $element jQuery
	 * @return boolean
	 */
	let visibleVertical = function($element)
	{
		let scrollbar = $element.data('scrollbar')
		let $body     = scrollbar.$body
		return visibleBar(scrollbar.$vertical, maxScroll($body, 'height'), $body.height())
	}

	//------------------------------------------------------------------- plugin common data & events
	let elements        = {}
	let moving          = null
	let next_identifier = 0

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

		let is_table  = this.is('table')
		let scrollbar = {
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
	left: function(set_left)
	{
		let $body = $('body')
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
	top: function(set_top)
	{
		let $body = $('body')
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
