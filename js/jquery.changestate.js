(function($)
{

	/**
	 * changeState jQuery plugin
	 *
	 * Calls change() when the state of the value changes from empty / non-empty after a key press
	 * "empty characters" can be configured into options.empty.
	 *
	 * @param options array
	 * @return jQuery
	 */
	$.fn.changeState = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		const settings = $.extend({
			empty: ' 0.,'
		}, options)

		//---------------------------------------------------------------------------------- isValueSet
		const isValueSet = function(value)
		{
			const empty  = settings.empty.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
			const regexp = new RegExp('/' + empty + '/')
			return value.replace(regexp, '').length
		}

		//--------------------------------------------------------------------------------------- keyup
		this.keyup(function(event)
		{
			const $this = $(this)
			if ($this.data('no-change-state')) {
				return
			}
			const is_set  = isValueSet($this.val())
			let   was_set = $this.data('change_state_was_set')
			if (was_set === undefined) {
				was_set = ($this.val() === String.fromCharCode(event.keyCode))
			}
			if ((is_set && !was_set) || (was_set && !is_set)) {
				$this.data('change_state_was_set', is_set)
				$this.change()
			}
		})

		return this
	}

})( jQuery )
