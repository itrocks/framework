$(document).ready(function()
{

	$('a.auto-redirect').build('each', function()
	{
		var $a    = $(this);
		var delay = $a.data('delay');

		if (delay === undefined) {
			delay = 0;
		}
		setTimeout(function() { $a.click(); }, delay);
	});

	$('form.auto-submit').build('each', function()
	{
		var $form = $(this);
		var delay = $form.data('delay');

		if (delay === undefined) {
			delay = 0;
		}
		setTimeout(function () { $form.submit(); }, delay);
	});

});
