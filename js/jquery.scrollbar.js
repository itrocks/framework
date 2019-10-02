
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
		var arrows    = scrollbar.settings.arrows;
		var direction = scrollbar.settings.direction;
		scrollbar.$angle = (direction === 'both')
			? $('<div class="scrollbar angle"/>').appendTo($element)
			: null;
		scrollbar.$horizontal = (['both', 'horizontal'].indexOf(direction) > -1)
			? scrollBar('horizontal', arrows).appendTo($element).mousedown(mouseDown)
			: null;
		scrollbar.$vertical = (['both', 'vertical'].indexOf(direction) > -1)
			? scrollBar('vertical', arrows).appendTo($element).mousedown(mouseDown)
			: null;
		scrollbar.$scrollbars = (direction === 'both')
			? scrollbar.$horizontal.add(scrollbar.$vertical)
			: ((direction === 'horizontal') ? scrollbar.$horizontal : scrollbar.$vertical);
	};

	//------------------------------------------------------------------------------------------ draw
	/**
	 * @param $element jQuery
	 */
	var draw = function($element)
	{
		drawVisibleBars($element);
		drawHorizontal($element);
		drawVertical($element);
	};

	//------------------------------------------------------------------------ drawFixedColumnHeaders
	/**
	 * @param $table jQuery
	 * @param left   integer
	 */
	var drawFixedColumnHeaders = function($table, left)
	{
		var scrollbar = $table.data('scrollbar');
		var $thead    = scrollbar.$head;
		var columns   = scrollbar.columns;
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

	//-------------------------------------------------------------------------------- drawHorizontal
	var drawHorizontal = function($element)
	{
		var scrollbar  = $element.data('scrollbar');
		var $scrollbar = scrollbar.$horizontal;
		if (!$scrollbar) {
			return;
		}
		var $bar    = $scrollbar.find('.bar');
		var $body   = scrollbar.$body;
		var $scroll = $bar.parent();
		// bar size
		var percentage = Math.round(1000 * $body.width() / $body[0].scrollWidth) / 10;
		$bar.css('width', percentage.toString() + '%');
		// bar position
		var max_left = $scroll.width() - $bar.width();
		var new_left = newPosition($scroll, $bar, max_left, 'horizontal', 'left', 'borderLeftWidth');
		if (!new_left && !scrollbar.initialized) {
			new_left = 0;
		}
		if (new_left !== null) {
			$bar.css('left', new_left.toString() + 'px');
			var body_max_left = $body[0].scrollWidth - $body.width();
			var body_left     = Math.round(body_max_left * new_left / max_left);
			scrollbar.$content.scrollLeft(body_left);
			if (scrollbar.is_table) {
				drawFixedColumnHeaders($element, body_left);
			}
		}
	};

	//---------------------------------------------------------------------------------- drawVertical
	var drawVertical = function($element)
	{
		var scrollbar  = $element.data('scrollbar');
		var $scrollbar = scrollbar.$vertical;
		if (!$scrollbar) {
			return;
		}
		var $bar    = $scrollbar.find('.bar');
		var $body   = scrollbar.$body;
		var $scroll = $bar.parent();
		// bar size
		var percentage = Math.round(1000 * $body.height() / $body[0].scrollHeight) / 10;
		$bar.css('height', percentage.toString() + '%');
		// bar position
		var max_top = $scroll.height() - $bar.height();
		var new_top = newPosition($scroll, $bar, max_top, 'vertical', 'top', 'borderTopWidth');
		if (new_top !== null) {
			$bar.css('top', new_top.toString() + 'px');
			var body_max_top = $body[0].scrollHeight - $body.height();
			var body_top     = Math.round(body_max_top * new_top / max_top);
			$body.scrollTop(body_top);
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
			'grid-template':         Q + template.join(Q + SP + Q) + Q,
			'grid-template-columns': columns.join(SP),
			'grid-template-rows':    rows.join(SP)
		});
	};

	//---------------------------------------------------------------------------------- gridTemplate
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
	var initTableAfter = function($table)
	{
		initTableFixedColumns($table);
		$table.children('tbody, tfoot, thead').css({ display: 'block', overflow: 'hidden' });
	};

	//------------------------------------------------------------------------------- initTableBefore
	var initTableBefore = function($table)
	{
		initTableColumnWidths($table);
	};

	//------------------------------------------------------------------------- initTableColumnWidths
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
	var initTableFixedColumns = function($table)
	{
		var scrollbar     = $table.data('scrollbar');
		var fixed_columns = scrollbar.settings.fixed_columns;
		scrollbar.columns = {};
		if (fixed_columns) {
			$table.find(fixed_columns).each(function() {
				var $fixed = $(this);
				var index  = $fixed.prevAll().length + 1;
				if (scrollbar.columns[index] === undefined) {
					scrollbar.columns[index] = $table.find('tr > :nth-child(' + index + ')');
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

	//------------------------------------------------------------------------------------- mouseDown
	var mouseDown = function(event)
	{
		var $scrollbar = $(this);
		if (moving) {
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
	var mouseMove = function(event)
	{
		if (!event.which) {
			return scrollbar.mouseup(event);
		}
		moving.mouse = { left: event.pageX, top: event.pageY };
		draw(moving.$element);
	};

	//--------------------------------------------------------------------------------------- mouseUp
	var mouseUp = function()
	{
		if (!moving) {
			return;
		}
		$(document).off('mousemove', mouseMove).off('mouseup', mouseUp);
		moving = null;
	};

	//----------------------------------------------------------------------------------- newPosition
	/**
	 * @param $scroll             jQuery
	 * @param $bar                jQuery
	 * @param max_position        integer
	 * @param direction           string @values horizontal, vertical
	 * @param position            string @values left, top
	 * @param borderPositionWidth string @values borderLeftWidth, borderTopWidth
	 * @returns integer|number|null
	 */
	var newPosition = function($scroll, $bar, max_position, direction, position, borderPositionWidth)
	{
		var border_width = parseInt(window.getComputedStyle($scroll[0])[borderPositionWidth]);
		if (!moving || (moving.direction !== direction)) {
			var current_position = $bar.offset()[position] - $scroll.offset()[position] - border_width;
			return (current_position > max_position) ? max_position : null;
		}
		var difference = moving.mouse[position] - moving.from[position];
		var old_top    = $bar.offset()[position] - $scroll.offset()[position] - border_width;
		moving.from[position] += difference;
		return Math.max(0, Math.min(max_position, old_top + difference));
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

	//--------------------------------------------------------------- horizontal / vertical scrollbar
	/**
	 * @param direction string @values horizontal, vertical
	 * @param arrows    boolean
	 * @returns jQuery
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
	 * @returns boolean
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
	$.fn.scrollBar = function(settings)
	{
		settings = $.extend({
			arrows:                  false,  // false, true
			direction:               'both', // both, horizontal, vertical
			fixed_columns:           '.fix', // jQuery selector for fixed columns
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
		this.on('remove', remove);
	};

})( jQuery );

//-------------------------------------------------------------------------------- window.scrollbar
window.scrollbar = {

	//------------------------------------------------------------------------- window.scrollbar.left
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
