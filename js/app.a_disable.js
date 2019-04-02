$(document).ready(function()
{

	//--------------------------------------------------------- a, button, input[type=submit] disable
	/**
	 * Disable click on .disabled links
	 */
	$('body').build({
		event: 'click', priority: 10, selector: 'a, button, input[type=submit]',
		callback: function(event)
		{
			if ($(this).hasClass('disabled')) {
				event.preventDefault();
				event.stopImmediatePropagation();
			}
		}
	});

});
