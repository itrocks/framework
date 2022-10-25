$(document).ready(function()
{

	const $body = $('body')

	//------------------------------------------------------------ .favorites > li:not(.add) showHide
	/**
	 * When there is no text into a tab, hide it
	 */
	const showHide = function()
	{
		const $this = $(this)
		$this.css('display', $this.text().trim() ? '' : 'none')
	}
	$body.build('each', '#favorites > :not(.add)', showHide)

	//-------------------------------------------------------------- #favorites > .current setCurrent
	const setCurrent = function($article, module)
	{
		const $current = $(this)
		$current.attr('data-class',   $article.data('class'))
		$current.attr('data-feature', $article.data('feature'))
		$current.attr('data-module',  module)

		const title = $article.find('h2').text()
		if (title) {
			const $anchor = $current.children('a')
			const feature = $article.data('feature')
			const id      = $article.data('id')
			const path    = $article.data('class').repl('\\', '/')
			$anchor.text(title).attr(
				'href',
				app.uri_base + SL + path + (id ? (SL + id) : '') + (feature ? (SL + feature) : '')
			)
			showHide.call($anchor.parent())
		}
	}

	//-------------------------------------------------------------------------------- main > article
	$body.build('each', '#favorites > .current', function()
	{
		$(this).data('setCurrent', setCurrent)
	})

})
