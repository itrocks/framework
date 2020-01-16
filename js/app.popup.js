$(document).ready(function()
{

	var $body    = $('body');
	var selector = '.popup > article > form > ul.data, .popup > article > ul.data';

	//---------------------------------------------------------------------------------------- resize
	var resize = function()
	{
		var $element   = $(this);
		var $article   = $element.closest('article');
		var $window    = $(window);
		var max_height = $window.height() - ($article.height() - $element.height());
		var overflow_x = ($window.width() < $article.width()) ? 'auto' : 'hidden';
		$element.css('max-height', max_height.toString() + 'px');
		$element.css('overflow-x', overflow_x);
	};

	//-------------------------------------------------------------------------------- .popup ul.data
	$body.build('each', selector, resize);

	//--------------------------------------------------------------------------------- window resize
	$(window).resize(function()
	{
		$(selector).each(resize);
	});

});
