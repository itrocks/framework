$(document).ready(function()
{
	var $body = $('body');

	$body.build('each', 'a.auto-redirect', function()
	{
		var $a    = $(this);
		var delay = $a.data('delay');

		if (delay === undefined) {
			delay = 0;
		}
		setTimeout(function() { $a.click(); }, delay);
	});

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
