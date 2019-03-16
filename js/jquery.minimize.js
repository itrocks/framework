(function($)
{

	$.fn.minimize = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			absolute_next:   false,     // set next element position absolute when minimized
			button:          undefined, // ie ('.minimizable')
			duration:        150,
			html_maximized:  undefined, // ie 'minimize'
			html_minimized:  undefined, // ie 'maximize'
			minimize_class:  'minimize',
			minimized_class: 'minimized',
			minimized_hide:  false,
			min_height:      24,
			min_padding:     0,
			min_width:       24
		}, options);

		this.each(function()
		{
			var $this = $(this);

			var $button = settings.button;
			if ($button === undefined) {
				var html = (settings.html_maximized === undefined) ? 'minimize' : settings.html_maximized;
				$button = $('<div class="' + settings.minimize_class + '">' + html + '</div>');
				$this.prepend($button);
			}

			//------------------------------------------------------------------------------------ maximize
			var maximize = function()
			{
				var $this = this;
				$this.removeClass(settings.minimized_class);
				if (settings.absolute_next) {
					$this.next().css('position', $this.data('next_position_backup'));
					$this.removeData('next_position_backup');
				}
				$this.animate(
					{
						height:  $this.data('height'),
						padding: $this.data('padding'),
						width:   $this.data('width')
					},
					settings.duration,
					function()
					{
						$this.css({ height: '', overflow: '', padding: '', width: '' });
						if (settings.html_maximized !== undefined) {
							$button.html(settings.html_maximized);
						}
					}
				);
			};

			//------------------------------------------------------------------------------------ minimize
			var minimize = function()
			{
				var $this = this;
				var padding = [
					$this.css('padding-top'),
					$this.css('padding-right'),
					$this.css('padding-bottom'),
					$this.css('padding-left')
				];
				$this.data('height',  $this.height() + 'px');
				$this.data('padding', padding.join(SP));
				$this.data('width',   $this.width() + 'px');
				$this.css({ overflow: 'hidden' });
				$this.animate(
					{
						height: settings.min_height + 'px',
						padding: settings.min_padding + 'px',
						width: settings.min_width + 'px'
					},
					settings.duration,
					function() {
						$this.addClass(settings.minimized_class);
						if (settings.html_minimized !== undefined) {
							$button.html(settings.html_minimized);
						}
					}
				);
				if (settings.absolute_next) {
					setTimeout(
						function () {
							var $next = $this.next();
							$this.data('next_position_backup', $next.css('position'));
							$next.css('position', 'absolute');
						},
						settings.duration
					);
				}
			};

			//----------------------------------------------------------------------------- .minimize click
			$button.click(function()
			{
				if ($this.hasClass(settings.minimized_class)) {
					$this.maximize = maximize;
					$this.maximize();
				}
				else {
					$this.minimize = minimize;
					$this.minimize();
				}
			});

			if ($this.hasClass('minimized')) {
				$this.minimize = minimize;
				$this.minimize();
			}

		});

		return this;
	};

})( jQuery );
