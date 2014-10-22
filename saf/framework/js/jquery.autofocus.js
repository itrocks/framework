(function($)
{

	/**
	 * Autofocus the first modifiable child element
	 */
	$.fn.autofocus = function()
	{
		var $this = $(this);
		var controls = $this
			.find('input[autocomplete], input[name], select[name], textarea[name]')
			.filter(':visible:not([readonly])');
		var i = 0;
		var length = controls.length;
		while (
			((i < length) && (length > 2))
			&& (controls.eq(i).attr('name') == 'login') || (controls.eq(i).attr('name') == 'password')
		) {
			i ++;
		}
		if (i < length) {
			var control = controls.eq(i);
			setTimeout(function() { control.focus(); }, 1);
		}

		return true;
	};

})( jQuery );
