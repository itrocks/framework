(function($)
{

	/**
	 * Autofocus the first modifiable child element
	 */
	$.fn.autofocus = function()
	{
		$(this)
			.find("input[autocomplete], input[name], select[name]")
			.filter(":visible:not([readonly])")
			.first()
			.focus();

		return true;
	};

})( jQuery );
