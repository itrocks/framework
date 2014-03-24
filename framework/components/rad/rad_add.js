(function($)
{

	/**
	 * Call this on a jQuery object you want to highlight and enable inserting elements between
	 * some contained elements
	 *
	 * @param element_selector string the childs element selector between which the user will insert
	 * @param hint             string the hint text to display and follow the mouse cursor
	 */
	$.fn.radAdd = function(element_selector, hint)
	{

		// highlight zones
		this.each(function() {
			var $this = $(this);
			$this.css('border', '2px solid lightgreen');
		});

		// horizontal or vertical ? (default is horizontal, if zero or one contained elements only)
		var vertical = false;
		var elements = (element_selector[0] == '>')
			? this.children(element_selector.substr(1))
			: this.find(element_selector);
		if (elements.length > 1) {
			var position1 = $(elements[0]).position();
			var position2 = $(elements[1]).position();
			vertical = (position1.left == position2.left);
		}

		// awful patch : display fields elements as blocks as table-row will not get red borders (but why ?)
		elements.filter('fieldset>div').css('display', 'block');

		//--------------------------------------------------------------------------------------- click
		/**
		 * On clicking an element : the default action will be ignored : we call the insert form instead
		 */
		elements.click(function(event)
		{
			console.log('good job guy');
			event.preventDefault();
			event.stopImmediatePropagation();
		});

		//----------------------------------------------------------------------------------- mousemove
		/**
		 * On entering : creates hint box and highlights.
		 * On moving : moves hint box,
		 * Highlights top, right, bottom or left border of the element we want to add before / after
		 */
		elements.mousemove(function(event)
		{
			var $this = $(this);
			// hover
			if (!$this.data('current')) {
				var $body = $('body');
				var $currently_hover = $body.data('currently-hover');
				if ($currently_hover != undefined) {
					$currently_hover.data('force-out', true);
					$currently_hover.mouseout();
				}
				// hint box
				if ($this.data('hint-element') == undefined) {
					var $hint_element = $('<div>');
					$hint_element.css({
						'background-color': 'white',
						border:   '1px solid black',
						display: 'inline-block',
						left: event.pageX + 30 + 'px',
						padding:  '2px',
						position: 'absolute',
						top: event.pageY + 30 + 'px'
					})
						.text(hint)
						.appendTo('body');
					$this.data('hint-element', $hint_element);
				}
				// save css border
				if ($this.data('old-css') == undefined) {
					$this.data('old-css', {
						border: $this.css('border'),
						'border-bottom': $this.css('border-bottom'),
						'border-left':   $this.css('border-left'),
						'border-right':  $this.css('border-right'),
						'border-top':    $this.css('border-top')
					});
				}
				// save currently inside
				$body.data('currently-hover', $this);
				$this.data('current', true);
			}
			// hint box follows the mouse
			if ($this.data('hint-element') != undefined) {
				$this.data('hint-element').css({
					left: event.pageX + 10 + 'px', top: event.pageY + 10 + 'px'
				});
			}
			// restore old css
			var old_css = $this.data('old-css');
			if (old_css != undefined) {
				$this.css(old_css);
				// show red lines
				if (vertical) {
					var top = (event.pageY - $this.offset().top)
						< ($this.offset().top + $this.height() - event.pageY);
					if (top) {
						$this.css('border-top', '2px solid red');
					}
					else {
						$this.css('border-bottom', '2px solid red');
					}
				}
				else {
					var left = (event.pageX - $this.offset().left)
						< ($this.offset().left + $this.width() - event.pageX);
					if (left) {
						$this.css('border-left', '2px solid red');
					}
					else {
						$this.css('border-right', '2px solid red');
					}
				}
				event.stopImmediatePropagation();
			}
		});

		//------------------------------------------------------------------------------------ mouseout
		/**
		 * Removes hint box
		 * Reset not highlighted border style css
		 */
		elements.mouseout(function(event)
		{
			var $this = $(this);
			if (
				event.pageX < $this.offset().left
				|| event.pageX > ($this.offset().left + $this.width())
				|| event.pageY < $this.offset().top
				|| event.pageY > ($this.offset().top + $this.height())
				|| $this.data('force-out')
			) {
				if ($this.data('force-out')) {
					$this.removeData('force-out');
				}
				// restore old css
				var old_css = $this.data('old-css');
				if (old_css != undefined) {
					$this.css(old_css);
					$this.removeData('old-css');
				}
				// remove hint box
				var $hint_element = $this.data('hint-element');
				if ($hint_element != undefined) {
					$hint_element.remove();
					$this.removeData('hint-element');
				}
				// remove current
				if ($this.data('current')) {
					$this.removeData('current');
					$('body').removeData('currently-hover');
				}
			}
		});

		return this;
	}

})( jQuery );
