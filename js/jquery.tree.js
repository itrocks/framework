(function($)
{

	$.fn.tree = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		const settings = $.extend({
			editable: false
		}, options)

		if (settings.editable) {
			this.find('li > span').attr('contenteditable', true)
		}

		return this
	}

})( jQuery )
