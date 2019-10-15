$(document).ready(function()
{

	var $body = $('body');
	var phone_max_width = 469;

	//-------------------------------------------------------------------------- hideListPlaceHolders
	var hideListPlaceHolders = function()
	{
		$('article.list tr.search input').removeAttr('placeholder');
	};

	//-------------------------------------------------------------------------------- responsiveList
	var responsiveList = function()
	{
		var width = document.body.clientWidth;

		if (width > phone_max_width) {
			hideListPlaceHolders();
		}
		else {
			showListPlaceHolders();
		}
	};

	//-------------------------------------------------------------------------- showListPlaceHolders
	var showListPlaceHolders = function()
	{
		$('article.list > form > table > thead').each(function() {
			var $list    = $(this);
			var $titles  = $list.find('> tr.title > th');
			var position = -1;
			$list.find('> tr.search > td').each(function() {
				position ++;
				var $input = $(this).find('input');
				if (!$input.length) return;
				var $title = $($titles.get(position));
				$input.attr('placeholder', $title.text().trim())
			});
		});
	};

	//----------------------------------------------------------------------- article responsive list
	/**
	 * Every time a list is loaded, apply placeholder if needed
	 */
	$body.build('each', 'article.list', responsiveList);

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

		// scrollbar trailing column for full-width row

		var onDraw = function()
		{
			var $element    = $(this);
			var $body       = $element.find('tbody');
			var $trailing   = $body.find('.trailing');
			var was_visible = $trailing.is(':visible');
			var is_visible  = !$element.find('.horizontal.scrollbar').is(':visible');
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
