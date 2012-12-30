//------------------------------------------------------------------------------- getInputTextWidth
getInputTextWidth = function(text)
{
	$('<span id="width">').append(text + "W").appendTo('body');
	var width = $('#width').width();
	$('#width').remove();
	return Math.max(39, width + 3);
}

getTextHeight = function(text)
{
	$('<span id="height">').append(text.replace(/\n/g, '<br>')).appendTo('body');
	var height = $('#height').height();
	$('#height').remove();
	return height;
}

//------------------------------------------------------------------------------------ getTextWidth
getTextWidth = function(text)
{
	$('<span id="width">').append(text).appendTo('body');
	var width = $('#width').width();
	$('#width').remove();
	return width;
}
