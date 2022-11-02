(function($)
{

	let enabled = true

	/**
	 * Autofocus the first modifiable child element
	 *
	 * @param enable boolean if set, allow to disable / re-enable autofocus management
	 */
	$.fn.autofocus = function(enable)
	{
		if (enable !== undefined) {
			enabled = enable
			return
		}
		const $this = $(this)
		if (!enabled || isPhone() || $this.find('.disable-autofocus').length) {
			return
		}
		const $inputs = $this.find('input, select, textarea')
		const $focus  = $inputs.filter('[data-focus]')
		if ($focus.length) {
			setTimeout(() => $focus.focus())
			return true
		}
		const $controls = $inputs.filter(':visible:not([readonly]):not([data-no-autofocus])')
		let   position  = 0
		const length    = $controls.length
		while (
			((position < length) && (length > 2))
			|| ($controls.eq(position).attr('name') === 'login')
			|| ($controls.eq(position).attr('name') === 'password')
			|| ($controls.eq(position).attr('name') === 'password2')
		) {
			position ++
		}
		if (position >= length) {
			position = 0
		}
		if (length) {
			const $control = $controls.eq(position)
			setTimeout(() => $control.focus())
		}

		return true
	}

})( jQuery )
