(function($)
{

	$.fn.verticalscrollbar = function()
	{

		//------------------------------------------------------------------------- .vertical.scrollbar
		// vertical scrollbar
		this.each(function()
		{
			var $this     = $(this);
			var $up       = $this.children('.up');
			var $position = $this.children('.position');
			var $down     = $this.children('.down');

			var data_start   = $this.data('start') - 1;
			var data_length  = $this.data('length');
			var data_total   = $this.data('total');

			var tr_height        = $this.closest('tr').height() - 1;
			var scrollbar_height = Math.max((tr_height * data_length), $this.height());
			var position_height  = Math.max(Math.round((data_length * scrollbar_height) / data_total), 15);
			var scroll_height    = scrollbar_height - position_height;
			var position_start   = (data_total - data_length > 0)
				? Math.round((data_start * scroll_height) / (data_total - data_length))
				: 0;

			if ((position_start + position_height) > scrollbar_height) {
				position_start = position_height - scrollbar_height;
			}
			var end_height = scrollbar_height - position_start - position_height;

			$up.height(position_start);
			$position.height(position_height);
			$down.height(end_height);
		});

		this.find('.position').each(function()
		{
			var $this = $(this);
			$this.draggable({
				containment: $this.parent(),
				opacity: .5,

				stop: function()
				{
					var $this      = $(this);
					var $scrollbar = $this.parent();

					var old_start   = $scrollbar.data('start');
					var data_length = $scrollbar.data('length');
					var data_total  = $scrollbar.data('total');

					var scrollbar_height = $scrollbar.innerHeight() - 1;
					var position_height  = $scrollbar.children('.position').outerHeight() - 1;
					var scroll_height    = scrollbar_height - position_height;

					var position_start
						= $scrollbar.children('.up').height() + parseInt($this.css('top').repl('px', ''));
					var new_start
						= Math.round(((position_start * (data_total - data_length)) / scroll_height)) + 1;

					if (new_start !== old_start) {
						$this.attr('href', $this.attr('href').repl('=' + old_start, '=' + new_start));
						$this.click();
					}
				}

			});
		});

		return this;
	};

})( jQuery );
