(function($)
{

	$.fn.sortcontent = function()
	{
		this.each(function() {
			var $this = $(this);
			var elements = false;
			var selected = null;
			if ($this.is('ul')) {
				elements = $this.children('li').get();
			}
			else if ($this.is('select')) {
				elements = $this.children('option').get();
				selected = $this.children('option[value="' + $this.val() + '"]');
			}
			else if ($this.is('table')) {
				var $tbody = $this.children('tbody');
				if ($tbody.length) {
					$this = $tbody;
				}
				elements = $this.children('tr').get();
			}
			if (elements) {
				elements.sort(function(a, b) {
					return (a.textContent.trim().toUpperCase() > b.textContent.trim().toUpperCase())
						? 1
						: -1;
				});
				$this.empty().append(elements);
				if (selected) {
					$this.val(selected.attr('value'));
				}
			}

		});

		return this;
	};

})( jQuery );
