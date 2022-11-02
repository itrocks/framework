$(document).ready(function()
{
	const $body = $('body')

	//------------------------------------------------------------------------------- a.auto-redirect
	$body.build('each', 'a.auto-redirect', function()
	{
		const $a    = $(this)
		let   delay = $a.data('delay')

		if (delay === undefined) {
			delay = 0
		}

		let   condition = true
		const data_if   = $a.data('if')
		if (data_if) {
			condition = $(data_if).length
		}
		if (condition) {
			setTimeout(() => { $.data($a[0], 'events') ? $a.click() : $a[0].click() }, delay)
		}
	})

	//-------------------------------------------------------------------------------- a.refresh-link
	$body.build('each', 'a.refresh-link[target]', function()
	{
		this.href = refreshLink($(this).attr('target'))
	})

	//--------------------------------------------------------------------------- button.auto-refresh
	$body.build('each', 'button.auto-refresh', function()
	{
		const $button = $(this)
		const target  = $button.data('target')
		refresh(target ? target : '#main')
	})

	//----------------------------------------------------------------------------- form.refresh-link
	$body.build('each', 'form.refresh-link[target]', function()
	{
		this.action = refreshLink($(this).data('target'))
	})

	//------------------------------------------------------------------------------ form.auto-submit
	$body.build('each', 'form.auto-submit', function()
	{
		const $form = $(this)
		let   delay = $form.data('delay')

		if (delay === undefined) {
			delay = 0
		}
		setTimeout(() => $form.submit(), delay)
	})

})
