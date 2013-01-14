//------------------------------------------------------------------------------- getInputTextWidth
get_input_text_width_cache = new Array();
getInputTextWidth = function(text)
{
	var width = get_input_text_width_cache[text];
	if (width != undefined) {
		return width;
	}
	else {
		$('<span id="width">').append(text + "W").appendTo('body');
		width = $('#width').width();
		$('#width').remove();
		width = Math.max(39, width + 3);
		get_input_text_width_cache[text] = width;
		return width;
	}
}

//----------------------------------------------------------------------------------- getTextHeight
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
