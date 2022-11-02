$(document).ready(function () {
	const selector         = 'article table.static-table'
	let   last_table_width = 0

	const getRowWidth = function ($table) {
		return Math.max(
			$table.children('thead').find('tr').width(), $table.children('tbody').find('tr').width()
		)
	}

	const updateAllTables = function ($tables) {
		$tables.each(function () {
			const $table = $(this)
			updateLastCellSize(
				$table, getRowWidth($table)
			)
		})
	}

	const updateLastCellSize = function ($table, row_width) {
		const table_width             = $table.width()
		const $table_head             = $table.children('thead')
		const $table_body             = $table.children('tbody')
		const $head_last_child        = $table_head.find('tr > th:last-child')
		const $body_last_child        = $table_body.find('tr > td:last-child')
		const current_last_cell_width = $head_last_child.width()
		let   last_cell_width         = 0

		// get the new width of the last cell.
		last_cell_width = table_width < last_table_width
			? current_last_cell_width - (last_table_width - table_width)
			: table_width - (row_width - current_last_cell_width)


		last_table_width = table_width
		// set the new last cell width
		$head_last_child.each(function() { $(this).width(last_cell_width) })
		$body_last_child.each(function() { $(this).width(last_cell_width) })
	}

	$(window).resize(() => updateAllTables($(selector)))
	$('body').build('each', selector, () => updateAllTables($(selector)))

})
