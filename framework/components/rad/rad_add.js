(function($)
{

	/**
	 * @param element_selector string
	 */
	$.fn.radAdd = function(element_selector)
	{

		// highlight zones
		this.each(function() {
			var $this = $(this);
			var app = window.app;
			$this.css("border", "2px solid lightgreen");
		});

		// horizontal or vertical ? (default is horizontal, if zero or one contained elements only)
		var vertical = false;
		elements = this.find(element_selector);
		if (elements.length > 1) {
			var position1 = $(elements[0]).position();
			var position2 = $(elements[1]).position();
			vertical = (position1.left == position2.left);
		}

		var old;

		//----------------------------------------------------------------------------------- mousemove
		/**
		 * Highlight top, right, bottom or left border of the element we want to add before / after
		 */
		this.add(element_selector).mousemove(function(event)
		{
			var $this = $(this);
			$this.mouseout();
			if (vertical) {
				var top = (event.pageY - $this.offset().top)
					< ($this.offset().top + $this.height() - event.pageY);
				if (top) {
					old = { element: $this, top: $this.css("border-top") };
					$this.css("border-top", "2px solid red");
				}
				else {
					old = { element: $this, bottom: $this.css("border-bottom") };
					$this.css("border-bottom", "2px solid red");
				}
			}
			else {
				var left = (event.pageX - $this.offset().left)
					< ($this.offset().left + $this.width() - event.pageX);
				if (left) {
					old = { element: $this, left: $this.css("border-left") };
					$this.css("border-left", "2px solid red");
				}
				else {
					old = { element: $this, right: $this.css("border-right") };
					$this.css("border-right", "2px solid red");
				}
			}
			event.stopImmediatePropagation();
		})

		//------------------------------------------------------------------------------------ mouseout
		.mouseout(function()
		{
			if (old != undefined) {
				if (old.bottom != undefined) old.element.css("border-bottom", old.bottom);
				if (old.left   != undefined) old.element.css("border-left",   old.left);
				if (old.right  != undefined) old.element.css("border-right",  old.right);
				if (old.top    != undefined) old.element.css("border-top",    old.top);
			}
		});

		return this;
	}

})( jQuery );
