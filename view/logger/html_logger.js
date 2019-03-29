$(document).ready(function()
{

	$('fieldset #output>div').find('a[href]').click(function(event)
	{
		event.preventDefault();
		event.stopImmediatePropagation();
		alert('click deactivated from log entry output');
	});

});
