
//------------------------------------------------------------------------------- copyCssPropertyTo
copyCssPropertyTo = function(context, element)
{
	var tab = ["font-size",
		"font-family",
		"line-height",
		"border-bottom-width",
		"border-left-width",
		"border-top-width",
		"border-right-width",
		"border-color",
		"margin-bottom",
		"margin-left",
		"margin-right",
		"margin-top",
		"text-rendering",
		"word-wrap"
	];
	for (var i = 0; i < tab.length; i++) {
		element.css(tab[i], context.css(tab[i]));
	}
};

//-------------------------------------------------------------------------- dateFormatToDatepicker
dateFormatToDatepicker = function(text)
{
	return text.replace("d", "dd").replace("m", "mm").replace("Y", "yy");
};

//------------------------------------------------------------------------------ getInputTextHeight
getInputTextHeight = function(context)
{
	return Math.max(20, getTextHeight(context, 16));
};

//------------------------------------------------------------------------------- getInputTextWidth
// TODO limit cache size as it could gros too much !
getInputTextWidth = function(context)
{
	return Math.max(40, getTextWidth(context, 16));
};

//----------------------------------------------------------------------------------- getTextHeight
getTextHeight = function(context, extraHeight)
{
	var $content = context.val().split("\n");
	// If the last element is empty, need put a character to prevent the browser ignores the character
	var $last_index = $content.length -1;
	if (!$content[$last_index]) {
		$content[$last_index] = "_";
	}
	$('<div id="height">').append($content.join("<br>")).appendTo(context.parent());
	var $height = $('#height');
	copyCssPropertyTo(context, $height);
	$height.css("position", "absolute");
	var $width = getInputTextWidth(context);
	$height.width($width);
	var height = $height.height() + extraHeight;
	$height.remove();
	 return height;
};

//------------------------------------------------------------------------------------ getTextWidth
get_text_width_cache = [];
getTextWidth = function(context, extraWidth)
{
	var width = get_text_width_cache[context.val()];
	if (width != undefined) {
		return width;
	}
	else {
		var $content = context.val().replace(" ", "_").split("\n");
		$('<span id="width">').append($content.join("<br>")).appendTo('body');
		var $width = $('#width');
		copyCssPropertyTo(context, $width);
		$width.css("position", "absolute");
		var $pos = context.position();
		$width.css("top", $pos.top);
		$width.css("left", $pos.left);
		width = $width.width() + extraWidth;
		var $parent = context.parent();
		var $margins = parseInt($parent.css("margin-right"))
			+ parseInt($parent.css("padding-right"))
			+ parseInt(context.css("margin-right"));
		var ending_right_parent = ($(window).width() - ($parent.offset().left + $parent.outerWidth()));
		ending_right_parent += $margins;
		var ending_right = ($(window).width() - ($width.offset().left + $width.outerWidth()) - extraWidth);
		if (ending_right < ending_right_parent) {
			width = width - (ending_right_parent - ending_right);
		}
		$width.remove();
		get_text_width_cache[context.val()] = width;
		return width;
	}
};

//---------------------------------------------------------------------------------------- redirect
/**
 *
 *
 * @param uri
 * @param target
 */
redirect = function(uri, target)
{
	//noinspection JSUnresolvedVariable
	var app = window.app;
	var more = ((target != undefined) && (target != "") && (target[0] == '#')) ? "&as_widget=1" : "";
	uri = app.uri_base + uri + "?PHPSESSID=" + app.PHPSESSID;
	if (!more) {
		window.location = uri;
	}
	else {
		$.ajax({ url: uri + more, success: function(data) { $(target).html(data).build(); } });
	}
};
