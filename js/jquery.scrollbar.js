
//---------------------------------------------------------------------- window.scrollbar shortcuts
window.scrollbar = {

	//------------------------------------------------------------------------- window.scrollbar.left
	left: function(set_left)
	{
		var $body = $('body');
		if (set_left !== undefined) {
			document.documentElement.scrollLeft = set_left;
			$body.scrollLeft(set_left);
		}
		return document.documentElement.scrollLeft
			? document.documentElement.scrollLeft
			: $body.scrollLeft();
	},

	//-------------------------------------------------------------------------- window.scrollbar.top
	top: function(set_top)
	{
		var $body = $('body');
		if (set_top !== undefined) {
			document.documentElement.scrollTop = set_top;
			$body.scrollTop(set_top);
		}
		return document.documentElement.scrollTop
			? document.documentElement.scrollTop
			: $body.scrollTop();
	}

};

//-------------------------------------------------------------------------------- jQuery.scrollbar
(function($)
{

	//--------------------------------------------------------------- horizontal / vertical scrollbar
	var scrollbar = function(settings)
	{
		var $element   = this;
		var $scrollbar = $(
			'<div class="' + (settings.arrows ? 'arrows ' : '') + settings.direction + ' scrollbar">'
			+ (settings.arrows ? '<div class="previous"/><div class="scroll">' : '')
			+ '<div class="bar"/>'
			+ (settings.arrows ? '</div><div class="next"/>' : '')
			+ '</div>'
		);
	};

	//------------------------------------------------- both / horizontal / vertical scrollbar plugin
	$.fn.scrollbar = function (settings)
	{
		settings = $.extend({
			arrows:    false, // false, true
			direction: 'both' // both, horizontal, vertical
		}, settings);

		var directions = (settings.direction === 'both')
			? ['horizontal', 'vertical']
			: [settings.direction];

		for (var direction in directions) if (directions.hasOwnProperty(direction)) {
			settings.direction = directions[direction];
			scrollbar.call(this, settings);
		}
	};
})( jQuery );
