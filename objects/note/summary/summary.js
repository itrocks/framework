$(document).ready(function()
{
	const $body = $('body')

	//--------------------------------------------------------------------------------------- refresh
	const refresh = function()
	{
		const $notes_summary = $('#notes-summary')
		if (!$notes_summary.length) {
			return
		}
		const $article = $notes_summary.closest('article[data-class][data-id]')
		const object   = $article.data('class').repl(BS, SL) + SL + $article.data('id')

		const uri = app.uri_base + '/ITRocks/Framework/Objects/Note/summary/' + object + '?as_widget'
		$.get(uri, function(html) { $notes_summary.html(html).build(); })
	}

	//------------------------------------------------------------------ #notes-summary > .notes call
	$body.build('call', '#notes-summary > .notes', function()
	{
		this.closest('#notes-summary').removeClass('popup')

		const count = this.children('li[data-id]').length
		this.closest('[data-count]').attr('data-count', count ? count : '')

		this.children('li[data-id]').on('dblclick', function()
		{
			const $li = $(this)
			const id  = $li.data('id')
			const uri = app.uri_base + '/ITRocks/Framework/Objects/Note/' + id + '/summaryEdit?as_widget'
			$.get(uri, function(html) { $li.html(html).build(); })
		})
	})

	//------------------------------------------------------------------------ .deleted / .saved call
	$body.build('call', '.deleted[data-class="ITRocks\\\\Framework\\\\Objects\\\\Note"]', refresh)
	$body.build('call', '.saved[data-class="ITRocks\\\\Framework\\\\Objects\\\\Note"]',   refresh)
})
