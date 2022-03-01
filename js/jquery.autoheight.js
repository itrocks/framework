(function($)
{

	$.fn.autoHeight = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		const settings = $.extend({
			minimum: 23,
			maximum: 280
		}, options)

		//---------------------------------------------------------------------------------- autoHeight
		const autoHeight = function(additional_text)
		{
			if (additional_text === undefined) {
				additional_text = ''
			}
			const $this           = $(this)
			const previous_height = parseInt($this.data('ui-text-height'))
			const new_height      = Math.min(Math.max(
				getInputTextHeight($this, additional_text),
				settings.minimum), settings.maximum
			)

			$this.data('ui-text-height', new_height)
			if (new_height !== previous_height) {
				$this.height(new_height)
			}
		}

		if (this.data('plugins.autoHeight')) {
			autoHeight.call(this)
			return this
		}
		this.data('plugins.autoHeight', true)

		//------------------------------------------------------------------------- autoHeight keypress
		this.keypress(function(event)
		{
			if ((event.keyCode >= 32) || (event.keyCode === 13)) {
				const additional_text = (event.keyCode === 13) ? "\n" : String.fromCharCode(event.charCode)
				autoHeight.call(this, additional_text)
			}
		})

		//----------------------------------------------------------------------------- autoHeight init
		this.each(autoHeight)

		return this
	}

})( jQuery )
