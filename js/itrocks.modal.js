$(document).ready(function()
{

	//----------------------------------------------------------------------- #modal .actions a click
	$('body').build('call', '#modal .actions', function()
	{
		var $actions = $(this);

		$actions.find('a').click(function()
		{
			$(this).closest('#modal').empty();
		});

		$actions.find('> .close > a').click(function(event)
		{
			$(this).closest('#modal').empty();
			event.preventDefault();
			event.stopImmediatePropagation();
		});
	});

});
