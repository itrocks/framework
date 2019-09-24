$(document).ready(function()
{

	$('body').build('each', 'main > article', function()
	{
		var $article = $(this);
		var title = $article.find('h2').text();
		if (title) {
			var $anchor = $('body > .main > .favorites .current > a');
			$anchor.text(title);
			var feature = $article.data('feature');
			var id      = $article.data('id');
			var path    = $article.data('class').replace('\\', '/');
			$anchor[0].href = app.uri_base + SL + path + (id ? (SL + id) : '') + (feature ? (SL + feature) : '');
		}
	});

});
