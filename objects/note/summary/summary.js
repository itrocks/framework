$(document).ready(function()
{
	var $body = $('body');

	//--------------------------------------------------------------------------------------- refresh
	var refresh = function()
	{
		var $notes_summary = $('#notes-summary');
		if (!$notes_summary.length) {
			return;
		}
		var $article = $notes_summary.closest('article[data-class][data-id]');
		var object   = $article.data('class').repl(BS, SL) + SL + $article.data('id');

		var uri = app.uri_base + '/ITRocks/Framework/Objects/Note/summary/' + object + '?as_widget';
		$.get(uri, function(html) { $notes_summary.html(html).build(); });
	};

	//------------------------------------------------------------------ #notes-summary > .notes call
	$body.build('call', '#notes-summary > .notes', function()
	{
		this.closest('#notes-summary').removeClass('popup');

		var count = this.children('li[data-id]').length;
		this.closest('[data-count]').attr('data-count', count ? count : '');

		this.children('li[data-id]').on('dblclick', function()
		{
			var $li = $(this);
			var id  = $li.data('id');
			var uri = app.uri_base + '/ITRocks/Framework/Objects/Note/' + id + '/summaryEdit?as_widget';
			$.get(uri, function(html) { $li.html(html).build(); });
		});
	});

	//------------------------------------------------------------------------ .deleted / .saved call
	$body.build('call', '.deleted[data-class="ITRocks\\\\Framework\\\\Objects\\\\Note"]', refresh);
	$body.build('call', '.saved[data-class="ITRocks\\\\Framework\\\\Objects\\\\Note"]',   refresh);
});
