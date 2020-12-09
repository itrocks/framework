$(document).ready(function()
{
	var $body = $('body');

	//------------------------------------------------------------ li.component-objects => li.objects
	$body.build('call', '.component-objects > .map', function()
	{
		this.closest('.component-objects').addClass('objects').removeClass('component-objects');
	}, { priority: 1 });

	//------------------------------------------------------------ li.objects => li.component-objects
	$body.build('call', '.objects > .collection', function()
	{
		this.closest('.objects').addClass('component-objects').removeClass('objects');
	}, { priority: 1 });

});
