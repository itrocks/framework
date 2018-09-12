$(window).scroll(function ()
{

	$('.window>h2:first-child').each(function()
	{
		var $this      = $(this);
		var $parent    = $this.parent();
		var parent_top = $parent.offset().top;

		if (parent_top < window.scrollbar.top()) {
			var top = 0;
			if (!$this.hasClass('fixed')) {
				var $element      = $this;
				var $next_element = $element;
				var margin_top    = parseInt($this.css('margin-top'));
				do {
					$element      = $next_element;
					$next_element = $element.next();
					var height = $element.height()
						+ parseInt($element.css('border-top-width'))
						+ parseInt($element.css('border-bottom-width'))
						+ Math.max(
							parseInt($element.css('margin-bottom')),
							parseInt($next_element.css('margin-top'))
						);
					var style = $element.attr('style');
					$element
						.addClass('fixed')
						.data('stay-top-style', (style === undefined) ? '' : style)
						.css({
							height:    $element.height(),
							position:  'fixed',
							top:       top,
							width:     $element.width(),

							'margin-bottom': 0,
							'margin-top':    0,
							'z-index':       window.zindex_counter + 2000
						})
						.data('stay-top', top);
					top += height;
				}
				while ($next_element.length && $next_element.is('.general.actions, .global-settings'));
				$this.after($('<div>').addClass('fixed stay-top').css({
					background:      'white',
					height:          top - $this.height(),
					position:        'fixed',
					top:             $this.height(),
					width:           $this.width(),
					'border-bottom': '1px solid darkgray',
					'z-index':       window.zindex_counter + 1000
				}).data('stay-top', $this.height()));
				$element.after($('<div>').addClass('stay-top').css({ height: top + margin_top }));
				$this
					.css({ 'border-top-left-radius': 0, 'border-top-right-radius': 0 })
					.data('stay-top-bottom', top);
			}

			$parent.children('.fixed').each(function() {
				$(this).css(
					'left',
					$parent.offset().left + parseInt($parent.css('padding-left')) - window.scrollbar.left()
				);
			});

			var max_top = parent_top + $parent.height()
				+ parseInt($parent.css('border-top-width'))
				+ parseInt($parent.css('border-bottom-width'))
				+ parseInt($parent.css('padding-bottom'))
				- window.scrollbar.top();
			top = $this.data('stay-top-bottom');

			if (top > max_top) {
				var diff = top - max_top;
				$this.data('stay-top-diff', diff);
				$parent.children('.fixed').each(function() {
					var $element = $(this);
					$element.css('top', $element.data('stay-top') - diff);
				});
			}
			else if ($this.data('stay-top-diff')) {
				$this.removeData('stay-top-diff');
				$parent.children('.fixed').each(function() {
					var $element = $(this);
					$element.css('top', $element.data('stay-top'));
				});
			}
		}

		else if ($this.hasClass('fixed')) {
			$parent.children('.stay-top').remove();
			var $fixed = $parent.children('.fixed');
			$fixed.each(function() {
				var $element = $(this);
				$element.attr('style', $element.data('stay-top-style'));
			});
			$fixed.removeClass('fixed')
				.removeData('stay-top-style')
				.removeData('stay-top');
			$this.removeData('stay-top-bottom');
		}

	});

});
