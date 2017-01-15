$(window).scroll(function ()
{

	$('.window>h2:first-child').each(function()
	{
		var $this      = $(this);
		var $parent    = $this.parent();
		var parent_top = $parent.offset().top;

		if (parent_top <= window.scrollbar.top()) {
			var $fixed = $this.next('.fixed');
			if (!$fixed.length) {
				$this.data('top',
					parseInt($this.parent().css('padding-top'))
					- parseInt($this.parent().css('border-top-width'))
				);
				$fixed = $this.clone()
					.addClass('fixed')
					.css({ position: 'fixed', width: $this.width(), 'z-index': 1 })
					.insertAfter($this)
					.build();
			}
			$fixed.css(
				'top',
				Math.min(parent_top + $parent.height() - window.scrollbar.top(), $this.data('top')) + 'px'
			);
		}

		else if ($this.next('.fixed').length) {
			$this.next('.fixed').remove();
		}

	});

});
