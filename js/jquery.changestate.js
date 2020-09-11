(function($)
{

	/**
	 * changeState jQuery plugin
	 *
	 * Calls change() when the state of the value changes from empty / non-empty after a key press
	 * "empty characters" can be configured into options.empty.
	 *
	 * @param options array
	 * @returns jQuery
	 */
	$.fn.changeState = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			empty: ' 0.,'
		}, options);

		//---------------------------------------------------------------------------------- isValueSet
		var isValueSet = function(value)
		{
			var empty  = settings.empty.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
			var regexp = new RegExp('/' + empty + '/');
			return value.replace(regexp, '').length;
		};

		//--------------------------------------------------------------------------------------- keyup
		this.keyup(function(event)
		{
			var $this = $(this);
			if ($this.data('no-change-state')) {
				return;
			}
			var is_set  = isValueSet($this.val());
			var was_set = $this.data('change_state_was_set');
			if (was_set === undefined) {
				was_set = ($this.val() === String.fromCharCode(event.keyCode));
			}
			if ((is_set && !was_set) || (was_set && !is_set)) {
				$this.data('change_state_was_set', is_set);
				$this.change();
			}
		});

		return this;
	};

})( jQuery );
