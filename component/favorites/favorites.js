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

	//-------------------------------------------------------------------------------- main > article
	/**
	 * Apply the colors from the newly added article, seeked into the nav#menu, to the current tab
	 */
	$body.build('each', 'main > article', function()
	{
		var $article = $(this);
		var $current = $('#favorites > .current');
		var $anchor  = $current.children('a');
		var title    = $article.find('h2').text();

		var $selected_module = $('#menu h3.selected').closest('li');
		if ($selected_module.length) {
			$current.css('background', window.getComputedStyle($selected_module[0]).getPropertyValue('--dark-color'));
		}

		if (title) {
			$anchor.text(title);
			var feature = $article.data('feature');
			var id      = $article.data('id');
			var path    = $article.data('class').replace('\\', '/');
			$anchor[0].href = app.uri_base + SL + path + (id ? (SL + id) : '') + (feature ? (SL + feature) : '');
			showHide.call($anchor.parent());
		}
	});

});
