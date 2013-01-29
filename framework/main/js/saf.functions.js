
//-------------------------------------------------------------------------- dateFormatToDatepicker
dateFormatToDatepicker = function(text)
{
	return text.replace("d", "dd").replace("m", "mm").replace("Y", "yy");
};

//------------------------------------------------------------------------------- getInputTextWidth
// TODO limit cache size as it could gros too much !
getInputTextWidth = function(text)
{
	return Math.max(40, getTextWidth(text) + 16);
};

//----------------------------------------------------------------------------------- getTextHeight
getTextHeight = function(text)
{
	$('<span id="height">').append(text.split("\n").join("<br>")).appendTo('body');
	var $height = $('#height');
	var height = $height.height();
	$height.remove();
	return height;
};

//------------------------------------------------------------------------------------ getTextWidth
get_text_width_cache = [];
getTextWidth = function(text)
{
	var width = get_text_width_cache[text];
	if (width != undefined) {
		return width;
	}
	else {
		$('<span id="width">').append(text.split("\n").join("<br>")).appendTo('body');
		var $width = $('#width');
		width = $width.width();
		$width.remove();
		get_text_width_cache[text] = width;
		return width;
	}
};
