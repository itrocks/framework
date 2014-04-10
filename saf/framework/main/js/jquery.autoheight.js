(function($)
{

	$.fn.autoheight = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			maximum: 200
		}, options);

		//---------------------------------------------------------------------------- autoheight keyup
		this.keyup(function()
		{
			var $this = $(this);
			var previous_height = parseInt($this.attr('ui-text-height'));
			var new_height = Math.min(getInputTextHeight($this), settings.maximum);
			$this.attr('ui-text-height', new_height);
			if (new_height != previous_height) {
				var $table = $this.closest('table.collection, table.map');
				if (!$table.length) {
					// single element
					$this.height(new_height);
					$this.css('overflow','hidden');
				}
				else {
					// element into a collection / map
					// is element not named and next to a named element ? next_input = true
					var name = $this.attr('name');
					var next_input = false;
					if (name == undefined) {
						name = $this.prev('input').attr('name');
						next_input = true;
					}
					// calculate th's previous max height
					var position = $this.closest('td').prevAll('td').length;
					var $td = $($table.find('thead tr:first th')[position]);
					var previous_max_height = $td.height();
					if (new_height > previous_max_height) {
						// the element became wider than the widest element
						$td.height(new_height + $td.css('padding-top') + $td.css('padding-bottom'));
						$td.css('max-height', new_height + 'px');
						$td.css('min-height', new_height + 'px');
					}
					else if (previous_height == previous_max_height) {
						// the element was the widest element : grow or shorten
						new_height = 0;
						$table.find('[name="' + name + '"]').each(function()
						{
							var $this = $(this);
							if (next_input) {
								$this = $this.next('input');
							}
							var ui_text_height = parseInt($this.attr('ui-text-height'));
							if (ui_text_height == 0) {
								$this.attr(
									'ui-text-height', ui_text_height = getTextHeight($this.val())
								);
							}
							if (ui_text_height > new_height) {
								new_height = ui_text_height;
							}
						});
						$td.height(new_height);
						$td.css('max-height', new_height + 'px');
						$td.css('min-height', new_height + 'px');
					}
				}
			}
		});

		//----------------------------------------------------------------------------- autoheight init
		$(this).keyup();

		return this;
	};

})( jQuery );
