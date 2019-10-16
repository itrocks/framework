
//-------------------------------------------------------------------------------- jQuery.scrollbar
(function($)
{

	//--------------------------------------------------------------------------- createScrollBarsDOM
	/**
	 * @param $element jQuery
	 */
	var createScrollBarsDOM = function($element)
	{
		var scrollbar = $element.data('scrollbar');
		var settings  = scrollbar.settings;
		var arrows    = settings.arrows;
		var direction = settings.direction;
		scrollbar.$angle = (direction === 'both')
			? $('<div class="scrollbar angle"/>').appendTo($element)
			: null;
		scrollbar.$horizontal = (['both', 'horizontal'].indexOf(direction) > -1)
			? createEvents(scrollBar('horizontal', arrows).appendTo($element))
			: null;
		scrollbar.$vertical = (['both', 'vertical'].indexOf(direction) > -1)
			? createEvents(scrollBar('vertical', arrows).appendTo($element))
			: null;
		scrollbar.$scrollbars = (direction === 'both')
			? scrollbar.$horizontal.add(scrollbar.$vertical)
			: ((direction === 'horizontal') ? scrollbar.$horizontal : scrollbar.$vertical);
	};

	//---------------------------------------------------------------------------------- createEvents
	var createEvents = function($scrollbar)
	{
		return $scrollbar.mousedown(mouseDown).mouseout(mouseOutStyle).mousemove(mouseMoveStyle);
	};

	//------------------------------------------------------------------------------------------ draw
	/**
	 * @param $element jQuery
	 */
	var draw = function($element)
	{
		var scrollbar = $element.data('scrollbar');
		drawVisibleBars($element);
		drawBar(scrollbar, 'vertical', 'top', 'height');
		if (drawBar(scrollbar, 'horizontal', 'left', 'width') && scrollbar.is_table) {
			drawFixedColumnHeaders($element);
		}
		if (scrollbar.settings.draw) {
			scrollbar.settings.draw.call($element);
		}
	};

	//--------------------------------------------------------------------------------------- drawBar
	/**
	 * @param scrollbar object
	 * @param direction string @values horizontal, vertical
	 * @param position  string @values left, top
	 * @param size      string @values height, width
	 * @return boolean
	 */
	var drawBar = function(scrollbar, direction, position, size)
	{
		var $scrollbar = scrollbar['$' + direction];
		if (!$scrollbar) {
			return false;
		}
		var $bar             = $scrollbar.find('.bar');
		var $body            = scrollbar.$body;
		var $scroll          = $bar.parent();
		var bar_size         = $bar[size].call($bar);
		var body_scroll_size = $body[0]['scroll' + size.ucfirst()];
		var body_size        = $body[size].call($body);
		var scroll_size      = $scroll[size].call($scroll);
		// bar size
		var percentage = Math.round(1000 * body_size / body_scroll_size) / 10;
		$bar.css(size, percentage.toString() + '%');
		// bar position
		var max_position = scroll_size - bar_size;
		var new_position = drawBarGetPosition($scroll, $bar, direction, position, size, max_position);
		if (!new_position && !scrollbar.initialized) {
			new_position = 0;
		}
		if (new_position !== null) {
			$bar.css(position, new_position.toString() + 'px');
			var body_max_position = body_scroll_size - body_size;
			drawContentScrollPosition(scrollbar, position, new_position, max_position, body_max_position);
		}
		return true;
	};

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
	var drawBarGetPosition = function($scroll, $bar, direction, position, size, max_position)
	{
		var bar_position          = $bar.offset()[position];
		var scroll_position       = $scroll.offset()[position];
		var border_position_width = 'border' + position.ucfirst() + 'Width';
		var scroll_style          = window.getComputedStyle($scroll[0]);
		var border_width          = parseInt(scroll_style[border_position_width]);
		var old_top               = bar_position - scroll_position - border_width;
		if (!moving || (moving.direction !== direction)) {
			return (old_top > max_position) ? max_position : null;
		}
		var difference         = moving.mouse[position] - moving.from[position];
		moving.from[position] += difference;
		return Math.max(0, Math.min(max_position, old_top + difference));
	};

	//--------------------------------------------------------------------- drawContentScrollPosition
	/**
	 * @param scrollbar         object
	 * @param position          string @values left, top
	 * @param new_position      integer
	 * @param max_position      integer
	 * @param body_max_position integer
	 */
	var drawContentScrollPosition = function(
		scrollbar, position, new_position, max_position, body_max_position
	) {
		var body_position         = Math.round(body_max_position * new_position / max_position);
		var contentScrollPosition = scrollbar.$content['scroll' + position.ucfirst()];
		contentScrollPosition.call(scrollbar.$content, body_position);
	};

	//------------------------------------------------------------------------ drawFixedColumnHeaders
	/**
	 * @param $table jQuery
	 */
	var drawFixedColumnHeaders = function($table)
	{
		var scrollbar = $table.data('scrollbar');
		var $thead    = scrollbar.$head;
		var columns   = scrollbar.columns;
		var left      = scrollbar.$body.scrollLeft();
		var previous  = 0;
		var right     = left + $thead.width() - $thead[0].scrollWidth;
		for (var index in columns) if (columns.hasOwnProperty(index)) {
			var $column = columns[index];
			// left
			if (parseInt(index) === ++previous) {
				$column.css('transform', 'translateX(' + left + 'px)');
				left += $column.width();
			}
			// right
			else {
				$column.css('transform', 'translateX(' + right + 'px');
				right -= $column.width();
			}
		}
	};

	//------------------------------------------------------------------------------- drawVisibleBars
	/**
	 * Calculate if scrollbars are visible or not, change css to draw them or not
	 *
	 * @param $element jQuery
	 */
	var drawVisibleBars = function($element)
	{
		var scrollbar = $element.data('scrollbar');

		var visibility = isVisible(scrollbar.$horizontal).toString()
			+ isVisible(scrollbar.$vertical).toString();
		visibleVertical($element);
		var horizontal = visibleHorizontal($element);
		var vertical   = visibleVertical($element);
		visibleAngle($element);
		if (scrollbar.initialized && ((horizontal.toString() + vertical.toString()) === visibility)) {
			return;
		}

		var columns  = scrollbar.grid.columns.slice(0);
		var rows     = scrollbar.grid.rows.slice(0);
		var template = scrollbar.grid.template.slice(0);
		if (!horizontal) {
			rows.pop();
			template.pop();
		}
		if (!vertical) {
			columns.pop();
			for (var index in template) if (template.hasOwnProperty(index)) {
				template[index] = template[index].lParse(SP);
			}
		}
		$element.css({
			'grid-template-areas':   Q + template.join(Q + SP + Q) + Q,
			'grid-template-columns': columns.join(SP),
			'grid-template-rows':    rows.join(SP)
		});
	};

	//----------------------------------------------------------------------------- fixedColumnsWidth
	/**
	 * Calculates the cumulative width of fixed columns
	 *
	 * @param columns jQuery[]
	 * @return integer
	 */
	var fixedColumnsWidth = function(columns)
	{
		var total_width = 0;
		for (var index in columns) if (columns.hasOwnProperty(index)) {
			var $column = columns[index];
			total_width += $column.width();
		}
		return total_width;
	};

	//---------------------------------------------------------------------------------- gridTemplate
	/**
	 * @param $element jQuery
	 * @return object
	 */
	var gridTemplate = function($element)
	{
		var scrollbar = $element.data('scrollbar');
		var weight    = parseInt(scrollbar.$scrollbars.css('--weight'));
		return {
			columns:  ['1fr', weight.toString() + 'px'],
			rows:     ['1fr', weight.toString() + 'px'],
			template: [
				'body vertical',
				'horizontal angle'
			]
		}
	};

	//----------------------------------------------------------------------------- gridTemplateTable
	/**
	 * @param $element jQuery
	 * @return object
	 */
	var gridTemplateTable = function($element)
	{
		var scrollbar = $element.data('scrollbar');
		var near      = scrollbar.settings.vertical_scrollbar_near;
		var near_foot = (['both', 'foot'].indexOf(near) > -1) ? 'vertical' : 'foot';
		var near_head = (['both', 'head'].indexOf(near) > -1) ? 'vertical' : 'head';
		var weight    = parseInt(scrollbar.$scrollbars.css('--weight'));

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
		};
	};

	//------------------------------------------------------------------------------------------ init
	/**
	 * @param $element jQuery
	 */
	var init = function($element)
	{
		var scrollbar  = $element.data('scrollbar');
		scrollbar.grid = $element.is('table')
			? gridTemplateTable($element, scrollbar.settings)
			: gridTemplate($element);

		$element.css({ display: 'grid', position: 'relative' });
		scrollbar.$body.css('grid-area', 'body');
		if (scrollbar.$foot) {
			scrollbar.$foot.css('grid-area', 'foot');
		}
		if (scrollbar.$head) {
			scrollbar.$head.css('grid-area', 'head');
		}
	};

	//-------------------------------------------------------------------------------- initTableAfter
	/**
	 * @param $table jQuery
	 */
	var initTableAfter = function($table)
	{
		initTableFixedColumns($table);
		$table.children('tbody, tfoot, thead').css({ display: 'block', overflow: 'hidden' });
	};

	//------------------------------------------------------------------------------- initTableBefore
	/**
	 * @param $table jQuery
	 */
	var initTableBefore = function($table)
	{
		initTableColumnWidths($table);
	};

	//------------------------------------------------------------------------- initTableColumnWidths
	/**
	 * @param $table jQuery
	 */
	var initTableColumnWidths = function($table)
	{
		var $tbody    = $table.children('tbody');
		var $tfoot    = $table.children('tfoot');
		var $thead    = $table.children('thead');
		var $tbody_tr = $tbody.children('tr:first-child');
		var $tfoot_tr = $tfoot.children('tr:first-child');
		var $thead_tr = $thead.children('tr:first-child');
		var $tr       = $thead_tr.length ? $thead_tr : ($tbody_tr.length ? $tbody_tr : $tfoot_tr);
		var trs       = [$tbody_tr, $tfoot_tr, $thead_tr];
		var widths    = [];

		$tr.children().each(function() {
			widths.push($(this).width());
		});
		for (var tr in trs) if (trs.hasOwnProperty(tr)) {
			var key = 0;
			trs[tr].children().each(function() {
				$(this).css('min-width', widths[key++]);
			});
		}
	};

	//------------------------------------------------------------------------- initTableFixedColumns
	/**
	 * @param $table jQuery
	 */
	var initTableFixedColumns = function($table)
	{
		var scrollbar     = $table.data('scrollbar');
		var settings      = scrollbar.settings;
		var fixed_columns = settings.fixed_columns;
		var tr_selector   = 'tr' + settings.tr_filter;
		scrollbar.columns = {};
		if (fixed_columns) {
			$table.find(fixed_columns).each(function() {
				var $fixed = $(this);
				var index  = $fixed.prevAll().length + 1;
				if (scrollbar.columns[index] === undefined) {
					scrollbar.columns[index] = $table.find(tr_selector + ' > :nth-child(' + index + ')');
				}
			});
		}
	};

	//------------------------------------------------------------------------------------- isVisible
	/**
	 * @param $element jQuery|null
	 * @return boolean
	 */
	var isVisible = function($element)
	{
		return $element && $element.is(':visible');
	};

	//-------------------------------------------------------------------------------- mouseClickMove
	/**
	 * @param event      object
	 * @param $scrollbar jQuery
	 * @return boolean
	 */
	var mouseClickMove = function(event, $scrollbar)
	{
		var $element  = $scrollbar.parent();
		var scrollbar = $element.data('scrollbar');

		var direction,  fixed_columns_size, mouse_position, position, size;
		if ($scrollbar.is('.horizontal')) {
			direction          = 'horizontal';
			fixed_columns_size = fixedColumnsWidth(scrollbar.columns);
			mouse_position     = event.pageX;
			position           = 'left';
			size               = 'width';
		}
		else {
			direction          = 'vertical';
			fixed_columns_size = 0;
			mouse_position     = event.pageY;
			position           = 'top';
			size               = 'height';
		}

		var $bar     = $scrollbar.find('.bar');
		var bar_size = $bar[size].call($bar);
		var offset   = $bar.offset();
		var start    = offset[position];
		var stop     = start + bar_size - 1;
		if ((mouse_position >= start) && (mouse_position <= stop)) {
			return false;
		}

		var $body     = scrollbar.$body;
		var body_size = $body[size].call($body);
		var body_move = body_size - fixed_columns_size;

		if (mouse_position < start) {
			body_move = -body_move;
		}
		scroll($element, direction, body_move);

		return true;
	};

	//------------------------------------------------------------------------------------- mouseDown
	/**
	 * @param event object
	 */
	var mouseDown = function(event)
	{
		if (moving) {
			return;
		}
		var $scrollbar = $(this);
		var $bar       = $scrollbar.find('.bar');
		$bar.addClass('moving');
		if (mouseClickMove(event, $scrollbar)) {
			setTimeout(function() { $bar.removeClass('moving'); }, 100);
			return;
		}
		moving = {
			direction:  $scrollbar.is('.horizontal') ? 'horizontal' : 'vertical',
			from:       { left: event.pageX, top: event.pageY },
			$element:   $scrollbar.parent(),
			$scrollbar: $scrollbar
		};
		$(document).mousemove(mouseMove).mouseup(mouseUp);
		event.preventDefault();
	};

	//------------------------------------------------------------------------------------- mouseMove
	/**
	 * @param event object
	 */
	var mouseMove = function(event)
	{
		if (!event.which) {
			return scrollbar.mouseup(event);
		}
		moving.mouse = { left: event.pageX, top: event.pageY };
		draw(moving.$element);
	};

	//-------------------------------------------------------------------------------- mouseMoveStyle
	/**
	 * @param event object
	 */
	var mouseMoveStyle = function(event)
	{
		var $scrollbar = $(this);
		var $bar       = $scrollbar.find('.bar');
		var mouse, start, stop;
		if ($scrollbar.is('.horizontal')) {
			mouse = event.pageX;
			start = $bar.offset().left;
			stop  = start + $bar.width() - 1;
		}
		else {
			mouse = event.pageY;
			start = $bar.offset().top;
			stop  = start + $bar.height() - 1;
		}
		if ((mouse < start) || (mouse > stop)) {
			mouseOutStyle.call($scrollbar, event);
			return;
		}
		if (moving) {
			return;
		}
		$bar.addClass('hover');
	};

	//--------------------------------------------------------------------------------- mouseOutStyle
	var mouseOutStyle = function()
	{
		var $scrollbar = $(this);
		if (moving) {
			return;
		}
		$scrollbar.find('.bar').removeClass('hover');
	};

	//--------------------------------------------------------------------------------------- mouseUp
	var mouseUp = function()
	{
		if (!moving) {
			return;
		}
		$(document).off('mousemove', mouseMove).off('mouseup', mouseUp);
		moving.$scrollbar.find('.bar').removeClass('moving');
		moving = null;
	};

	//------------------------------------------------------------------------------------ mouseWheel
	var mouseWheel = function(event)
	{
		var $element = $(this);
		// noinspection JSUnresolvedVariable deltaFactor exists
		var until = Math.abs(event.deltaFactor * (event.deltaY ? event.deltaY : event.deltaX));
		var speed = Math.round(until / 12);
		event.deltaX /= -Math.abs(event.deltaX);
		event.deltaY /= -Math.abs(event.deltaY);
		var animate  = function()
		{
			speed = Math.min(speed, until);
			if (event.deltaX) {
				scroll($element, 'horizontal', speed * event.deltaX);
			}
			if (event.deltaY) {
				scroll($element, 'vertical', speed * event.deltaY);
			}
			until -= speed;
			if (until > 0) {
				setTimeout(animate, 10);
			}
		};
		animate();
	};

	//---------------------------------------------------------------------------------------- remove
	var remove = function()
	{
		var element_identifier = $(this).data('scrollbar').identifier.toString();
		var old_elements       = elements;
		elements               = {};
		for (var identifier in old_elements) if (old_elements.hasOwnProperty(identifier)) {
			if (identifier !== element_identifier) {
				elements[identifier] = old_elements[identifier];
			}
		}
	};

	//---------------------------------------------------------------------------------------- resize
	var resize = function()
	{
		for(var index in elements) if (elements.hasOwnProperty(index)) {
			draw(elements[index]);
		}
	};

	//---------------------------------------------------------------------------------------- scroll
	/**
	 * Scroll content
	 *
	 * @param $element  jQuery
	 * @param direction string @values horizontal, vertical
	 * @param distance  integer
	 */
	var scroll = function($element, direction, distance)
	{
		var position, size;
		if (direction === 'horizontal') {
			position = 'left';
			size     = 'width';
		}
		else {
			position = 'top';
			size     = 'height';
		}

		var scrollbar        = $element.data('scrollbar');
		var $scrollbar       = scrollbar['$' + direction];
		var $bar             = $scrollbar.find('.bar');
		var $body            = scrollbar.$body;
		var $content         = scrollbar.$content;
		var bar_size         = $bar[size].call($bar);
		var body_position    = $body[0]['scroll' + position.ucfirst()];
		var body_size        = $body[size].call($body);
		var body_scroll_size = $body[0]['scroll' + size.ucfirst()];
		var scrollPosition   = $body['scroll' + position.ucfirst()];

		body_position  = (distance < 0)
			? Math.max(body_position + distance, 0)
			: Math.min(body_position + distance, body_scroll_size - body_size);
		var bar_left = Math.round(body_position * bar_size / body_size);
		scrollPosition.call($content, body_position);
		$bar.css(position, bar_left.toString() + 'px');

		if (scrollbar.is_table && (direction === 'horizontal')) {
			drawFixedColumnHeaders($element);
		}

		if (scrollbar.settings.draw) {
			scrollbar.settings.draw.call($element);
		}
	};

	//--------------------------------------------------------------- horizontal / vertical scrollbar
	/**
	 * @param direction string @values horizontal, vertical
	 * @param arrows    boolean
	 * @return jQuery
	 */
	var scrollBar = function(direction, arrows)
	{
		return $(
			'<div class="' + (arrows ? 'arrows ' : '') + direction + ' scrollbar">'
			+ (arrows ? '<div class="previous"/><div class="scroll">' : '')
			+ '<div class="bar"/>'
			+ (arrows ? '</div><div class="next"/>' : '')
			+ '</div>'
		);
	};

	//---------------------------------------------------------------------------------- visibleAngle
	/**
	 * Must be called after visibleHorizontal() and visibleVertical()
	 *
	 * @param $element jQuery
	 * @return boolean
	 */
	var visibleAngle = function($element)
	{
		var scrollbar  = $element.data('scrollbar');
		var $scrollbar = scrollbar.$angle;
		if (!$scrollbar) {
			return false;
		}
		var is_visible = (scrollbar.$horizontal.is(':visible') && scrollbar.$vertical.is(':visible'));
		visibleToCss($scrollbar, is_visible);
		return is_visible;
	};

	//---------------------------------------------------------------------------------- visibleToCss
	/**
	 * @param $scrollbar jQuery
	 * @param is_visible boolean
	 */
	var visibleToCss = function($scrollbar, is_visible)
	{
		var was_visible = $scrollbar.is(':visible');
		if (is_visible && !was_visible) {
			$scrollbar.show();
		}
		if (was_visible && !is_visible) {
			$scrollbar.hide();
		}
	};

	//------------------------------------------------------------------------------------ visibleBar
	/**
	 * @param $body        jQuery
	 * @param $scrollbar   jQuery|null
	 * @param total_size   integer
	 * @param visible_size integer
	 * @return boolean
	 */
	var visibleBar = function($body, $scrollbar, total_size, visible_size)
	{
		if (!$scrollbar) {
			return false;
		}
		var is_visible = (total_size > Math.ceil(visible_size));
		visibleToCss($scrollbar, is_visible);
		return is_visible;
	};

	//----------------------------------------------------------------------------- visibleHorizontal
	/**
	 * @param $element jQuery
	 * @return boolean
	 */
	var visibleHorizontal = function($element)
	{
		var scrollbar = $element.data('scrollbar');
		var $body     = scrollbar.$body;
		return visibleBar($body, scrollbar.$horizontal, $body[0].scrollWidth, $body.width());
	};

	//------------------------------------------------------------------------------- visibleVertical
	/**
	 * @param $element jQuery
	 * @return boolean
	 */
	var visibleVertical = function($element)
	{
		var scrollbar = $element.data('scrollbar');
		var $body     = scrollbar.$body;
		return visibleBar($body, scrollbar.$vertical, $body[0].scrollHeight, $body.height());
	};

	//------------------------------------------------------------------- plugin common data & events
	var elements        = {};
	var moving          = null;
	var next_identifier = 0;

	$(window).resize(resize);

	//----------------------------------------------------------------------- jQuery scrollBar plugin
	/**
	 * @param settings object
	 * @return jQuery
	 */
	$.fn.scrollBar = function(settings)
	{
		if (settings === 'draw') {
			draw(this);
			scroll(this, 'horizontal', 0);
			scroll(this, 'vertical',   0);
			return this;
		}

		settings = $.extend({
			arrows:                  false,  // false, true
			draw:                    null,   // event called after redraw
			direction:               'both', // both, horizontal, vertical
			fixed_columns:           '.fix', // jQuery selector for fixed columns
			tr_filter:               '',     // can filter body lines to translate position
			vertical_scrollbar_near: 'both'  // both, foot, head
		}, settings);

		var is_table  = this.is('table');
		var scrollbar = {
			$body:      is_table ? this.children('tbody') : this,
			$content:   is_table ? this.children('tbody, tfoot, thead') : this,
			$foot:      (is_table && this.children('tfoot').length) ? this.children('tfoot') : null,
			$head:      (is_table && this.children('thead').length) ? this.children('thead') : null,
			identifier: next_identifier ++,
			is_table:   is_table,
			settings:   settings
		};
		this.data('scrollbar', scrollbar);

		createScrollBarsDOM(this);
		if (is_table) {
			initTableBefore(this);
		}
		init(this);
		if (is_table) {
			initTableAfter(this);
		}
		draw(this);
		scrollbar.initialized = true;

		elements[scrollbar.identifier.toString()] = this;
		this.mousewheel(mouseWheel).on('remove', remove);

		return this;
	};

})( jQuery );

//-------------------------------------------------------------------------------- window.scrollbar
window.scrollbar = {

	//------------------------------------------------------------------------- window.scrollbar.left
	/**
	 * @param set_left integer
	 * @return integer
	 */
	left: function(set_left)
	{
		var $body = $('body');
		if (set_left !== undefined) {
			document.documentElement.scrollLeft = set_left;
			$body.scrollLeft(set_left);
		}
		return document.documentElement.scrollLeft
			? document.documentElement.scrollLeft
			: $body.scrollLeft();
	},

	//-------------------------------------------------------------------------- window.scrollbar.top
	/**
	 * @param set_top integer
	 * @return integer
	 */
	top: function(set_top)
	{
		var $body = $('body');
		if (set_top !== undefined) {
			document.documentElement.scrollTop = set_top;
			$body.scrollTop(set_top);
		}
		return document.documentElement.scrollTop
			? document.documentElement.scrollTop
			: $body.scrollTop();
	}

};
