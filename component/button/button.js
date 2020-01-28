$(document).ready(function()
{

	//-------------------------------------------------------------------------- .actions > li :hover
	$('body').build('each', '.actions > li > ul, li.action > ul', function()
	{
		var $ul           = $(this);
		var article_width = $ul.closest('article').width();
		var left          = $ul.offset().left + $ul.parent().offset().left;
		var width         = $ul.width();
		if ((left + width) > article_width) {
			$ul.css('margin-left', '-' + (left + width - article_width) + 'px');
		}
	});

});
