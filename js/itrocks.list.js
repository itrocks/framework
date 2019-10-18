$(document).ready(function()
{
	var $body = $('body');

	//----------------------------------------------------------------------- article.list form table
	$body.build('each', 'article.list > form > table', function()
	{
		var $table = $(this);

		// highlight column

		var $children = $table.children('tbody, tfoot, thead');

		$children.mousemove(function(event)
		{
			var hover_column    = 0;
			var selected_column = 0;
			var column          = 0;
			var $columns        = $table.find('> thead > tr.search > *').slice(1, -1);
			$columns.each(function() {
				column ++;
				var $td   = $(this);
				if (!hover_column && $td.hasClass('hover')) {
					hover_column = column;
				}
				if (!selected_column) {
					var left  = $td.offset().left;
					var right = left + $td.width() - 1;
					if ((event.pageX >= left) && (event.pageX <= right)) {
						selected_column = column;
					}
				}
				if (hover_column && selected_column) {
					return false;
				}
			});
			if (hover_column !== selected_column) {
				if (hover_column) {
					$table.find('.hover').removeClass('hover');
				}
				if (selected_column) {
					selected_column ++;
					$table.find('tr > :nth-child(' + selected_column + ')').addClass('hover');
				}
			}
		});

		$children.mouseout(function()
		{
			$(this).parent().find('.hover').removeClass('hover');
		});

		//------------------------------------------------------------------------------------ loadMore
		/**
		 * If the "load more" section is visible, load more lines
		 */
		var loadMore = function()
		{
			var $table = $(this);
			if ($table.data('load-more')) {
				return;
			}
			var $more = $table.find('tr.more');
			if (!$more.length) {
				return;
			}
			var $tbody = $table.children('tbody');
			if ($more.offset().top >= ($tbody.offset().top + $tbody.height())) {
				return;
			}

			$table.data('load-more', true);
			var move = $tbody.children('tr').length;
			var time = $table.data('load-time');
			var uri  = $table.closest('form').attr('action');
			// show loading spinner
			setTimeout(function() {
				if ($table.data('load-more')) {
					$more.find('.loading').css('display', 'inline-block');
				}
			}, 500);
			// ask server for more lines
			more_request_headers['target-height'] = $tbody.height();
			more_request_headers['target-width']  = $tbody.width();
			$.ajax({
				beforeSend: requestHeaders,
				url:     app.askAnd(uri, 'last_time=' + time + '&move=' + move + '&as_widget'),
				success: function(data)
				{
					var $load_table   = $(data).find('table');
					var $trs          = $load_table.find('> tbody > tr:not(.more)');
					var $existing_trs = $tbody.children(':not(.more)');
					var reset         = false;
					var scroll_top    = $tbody.scrollTop();
					// append trailing cells
					$trs.append('<td class="trailing" style="width: 100%">');
					// if table was updated : remove all rows, as their updated version was loaded
					if ($load_table.data('updated')) {
						$table.attr('data-load-time', $load_table.data('load-time'));
						$existing_trs.remove();
						reset = true;
					}
					// display new rows
					$().autofocus(false);
					$trs.insertBefore($more).build();
					$().autofocus(true);
					// remove "load more" section if the total count of lines is reached
					var rows_count       = $tbody.children().length;
					var total_rows_count = parseInt(
						$table.closest('article').find('.summary > .lines > strong:last').text()
					);
					if (rows_count >= total_rows_count) {
						$tbody.children('tr.more').remove();
					}
					// adjust head cells width if more larger lines were loaded
					var $head_tr = $table.find('> thead > :first > :not(.trailing)');
					$tbody.find('> :first > :not(.trailing)').each(function(column_number) {
						var $cell      = $(this);
						var $head_cell = $($head_tr[column_number]);
						var cell_width = Math.max(
							$cell.width(),
							parseInt($cell.css('width')),
							$head_cell.width(),
							parseInt($head_cell.css('min-width'))
						).toString() + 'px';
						$head_cell.css('min-width', cell_width);
						if (reset) {
							$cell.css('min-width', cell_width);
						}
					});
					// fix a bug where scroll position moves each time I add lines
					$tbody.scrollTop(scroll_top);
					// recalculate and redraw vertical scrollbar
					$table.scrollBar('draw');
					// unlock "more lines loading"
					$more.find('.loading').css('display', 'none');
					$table.removeData('load-more');
					// in case of mousewheel running too fast : do it again soon
					setTimeout(function(){ loadMore.call($table); });
				}
			});
		};

		//-------------------------------------------------------------------------------------- onDraw
		var onDraw = function()
		{
			loadMore.call(this);
			trailingCells.call(this);
		};

		//------------------------------------------------------------------------------- trailingCells
		/**
		 * scrollbar trailing column cells for full-width row
		 */
		var trailingCells = function()
		{
			var $table      = $(this);
			var $body       = $table.find('tbody');
			var $trailing   = $body.find('.trailing');
			var was_visible = $trailing.is(':visible');
			var is_visible  = !$table.find('.horizontal.scrollbar').is(':visible');
			if (is_visible) {
				if (!was_visible) {
					$trailing.show();
				}
			}
			else if (was_visible) {
				$trailing.hide();
			}
		};

		$table.scrollBar({ draw: onDraw, vertical_scrollbar_near: 'foot' });

		var $trailing = $table.find('> thead > tr > :last-child');
		$trailing.css({ 'min-width': $trailing.width().toString() + 'px', 'width': '100%' });
		$table.find('> tbody > tr:not(.more) > :last-child').after($('<td class="trailing" style="width: 100%">'));

	});

	//--------------------------------------------------------------------------------- window.resize
	/**
	 * Every time the window is resized, apply or remove placeholder as needed
	 */
	$(window).resize(responsiveList);

});
