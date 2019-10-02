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
	//$body.build('call', 'article.list > form > table', $.fn.fixedHeaders);
	$body.build(
		'call', 'article.list > form > table', $.fn.scrollBar, { vertical_scrollbar_near: 'foot' }
	);

	//--------------------------------------------------------------------------------- window.resize
	/**
	 * Every time the window is resized, apply or remove placeholder as needed
	 */
	$(window).resize(responsiveList);

});
