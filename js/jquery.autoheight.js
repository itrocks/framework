(function($)
{

	$.fn.autoHeight = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		const settings = $.extend({
			minimum: 21,
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
			let   new_height      = Math.min(getInputTextHeight($this, additional_text), settings.maximum)

			const line_height = function($element)
			{
				const font_size   = parseInt($element.css('font-size'))
				const line_height = $element.css('line-height')
				if (line_height.endsWith('px')) {
					return parseInt(line_height)
				}
				else if (parseInt(line_height).toString() === line_height) {
					return font_size * line_height
				}
			}($this)

			new_height = Math.max(
				Math.round(new_height / line_height) * line_height + 1, settings.minimum
			)

			$this.data('ui-text-height', new_height)
			if (new_height !== previous_height) {
				$this.height(new_height)
				if ($this.parent().is('li') && $this.height() < $this.parent().height()) {
					$this.height($this.parent().height())
				}
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
