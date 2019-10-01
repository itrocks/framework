
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

	//--------------------------------------------------------------- horizontal / vertical scrollbar
	var scrollBar = function(settings)
	{
		var is_table = this.is('table');
		var $element = this;

		var $scrollbar = $(
			'<div class="' + (settings.arrows ? 'arrows ' : '') + settings.direction + ' scrollbar">'
			+ (settings.arrows ? '<div class="previous"/><div class="scroll">' : '')
			+ '<div class="bar"/>'
			+ (settings.arrows ? '</div><div class="next"/>' : '')
			+ '</div>'
		);
		$scrollbar.appendTo($element);

		if (is_table) {
			scrollTable($element, $scrollbar);
		}

		scrollDraw($element, $scrollbar);
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
	var scrollTable = function($table, $scrollbar)
	{
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

		if (!$table.data('widths')) {
			$table.data('widths', widths);

			$tr.children().each(function() {
				widths.push($(this).width());
			});
			for (var tr in trs) if (trs.hasOwnProperty(tr)) {
				var key = 0;
				trs[tr].children().each(function() {
					$(this).css('min-width', widths[key++]);
				});
			}

			var grid_template = [
				'head       head',
				'body       vertical',
				'foot       vertical',
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
			$tbody.css({ 'grid-area': 'body' });
			$tfoot.css({ 'grid-area': 'foot' });
			$thead.css({ 'grid-area': 'head' });
		}
	};

	//------------------------------------------------- both / horizontal / vertical scrollbar plugin
	$.fn.scrollBar = function(settings)
	{
		settings = $.extend({
			arrows:    false,  // false, true
			direction: 'both', // both, horizontal, vertical
			table: {
				fix: '.fix' // selector for fixed columns
			}
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
