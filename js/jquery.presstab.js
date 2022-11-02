(function($)
{

	/**
	 * Allow HTML elements to accept tab keypress instead of using it to go to the next focusable element
	 *
	 * - Works with <textarea>
	 */
	$.fn.pressTab = function()
	{
		this.keydown(function(event)
		{
			if ((event.keyCode === 9) && !event.shiftKey) {
				const selection_start = this.selectionStart
				const selection_end   = this.selectionEnd
				const scroll_top      = this.scrollTop

				this.value = this.value.substring(0, selection_start)
					+ "\t"
					+ this.value.substring(selection_end, this.value.length)
				this.focus()

				this.selectionStart = selection_start + 1
				this.selectionEnd   = selection_start + 1
				this.scrollTop      = scroll_top

				event.preventDefault()
			}
		})

		return this
	}

})( jQuery )
