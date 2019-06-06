(function($)
{

	$.fn.autoHeight = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			maximum: 280
		}, options);

		//---------------------------------------------------------------------------------- autoHeight
		var autoHeight = function(additional_text)
		{
			if (additional_text === undefined) {
				additional_text = '';
			}
			var $this           = $(this);
			var previous_height = parseInt($this.data('ui-text-height'));
			var new_height      = Math.min(getInputTextHeight($this, additional_text), settings.maximum);

			var line_height = function($element)
			{
				var font_size   = parseInt($element.css('font-size'));
				var line_height = $element.css('line-height');
				if (line_height.endsWith('px')) {
					return parseInt(line_height);
				}
				else if (parseInt(line_height).toString() === line_height) {
					return font_size * line_height;
				}
			}($this);

			new_height = Math.round(new_height / line_height) * line_height + 1;
			$this.data('ui-text-height', new_height);
			if (new_height !== previous_height) {
				$this.height(new_height);
				if ($this.parent().is('li') && $this.height() < $this.parent().height()) {
					$this.height($this.parent().height());
				}
			}
		};

		if (this.data('plugins.autoHeight')) {
			autoHeight.call(this);
			return this;
		}
		this.data('plugins.autoHeight', true);

		//------------------------------------------------------------------------- autoHeight keypress
		this.keypress(function(event)
		{
			if ((event.keyCode >= 32) || (event.keyCode === 13)) {
				var additional_text = (event.keyCode === 13) ? "\n" : String.fromCharCode(event.charCode);
				autoHeight.call(this, additional_text);
			}
		});

		//----------------------------------------------------------------------------- autoHeight init
		autoHeight.call(this);

		return this;
	};

})( jQuery );
