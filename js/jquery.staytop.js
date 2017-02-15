$(window).scroll(function ()
{

	$('.window>h2:first-child').each(function()
	{
		var $this      = $(this);
		var $parent    = $this.parent();
		var parent_top = $parent.offset().top;

		if (parent_top < window.scrollbar.top()) {
			var $fixed = $this.next('.fixed');
			if ($fixed.length) {
				$fixed = $fixed.add($fixed.next('.fixed'));
			}
			else {
				var $next = $this.next('.actions');
				if ($next.length) {
					// first fixed element's top
					$this.data('top',
						parseInt($this.parent().css('padding-top'))
						- parseInt($this.parent().css('border-top-width'))
					);
					// first element (title bar)
					$fixed = $this.clone().css({'margin-bottom': 0});
					// second element (actions bar)
					var $fixed2 = $next.clone().css({
						background:            'white',
						'border-bottom-color': $this.css('border-bottom-color'),
						'border-bottom-style': $this.css('border-bottom-style'),
						'border-bottom-width': $this.css('border-bottom-width')
					});
					$fixed2.css({
						'margin-bottom':  0,
						'margin-top':     0,
						'padding-bottom':
							parseInt($next.css('margin-bottom') + parseInt($next.css('padding-bottom'))) + 'px',
						'padding-top':
							parseInt($next.css('margin-top') + parseInt($next.css('padding-top'))) + 'px'
					});
					// common to the two elements
					$fixed = $fixed.add($fixed2)
						.addClass('fixed')
						.css({position: 'fixed', width: $this.width(), 'z-index': 1})
						.insertAfter($this)
						.build();
					var height = 0;
					$fixed.each(function() {
						var $this = $(this);
						$this.data('height',
							$this.height()
							+ parseInt($this.css('margin-top'))
							+ parseInt($this.css('border-top-width'))
							+ parseInt($this.css('border-bottom-width'))
							+ parseInt($this.css('margin-bottom'))
						);
						height += $this.data('height');
					});
					$fixed.data('fixed-height', height);
				}
			}
			// top position
			var top = Math.min(
				$this.data('top'),
				parent_top + $parent.height() - window.scrollbar.top() - $fixed.data('fixed-height')
			);
			$fixed.each(function() {
				var $this = $(this);
				$this.css('top', top + 'px');
				top += $this.data('height');
			});
		}

		else while ($this.next('.fixed').length) {
			$this.next('.fixed').remove();
		}

	});

});
