(function($)
{

	var counter   = 0;
	var on_resize = 0;
	var tables    = [];

	//--------------------------------------------------------------------------------- onRemoveTable
	var onRemoveTable = function()
	{
		var removed_counter = $(this).data('fixed-headers-counter');
		var old_tables      = tables;
		tables              = [];
		for (var counter in old_tables) {
			if (
				old_tables.hasOwnProperty(counter)
				&& (counter !== removed_counter)
			) {
				tables[counter] = old_tables[counter];
			}
		}
	};

	$(document).keydown(function(event)
	{
		if ((event.keyCode < 33) || (event.keyCode > 40)) {
			return;
		}
		for (var table in tables) if (tables.hasOwnProperty(table)) {
			tables[table].$table.mousedown().mouseup();
		}
	});

	//--------------------------------------------------------------------------------- window.resize
	$(window).resize(function()
	{
		for(var table in tables) if (tables.hasOwnProperty(table)) (function() {
			table = tables[table];

			var $table        = table.$table;
			var $parent       = $table.parent();
			var parent_height = $parent.height();
			var parent_width  = $parent.width();

			$parent.children().not($table).each(function() { parent_height -= $(this).height(); });

			var new_height = table.css_height.replace(/\d+%/g, function (percent) {
				return Math.round(parent_height * parseFloat(percent) / 100) + 'px';
			});
			var new_width = table.css_width.replace(/\d+%/g, function (percent) {
				return Math.round(parent_width * parseFloat(percent) / 100) + 'px';
			});

			$table.css({height: new_height, width: new_width});

			// TODO this timing is not really reliable, but more reliable than without it
			on_resize ++;
			setTimeout(function () { $table.mousedown().mouseup(); on_resize--; }, 25);
		})();
	});

	//---------------------------------------------------------------------------------- fixedHeaders
	/**
	 * Applied to a table, this fixes thead, tfoot vertically, and left  / right header columns, so
	 * they don't scroll.
	 */
	$.fn.fixedHeaders = function()
	{

		var $table = this;

		$table.css({ display: 'block', overflow: 'auto' });

		var $tbody      = $table.children('tbody');
		var $tfoot      = $table.children('tfoot');
		var $thead      = $table.children('thead');
		var calculate   = false;
		var interval    = null;
		var click       = 0;
		var left_count  = 0;
		var right_count = undefined;
		var scroll      = { left: -1, top: -1 };
		var tbody       = $tbody.get(0);

		var origin = tbody.getBoundingClientRect();
		var height;
		var width;

		//------------------------------------------------------------------- window.resize information
		// if % sizes : convert to px
		$table.parent().hide();
		var css_height = $table.css('height');
		var css_width  = $table.css('width');
		$table.parent().show();
		if ((css_height + css_width).indexOf('%') > -1) {
			tables[counter] = {
				css_height: css_height,
				css_width:  css_width,
				$table:     $table
			};
			$table.data('fixed-headers-counter', counter);
			$table.on('remove', onRemoveTable);
		}

		// count left and right fixed header columns (automatic mode)
		$table.children('tbody').children('tr:first').children().each(function() {
			var $cell = $(this);
			if ($cell.is('td')) {
				right_count = 0;
			}
			else if (right_count === undefined) {
				left_count ++;
			}
			else {
				right_count ++;
			}
		});
		if (right_count === undefined) {
			right_count = 0;
		}

		var cell_selector = ':nth-last-child(' + (right_count + 1) + ')';
		$table.find('tr').each(function() {
			var $tr       = $(this);
			var $trailing = $tr.find('.trailing');
			if ($trailing.length) {
				$trailing.css('min-width', $trailing.width().toString() + 'px');
				$trailing.css('width', '100%');
				return;
			}
			$tr.children(cell_selector).after($('<td class="trailing" style="width: 100%">'));
		});

		right_count = Math.max(right_count, $table.find('> thead > tr:first > .fixed').length);

		// get left and right fixed cells
		var $left_th;
		if (left_count) {
			var left_selector = (left_count === 1)
				? ':first-child'
				: (':nth-child(-n + ' + left_count + ')');
			$left_th = $table.find('tr > ' + left_selector);
		}
		var $right_th;
		if (right_count) {
			var right_selector = (right_count === 1)
				? ':last-child'
				: (':nth-last-child(-n + ' + right_count + ')');
			$right_th = $table.find('tr > ' + right_selector);
		}
		if ((left_count + right_count) || $tfoot.length) {
			$thead.css('z-index', 1);
		}

		//---------------------------------------------------------------------------- intervalFunction
		var intervalFunction = function()
		{
			var rect = tbody.getBoundingClientRect();
			var move = {
				left: origin.left - rect.left,
				top:  origin.top  - rect.top
			};
			var move_horiz = move.left - scroll.left;
			var move_vert  = move.top  - scroll.top;
			if (on_resize) {
				calculate = true;
			}
			if (calculate) {
				height = $thead.height() + $tbody.height() + $tfoot.height() - $table.get(0).clientHeight;
				width  = $thead.width() - $table.get(0).clientWidth;
			}
			if (move_horiz || calculate) {
				scroll.left = move.left;
				if (left_count) {
					$left_th.css('transform', 'translateX(' + scroll.left + 'px');
				}
				if (right_count) {
					$right_th.css('transform', 'translateX(' + (scroll.left - width) + 'px');
				}
			}
			if (move_vert || calculate) {
				scroll.top = move.top;
				$thead.css('transform', 'translateY(' + scroll.top + 'px)');
				$tfoot.css('transform', 'translateY(' + (scroll.top - height) + 'px');
			}
			if (calculate) {
				calculate = false;
			}
		};

		//---------------------------------------------------------------------------- $table.mousedown
		$table.mousedown(function()
		{
			click ++;
			calculate = true;
			intervalFunction();
			if (!interval) {
				interval = setInterval(intervalFunction, 1);
			}
		});

		//------------------------------------------------------------------------------ $table.mouseup
		$table.mouseup(function()
		{
			var old_click = click;
			setTimeout(function() {
				if (click !== old_click) {
					return;
				}
				if (interval) {
					clearInterval(interval);
					interval = null;
				}
			}, 1000);
		});

		//--------------------------------------------------------------------------- $table.mousewheel
		$table.mousewheel(function()
		{
			$(this).mousedown().mouseup();
		});

		$table.mousedown().mouseup();
		$(window).resize();

	};

})( jQuery );
