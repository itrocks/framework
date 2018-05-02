(function($)
{

	$.fn.autoHeight = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			maximum: 280
		}, options);

		//---------------------------------------------------------------------------- autoHeight keyup
		this.keyup(function()
		{
			var $this           = $(this);
			var previous_height = parseInt($this.attr('ui-text-height'));
			var new_height      = Math.min(getInputTextHeight($this), settings.maximum);

			var line_height     = function($element)
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
			$this.attr('ui-text-height', new_height);
			if (new_height !== previous_height) {
				$this.height(new_height);
			}
		});

		//----------------------------------------------------------------------------- autoHeight init
		$(this).keyup();

		return this;
	};

	$.fn.autoheight = $.fn.autoHeight;

})( jQuery );
