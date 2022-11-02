$(document).ready(function()
{

	const $body    = $('body')
	const selector = '.popup > article > form > ul.data, .popup > article > ul.data'

	//---------------------------------------------------------------------------------------- resize
	const resize = function()
	{
		const $element   = $(this)
		const $article   = $element.closest('article')
		const $window    = $(window)
		const max_height = $window.height() - ($article.height() - $element.height())
		const overflow_x = ($window.width() < $article.width()) ? 'auto' : 'hidden'
		$element.css('max-height', max_height.toString() + 'px')
		$element.css('overflow-x', overflow_x)
	}

	//-------------------------------------------------------------------------------- .popup ul.data
	$body.build('each', selector, resize)

	//--------------------------------------------------------------------------------- window resize
	$(window).resize(function()
	{
		$(selector).each(resize)
	})

})
