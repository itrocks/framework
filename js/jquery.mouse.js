$(document).ready(function() {

	document.mouse = { which: 0, x: 0, y: 0 };

	//---------------------------------------------------------------------------- document.mousedown
	$(document).mousedown(function(event)
	{
		document.mouse.which = event.which;
	});

	//---------------------------------------------------------------------------- document.mousemove
	$(document).mousemove(function(event)
	{
		document.mouse.x = event.pageX;
		document.mouse.y = event.pageY;
	});

	//------------------------------------------------------------------------------ document.mouseup
	$(document).mouseup(function()
	{
		document.mouse.which = 0;
	});


});
