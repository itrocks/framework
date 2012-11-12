//------------------------------------------------------------------------------- getInputTextWidth
getInputTextWidth = function(text)
{
	$('<span id="width">').append(text + "W").appendTo('body');
	var width = $('#width').width();
	$('#width').remove();
	return Math.max(39, width + 3);
}

//------------------------------------------------------------------------------------ getTextWidth
getTextWidth = function(text)
{
	$('<span id="width">').append(text).appendTo('body');
	var width = $('#width').width();
	$('#width').remove();
	return width;
}
