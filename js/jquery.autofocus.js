(function($)
{

	var enabled = true;

	/**
	 * Autofocus the first modifiable child element
	 *
	 * @param enable boolean if set, allow to disable / re-enable autofocus management
	 */
	$.fn.autofocus = function(enable)
	{
		if (enable !== undefined) {
			enabled = enable;
			return;
		}
		if (!enabled) {
			return;
		}
		var $this = $(this);
		var controls = $this
			.find('input, select, textarea')
			.filter(':visible:not([readonly])');
		var position = 0;
		var length   = controls.length;
		while (
			((position < length) && (length > 2))
			|| (controls.eq(position).attr('name') === 'login')
			|| (controls.eq(position).attr('name') === 'password')
			|| (controls.eq(position).attr('name') === 'password2')
		) {
			position ++;
		}
		if (position >= length) {
			position = 0;
		}
		if (length) {
			var control = controls.eq(position);
			setTimeout(function() { control.focus(); }, 0);
		}

		return true;
	};


})( jQuery );
