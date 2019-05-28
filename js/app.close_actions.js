$(document).ready(function()
{
	var $body = $('body');

	//------------------------------------------------------------------------------- div.popup close
	/**
	 * Set popup windows close action to javascript remove popup instead of calling a link
	 */
	$body.build('click', 'div.popup .general.actions .close > a', function()
	{
		var $this = $(this);
		$this.removeAttr('href').removeAttr('target');
		setTimeout(function() { $this.closest('.popup').remove(); });
	});

	$body.build('each', 'div.popup .general.actions a[href]:not([href*="close="])', function() {
		var $this = $(this);
		var href  = $this.attr('href');
		if (!href.beginsWith('#')) {
			var close_link = app.askAnd(href, 'close=window' + window.id_index);
			$this.attr('href', close_link);
		}
	});

	//------------------------------------------------------------------------ section#messages close
	/**
	 * #messages close action empties #messages instead of calling a link
	 */
	$body.build('click', 'div#messages .actions .close a', function(event)
	{
		$(this).closest('div#messages').empty();
		event.preventDefault();
		event.stopImmediatePropagation();
	});

});
