$(document).ready(function()
{

	//-------------------------------------------------------------------------- .actions > li :hover
	$('body').build('each', '.actions > li > ul, li.action > ul', function()
	{
		const $ul           = $(this)
		const article_width = $ul.closest('article').width()
		const left          = $ul.offset().left + $ul.parent().offset().left
		const width         = $ul.width()
		if ((left + width) > article_width) {
			$ul.css('margin-left', '-' + (left + width - article_width) + 'px')
		}
	})

})
