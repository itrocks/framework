(function($)
{

	$.fn.slideShow = function()
	{
		var elements = this;
		var position = elements.length - 1;
		for (var i = 0; i < position; i++) {
			$(elements[i]).hide();
		}

		setInterval(function()
		{
			$(elements[position]).fadeOut(1000);
			position ++;
			if (position >= elements.length) {
				position = 0;
			}
			$(elements[position]).fadeIn(1000);
		}, 5000);

		return this;
	};

})( jQuery );
