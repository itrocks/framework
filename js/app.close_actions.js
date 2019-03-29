$(document).ready(function()
{

	//------------------------------------------------------------------------------- div.popup close
	/**
	 * Set popup windows close action to javascript remove popup instead of calling a link
	 */
	$('div.popup .general.actions').build(function()
	{
		this.find('.close > a').click(function()
		{
			var $this = $(this);
			$this.removeAttr('href').removeAttr('target');
			setTimeout(function() { $this.closest('.popup').remove(); });
		});

		this.find('a[href]:not([href*="close="])').each(function()
		{
			var $this = $(this);
			var href  = $this.attr('href');
			if (!href.beginsWith('#')) {
				var close_link = app.askAnd(href, 'close=window' + window.zindex_counter);
				$this.attr('href', close_link);
			}
		});
	});

	//------------------------------------------------------------------------ section#messages close
	/**
	 * #messages close action empties #messages instead of calling a link
	 */
	$('div#messages .actions .close a').build('click', function(event)
	{
		$(this).closest('div#messages').empty();
		event.preventDefault();
		event.stopImmediatePropagation();
	});

});
