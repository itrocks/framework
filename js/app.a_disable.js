$(document).ready(function()
{

	//--------------------------------------------------------- a, button, input[type=submit] disable
	/**
	 * Disable click on .disabled links
	 */
	$('a, button, input[type=submit]').build({ event: 'click', priority: 10, callback: function(event)
	{
		if ($(this).hasClass('disabled')) {
			event.preventDefault();
			event.stopImmediatePropagation();
		}
	}});

});
