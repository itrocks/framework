
//---------------------------------------------------------------------- window.scrollbar shortcuts
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

//-------------------------------------------------------------------------------- jQuery.scrollbar
(function($)
{
	var $move_scrollbar = null;
	var initial_mouse   = {};

	//-------------------------------------------------------------------------- $scrollbar mousedown
	var mousedown = function(event)
	{
		if ($move_scrollbar) {
			return;
		}
		$move_scrollbar = $(this);
		initial_mouse   = { x: event.pageX, y: event.pageY };
		$(document).mousemove(mousemove).mouseup(mouseup);
		event.preventDefault();
	};

	//---------------------------------------------------------------------------- document mousemove
	var mousemove = function(event)
	{
		var $scrollbar = $move_scrollbar;
		if (!event.which) {
			$scrollbar.mouseup(event);
			return;
		}
		// moving scrollbar
		var $bar = $scrollbar.find('.bar');
		var $in  = $bar.parent();
		// moving element
		var $element = $scrollbar.parent();
		var $table   = $element.is('table') ? $element : null;
		var $children;
		var $thead;
		if ($table) {
			$children = $table.children('tbody, tfoot, thead');
			$element  = $children.filter('tbody');
			$thead    = $children.filter('thead');
			if (!$thead.length) {
				$thead = $element;
			}
		}
		// horizontal
		if ($scrollbar.is('.horizontal')) {
			// move scrollbar
			var dx           = event.pageX - initial_mouse.x;
			var left_border  = parseInt(window.getComputedStyle($in[0]).borderLeftWidth);
			var max_x        = $in.width() - $bar.width();
			var old_x        = $bar.offset().left - $in.offset().left - left_border;
			var new_x        = Math.max(0, Math.min(max_x, old_x + dx));
			initial_mouse.x += dx;
			$bar.css('left', new_x);
			// move element
			var element_max_x = $thead[0].scrollWidth - $thead.width();
			var element_x     = Math.round(element_max_x * new_x / max_x);
			($table ? $children : $element).scrollLeft(element_x);
			// translate vertical headers
			var $columns = $table.data('scrollbar').$columns;
			var previous = 0;
			var left     = element_x;
			var right    = element_x + $thead.width() - $thead[0].scrollWidth;
			for (var index in $columns) if ($columns.hasOwnProperty(index)) {
				var $column = $columns[index];
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
		}
		// vertical
		if ($scrollbar.is('.vertical')) {
			// move scrollbar
			var dy           = event.pageY - initial_mouse.y;
			var top_border   = parseInt(window.getComputedStyle($in[0]).borderTopWidth);
			var max_y        = $in.height() - $bar.height();
			var old_y        = $bar.offset().top - $in.offset().top - top_border;
			var new_y        = Math.max(0, Math.min(max_y, old_y + dy));
			initial_mouse.y += dy;
			$bar.css('top', new_y);
			// move element
			var element_max_y = $element[0].scrollHeight - $element.height();
			var element_y     = Math.round(element_max_y * new_y / max_y);
			$element.scrollTop(element_y);
		}
	};

	//------------------------------------------------------------------------------ document mouseup
	var mouseup = function(event)
	{
		if (!$move_scrollbar) {
			return;
		}
		$(document).off('mousemove', mousemove).off('mouseup', mouseup);
		$move_scrollbar = null;
	};

	//--------------------------------------------------------------- horizontal / vertical scrollbar
	var scrollBar = function(settings)
	{
		var $element = this;
		var $scrollbar = $(
			'<div class="' + (settings.arrows ? 'arrows ' : '') + settings.direction + ' scrollbar">'
			+ (settings.arrows ? '<div class="previous"/><div class="scroll">' : '')
			+ '<div class="bar"/>'
			+ (settings.arrows ? '</div><div class="next"/>' : '')
			+ '</div>'
		);
		$scrollbar.appendTo($element);
		if ($element.is('table')) {
			scrollTable($element, $scrollbar, settings);
		}
		scrollDraw($element, $scrollbar);
		$scrollbar.mousedown(mousedown);
	};

	//------------------------------------------------------------------------------------ scrollDraw
	var scrollDraw = function($element, $scrollbar)
	{
		if ($element.is('table')) {
			$element = $element.children('tbody');
		}
		var $bar = $scrollbar.find('.bar');
		var percentage;
		if ($scrollbar.is('.horizontal')) {
			percentage = Math.round(1000 * $element.width() / $element[0].scrollWidth) / 10;
			$bar.css('width', percentage.toString() + '%');
		}
		if ($scrollbar.is('.vertical')) {
			percentage = Math.round(1000 * $element.height() / $element[0].scrollHeight) / 10;
			$bar.css('height', percentage.toString() + '%');
		}
	};

	//--------------------------------------------------------------------------- scrollbar for table
	var scrollTable = function($table, $scrollbar, settings)
	{
		if ($table.data('scrollbar')) {
			return;
		}

		var $tbody      = $table.children('tbody');
		var $tfoot      = $table.children('tfoot');
		var $thead      = $table.children('thead');
		var $tbody_tr   = $tbody.children('tr:first-child');
		var $tfoot_tr   = $tfoot.children('tr:first-child');
		var $thead_tr   = $thead.children('tr:first-child');
		var $tr         = $thead_tr.length ? $thead_tr : ($tbody_tr.length ? $tbody_tr : $tfoot_tr);
		var foot_height = ($tfoot.length ? $tfoot.height() : 0);
		var head_height = ($thead.length ? $thead.height() : 0);
		var trs         = [$tbody_tr, $tfoot_tr, $thead_tr];
		var weight      = parseInt($scrollbar.css('--weight'));
		var widths = [];

		// align thead / tbody / tfoot columns

		$tr.children().each(function() {
			widths.push($(this).width());
		});
		for (var tr in trs) if (trs.hasOwnProperty(tr)) {
			var key = 0;
			trs[tr].children().each(function() {
				$(this).css('min-width', widths[key++]);
			});
		}

		// grid layout template

		var near = settings.vertical_scrollbar_near;
		near = (near === 'both') ? ['foot', 'head'] : [near];
		var near_foot = (near.indexOf('foot') > -1) ? 'vertical' : 'foot';
		var near_head = (near.indexOf('head') > -1) ? 'vertical' : 'head';

		var grid_template = [
			'head ' + near_head,
			'body vertical',
			'foot ' + near_foot,
			'horizontal angle'
		];
		var grid_template_columns = ['1fr', weight.toString() + 'px'];
		var grid_template_rows    = [
			head_height.toString() + 'px',
			'1fr',
			foot_height.toString() + 'px',
			weight.toString() + 'px'
		];
		$table.css({
			'display':               'grid',
			'grid-template':         Q + grid_template.join(Q + SP + Q) + Q,
			'grid-template-columns': grid_template_columns.join(SP),
			'grid-template-rows':    grid_template_rows.join(SP),
			'position':              'relative'
		});

		$table.children().css({ display: 'block', overflow: 'hidden' });
		$tbody.css('grid-area', 'body');
		$tfoot.css('grid-area', 'foot');
		$thead.css('grid-area', 'head');

		// count vertical fixed columns
		var $columns = {};
		if (settings.fixed_columns) {
			$table.find(settings.fixed_columns).each(function() {
				var $fixed = $(this);
				var index  = $fixed.prevAll().length + 1;
				if ($columns[index] === undefined) {
					$columns[index] = $table.find('tr > :nth-child(' + index + ')');
				}
			});
		}

		$table.data('scrollbar', { $columns: $columns });
	};

	//------------------------------------------------- both / horizontal / vertical scrollbar plugin
	$.fn.scrollBar = function(settings)
	{
		settings = $.extend({
			arrows:                  false,  // false, true
			direction:               'both', // both, horizontal, vertical
			fixed_columns:           '.fix', // jQuery selector for fixed columns
			vertical_scrollbar_near: 'both'  // both, foot, head
		}, settings);

		var directions = (settings.direction === 'both')
			? ['horizontal', 'vertical']
			: [settings.direction];
		var settings_direction = settings.direction;
		for (var direction in directions) if (directions.hasOwnProperty(direction)) {
			settings.direction = directions[direction];
			scrollBar.call(this, settings);
		}
		settings.direction = settings_direction;

		if (settings.direction === 'both') {
			$('<div class="scrollbar angle"/>').appendTo(this);
		}
	};

})( jQuery );
