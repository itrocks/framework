$(document).ready(function()
{
	var $body = $('body');

	//------------------------------------------------------------------------------- a.auto-redirect
	$body.build('each', 'a.auto-redirect', function()
	{
		var $a    = $(this);
		var delay = $a.data('delay');

		if (delay === undefined) {
			delay = 0;
		}

		var condition = true;
		var data_if   = $a.data('if');
		if (data_if) {
			condition = $(data_if).length;
		}
		if (condition) {
			setTimeout(function () { $.data($a[0], 'events') ? $a.click() : $a[0].click(); }, delay);
		}
	});

	//-------------------------------------------------------------------------------- a.refresh-link
	$body.build('each', 'a.refresh-link[target]', function()
	{
		this.href = refreshLink($(this).attr('target'));
	});

	//--------------------------------------------------------------------------- button.auto-refresh
	$body.build('each', 'button.auto-refresh', function()
	{
		var $button = $(this);
		var target  = $button.data('target');
		refresh(target ? target : '#main');
	});

	//----------------------------------------------------------------------------- form.refresh-link
	$body.build('each', 'form.refresh-link[target]', function()
	{
		this.action = refreshLink($(this).data('target'));
	});

	//------------------------------------------------------------------------------ form.auto-submit
	$body.build('each', 'form.auto-submit', function()
	{
		var $form = $(this);
		var delay = $form.data('delay');

		if (delay === undefined) {
			delay = 0;
		}
		setTimeout(function () { $form.submit(); }, delay);
	});

});
