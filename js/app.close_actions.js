$(document).ready(function()
{
	const $body = $('body')

	//------------------------------------------------------------------------------- div.popup close
	/**
	 * Set popup windows close action to javascript remove popup instead of calling a link
	 */
	$body.build('click', 'div.popup .general.actions .close > a', function(event)
	{
		const $this = $(this)
		$this.removeAttr('href').removeAttr('target')
		setTimeout(() => $this.closest('.popup').remove())
		event.preventDefault()
		event.stopImmediatePropagation()
	})

	//------------------------------------------------------------------ div.popup actions:not(close)
	$body.build('each', 'div.popup .general.actions a[href]:not([href*="close="])', function()
	{
		const $this = $(this)
		const href  = $this.attr('href')
		if (!href.startsWith('#')) {
			const close_link = app.askAnd(href, 'close=window' + window.id_index)
			$this.attr('href', close_link)
		}
	})

	//------------------------------------------------------------------------ section#responses close
	/**
	 * #responses close action empties #responses instead of calling a link
	 */
	$body.build('click', 'div#responses .actions .close a', function(event)
	{
		$(this).closest('div#responses').empty()
		event.preventDefault()
		event.stopImmediatePropagation()
	})

})
