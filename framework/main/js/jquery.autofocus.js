(function($)
{

	/**
	 * Autofocus the first modifiable child element
	 */
	$.fn.autofocus = function()
	{
		$(this).find(
			"input[name]:visible:not([readonly]),"
			+ " select[name]:visible:not([readonly]),"
			+ " input[autocomplete]:visible:not([readonly])"
		).first().focus();

		return true;
	}

})( jQuery );
