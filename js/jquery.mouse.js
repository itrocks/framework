$(document).ready(function()
{

	//-------------------------------------------------------------------------------- document.mouse
	document.mouse = {
		which: 0,
		x:     0,
		y:     0,

		//--------------------------------------------------------------------- document.mouse.distance
		distance: function(from)
		{
			return Math.sqrt(Math.pow(from.x - this.x, 2) + Math.pow(from.y - this.y, 2))
		}
	}

	//---------------------------------------------------------------------------- document.mousedown
	$(document).mousedown(function(event)
	{
		document.mouse.which = event.which
	})

	//---------------------------------------------------------------------------- document.mousemove
	$(document).mousemove(function(event)
	{
		document.mouse.x = event.pageX
		document.mouse.y = event.pageY
	})

	//------------------------------------------------------------------------------ document.mouseup
	$(document).mouseup(function()
	{
		document.mouse.which = 0
	})

})
