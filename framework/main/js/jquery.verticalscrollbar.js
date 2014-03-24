(function($)
{

	$.fn.verticalscrollbar = function()
	{

		//----------------------------------------------------------------------- .vertical.scrollbar
		// vertical scrollbar
		this.each(function()
		{
			var $this     = $(this);
			var $rowheight = $this.closest('tr').height() - 1;
			var $up       = $this.children('.up');
			var $position = $this.children('.position');
			var $down     = $this.children('.down');
			var start     = $this.data('start') - 1;
			var length    = $this.data('length');
			var total     = $this.data('total');
			var height    = Math.max(($rowheight * length) - 1, $this.innerHeight());
			var real_start  = Math.round((start * height) / total);
			var real_height = Math.max(Math.round((length * height) / total), 16);
			if ((real_start + real_height) > height) {
				real_start = height - real_height;
			}
			var real_end = height - real_start - real_height;
			$up.height(real_start);
			$position.height(real_height);
			$down.height(real_end);
		});
		this.find('.position').each(function()
		{
			var $this = $(this);
			$this.draggable({
				containment: $this.parent(),
				opacity: .7,

				stop: function()
				{
					var $this = $(this);
					var $scrollbar = $this.parent();
					var old_start = $scrollbar.data('start');
					var start = $scrollbar.children('.up').height()
						+ parseInt($this.css('top').replace('px', ''));
					var new_start = Math.round(
						((start * $scrollbar.data('total')) / $scrollbar.innerHeight())
					) + 1;
					$this.attr('href', $this.attr('href').replace('=' + old_start, '=' + new_start));
					$this.click();
				}

			});
		});

		return this;
	};

})( jQuery );
