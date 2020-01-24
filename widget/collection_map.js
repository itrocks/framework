$(document).ready(function()
{
	var $body = $('body');

	//------------------------------------------------------------ li.component-objects => li.objects
	$body.build('call', 'li.component-objects > div > ul.map', function()
	{
		this.closest('li.component-objects').addClass('objects').removeClass('component-objects');
	});

	//------------------------------------------------------------ li.objects => li.component-objects
	$body.build('call', 'li.objects > div > ul.collection', function()
	{
		this.closest('li.objects').addClass('component-objects').removeClass('objects');
	});

});
