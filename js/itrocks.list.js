$(document).ready(function()
{
	const $body = $('body')

	//----------------------------------------------------------------------- article.list form table
	$body.build('each', 'article.list > form > table', function()
	{
		const $table = $(this)

		// highlight column

		const $children = $table.children('tbody, tfoot, thead')

		$children.mousemove(function(event)
		{
			let   hover_column    = 0
			let   selected_column = 0
			let   column          = 0
			const $columns        = $table.find('> thead > tr.search > *').slice(1, -1)
			$columns.each(function() {
				column ++
				const $td   = $(this)
				if (!hover_column && $td.hasClass('hover')) {
					hover_column = column
				}
				if (!selected_column) {
					const left  = $td.offset().left
					const right = left + $td.width() - 1
					if ((event.pageX >= left) && (event.pageX <= right)) {
						selected_column = column
					}
				}
				if (hover_column && selected_column) {
					return false
				}
			})
			if (hover_column !== selected_column) {
				if (hover_column) {
					$table.find('.hover').removeClass('hover')
				}
				if (selected_column) {
					selected_column ++
					$table.find('tr > :nth-child(' + selected_column + ')').addClass('hover')
				}
			}
		})

		$children.mouseout(function()
		{
			$(this).parent().find('.hover').removeClass('hover')
		})

		//----------------------------------------------------------- table input[type=checkbox] arrows
		$body.build('keydown', 'article.list > form > table.list', function(event)
		{
			const $target = $(event.target)
			if (!$target.is('input[type=checkbox]')) {
				return
			}
			const $td = $target.closest('th')
			const $tr = $target.closest('tr')

			switch (event.keyCode) {
				case 37: // left
				case 38: // top
					$tr.prev().length
						? $tr.prev().children('th:first-child').find('input[type=checkbox]').focus()
						: $tr.parent().prev().find('input[type=checkbox]:first').focus()
					break
				case 39: // right
					$td.nextAll().find('a:first').focus()
					break
				case 40: // down
					$tr.parent().is('tbody')
						? $tr.next().children('th:first-child').find('input[type=checkbox]').focus()
						: $tr.parent().next().children('tr:first-child').children('th:first-child')
							.find('input[type=checkbox]').focus()
					break
			}
		})

		//------------------------------------------------------------------------------------ loadMore
		/**
		 * If the "load more" section is visible, load more lines
		 */
		const loadMore = function()
		{
			const $table = $(this)
			if ($table.data('load-more')) {
				return
			}
			const $more = $table.find('tr.more')
			if (!$more.length) {
				return
			}
			const $tbody = $table.children('tbody')
			if ($more.offset().top >= ($tbody.offset().top + $tbody.height())) {
				return
			}

			$table.data('load-more', true)
			const move = $tbody.children('tr').length
			const time = $table.data('load-time')
			const uri  = $table.closest('form').attr('action')
			// show loading spinner
			setTimeout(function() {
				if ($table.data('load-more')) {
					$more.find('.loading').css('display', 'inline-block')
				}
			}, 500)
			// ask server for more lines
			more_request_headers['target-height'] = $tbody.height()
			more_request_headers['target-width']  = $tbody.width()
			$.ajax({
				beforeSend: requestHeaders,
				url:     app.askAnd(uri, 'last_time=' + time + '&move=' + move + '&as_widget'),
				success: function(data)
				{
					const $load_table   = $(data).find('table')
					const $trs          = $load_table.find('> tbody > tr:not(.more)')
					const $existing_trs = $tbody.children(':not(.more)')
					let   reset         = false
					// append trailing cells
					$trs.append('<td class="trailing" style="width: 100%">')
					// if table was updated : remove all rows, as their updated version was loaded
					if ($load_table.data('updated')) {
						$table.attr('data-load-time', $load_table.data('load-time'))
						$existing_trs.remove()
						reset = true
					}
					// display new rows
					$().autofocus(false)
					$trs.insertBefore($more).build()
					$().autofocus(true)
					// remove "load more" section if the total count of lines is reached
					const rows_count       = $tbody.children().length
					const total_rows_count = parseInt(
						$table.closest('article').find('.summary > .lines > strong:last').text()
					)
					if (rows_count >= total_rows_count) {
						$tbody.children('tr.more').remove()
					}
					// adjust head cells width if more larger lines were loaded
					const $head_tr = $table.find('> thead > :first > :not(.trailing)')
					$tbody.find('> :first > :not(.trailing)').each(function(column_number) {
						const $cell      = $(this)
						const $head_cell = $($head_tr[column_number])
						const cell_width = Math.max(
							$cell.width(),
							parseInt($cell.css('width')),
							$head_cell.width(),
							parseInt($head_cell.css('min-width'))
						).toString() + 'px'
						$head_cell.css('min-width', cell_width)
						if (reset) {
							$cell.css('min-width', cell_width)
						}
					})
					// recalculate and redraw vertical scrollbar
					$table.scrollBar('refreshFixedColumns')
					$table.scrollBar('draw')
					// unlock "more lines loading"
					$more.find('.loading').css('display', 'none')
					$table.removeData('load-more')
					// in case of mousewheel running too fast : do it again soon
					setTimeout(function() { loadMore.call($table) })
				}
			})
		}

		//-------------------------------------------------------------------------------------- onDraw
		const onDraw = function()
		{
			loadMore.call(this)
			trailingCells.call(this)
		}

		//------------------------------------------------------------------------------- trailingCells
		/**
		 * scrollbar trailing column cells for full-width row
		 */
		const trailingCells = function()
		{
			const $table      = $(this)
			const $body       = $table.find('tbody')
			const $trailing   = $body.find('.trailing')
			const was_visible = $trailing.is(':visible')
			const is_visible  = !$table.find('.horizontal.scrollbar').is(':visible')
			if (is_visible) {
				if (!was_visible) {
					$trailing.show()
				}
			}
			else if (was_visible) {
				$trailing.hide()
			}
		}

		$table.scrollBar({ draw: onDraw, vertical_scrollbar_near: 'foot' })

		const $trailing = $table.find('> thead > tr > :last-child')
		$trailing.css({ 'min-width': $trailing.width().toString() + 'px', 'width': '100%' })
		$table.find('> tbody > tr:not(.more) > :last-child')
			.after($('<td class="trailing" style="width: 100%">'))

	})

})
