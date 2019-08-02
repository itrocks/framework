$(document).ready(function()
{

	var phone_max_width = 469;

	//-------------------------------------------------------------------------- hideListPlaceHolders
	var hideListPlaceHolders = function()
	{
		$('ul.list > li.search > ol > li').each(function() {
			var $input = $(this).find('input');
			if (!$input.length) return;
			$input.removeAttr('placeholder');
		});
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
		$('ul.list').each(function() {
			var $list    = $(this);
			var $titles  = $list.find('> li.title > ol > li');
			console.log($titles);
			var position = -1;
			$list.find('> li.search > ol > li').each(function() {
				position ++;
				var $input = $(this).find('input');
				if (!$input.length) return;
				var $title = $($titles.get(position));
				console.log(position, ':', $(this), '|', $title);
				$input.attr('placeholder', $title.text().trim())
			});
		});
	};

	//----------------------------------------------------------------------- article responsive list
	/**
	 * Every time a list is loaded, apply placeholder if needed
	 */
	$('body').build('each', 'ul.list', responsiveList);

	//--------------------------------------------------------------------------------- window.resize
	/**
	 * Every time the window is resized, apply or remove placeholder as needed
	 */
	$(window).resize(responsiveList);

});
