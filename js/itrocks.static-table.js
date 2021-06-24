$(document).ready(function () {
	var selector         = 'article table.static-table';
	var last_table_width = 0;

	var getRowWidth = function ($table) {
		return Math.max(
			$table.children('thead').find('tr').width(), $table.children('tbody').find('tr').width()
		);
	};

	var updateAllTables = function ($tables) {
		$tables.each(function () {
			var $table = $(this);
			updateLastCellSize(
				$table, getRowWidth($table)
			);
		});
	};

	var updateLastCellSize = function ($table, row_width) {
		var table_width             = $table.width();
		var $table_head             = $table.children('thead');
		var $table_body             = $table.children('tbody');
		var $head_last_child        = $table_head.find('tr > th:last-child');
		var $body_last_child        = $table_body.find('tr > td:last-child');
		var current_last_cell_width = $head_last_child.width();
		var last_cell_width         = 0;

		// get the new width of the last cell.
		last_cell_width = table_width < last_table_width
			? current_last_cell_width - (last_table_width - table_width)
			: table_width - (row_width - current_last_cell_width);


		last_table_width = table_width;
		// set the new last cell width
		$head_last_child.each(function () {$(this).width(last_cell_width);});
		$body_last_child.each(function () {$(this).width(last_cell_width);});
	};

	$(window).resize(function () {updateAllTables($(selector));});
	$('body').build('each', selector, function () {
		updateAllTables($(selector));
	});

});
