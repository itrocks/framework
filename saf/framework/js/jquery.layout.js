(function($)
{

	/**
	 * layout plugin for jQuery
	 */
	$.fn.inputTitleHelper = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			help_class: 'helper'
		}, options);

		return this;
	};

})( jQuery );
