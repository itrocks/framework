$(document).ready(function()
{

	var $body = $('body');

	//------------------------------------------------------------ .favorites > li:not(.add) showHide
	/**
	 * When there is no text into a tab, hide it
	 */
	var showHide = function()
	{
		var $this = $(this);
		$this.css('display', $this.text().trim() ? '' : 'none');
	};
	$body.build('each', '#favorites > :not(.add)', showHide);

	//-------------------------------------------------------------- #favorites > .current setCurrent
	var setCurrent = function($article, module)
	{
		var $current = $(this);
		$current.attr('data-class',   $article.data('class'));
		$current.attr('data-feature', $article.data('feature'));
		$current.attr('data-module',  module);

		var title = $article.find('h2').text();
		if (title) {
			var $anchor = $current.children('a');
			var feature = $article.data('feature');
			var id      = $article.data('id');
			var path    = $article.data('class').repl('\\', '/');
			$anchor.text(title).attr(
				'href',
				app.uri_base + SL + path + (id ? (SL + id) : '') + (feature ? (SL + feature) : '')
			);
			showHide.call($anchor.parent());
		}
	};

	//-------------------------------------------------------------------------------- main > article
	$body.build('each', '#favorites > .current', function()
	{
		$(this).data('setCurrent', setCurrent);
	});

});
