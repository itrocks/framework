(function($)
{

	$.fn.autoHeight = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			maximum: 400,
			overflow: 'scroll'
		}, options);

		//----------------------------------------------------------------------------- autoHeight blur
		this.blur(function()
		{
			var $this = $(this);
			$this.css('overflow', '');
			$this.data('focus', false);
			$this.keyup();
		});

		//---------------------------------------------------------------------------- autoHeight focus
		this.focus(function()
		{
			var $this = $(this);
			$this.css('overflow', settings.overflow);
			$this.data('focus', true);
			$this.keyup();
		});

		//---------------------------------------------------------------------------- autoHeight keyup
		this.keyup(function()
		{
			var $this = $(this);
			var previous_height = parseInt($this.attr('ui-text-height'));
			var new_height = Math.min(getInputTextHeight($this), settings.maximum);
			if (!$this.data('focus')) {
				new_height -= 15;
			}
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
