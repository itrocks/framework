(function($)
{

	//------------------------------------------------------------------------------ $.fn.sortContent
	/**
	 * @param separator string separator elements selector
	 * @return jQuery
	 */
	$.fn.sortContent = function(separator)
	{

		this.each(function() {
			let $this     = $(this)
			let $elements = null
			let $selected = null
			if ($this.is('ul')) {
				$elements = $this.children('li')
			}
			else if ($this.is('select')) {
				$elements = $this.children('option')
				$selected = $this.children('option[value="' + $this.val() + '"]')
			}
			else if ($this.is('table')) {
				const $tbody = $this.children('tbody')
				if ($tbody.length) {
					$this = $tbody
				}
				$elements = $this.children('tr')
			}
			if ($elements && $elements.length) {
				let elements
				if (separator === undefined) {
					elements = [$elements.get()]
				}
				else {
					elements = cutElements($elements, separator)
				}
				$this.empty()
				$.each(elements, function() {
					elements = $(this).get()
					elements.sort(function(a, b) {
						return (a.textContent.trim().toUpperCase() > b.textContent.trim().toUpperCase())
							? 1
							: -1
					})
					$.each(elements, function() { $this.append(this) })
				})
				if ($selected && $selected.attr('value')) {
					$this.val($selected.attr('value'))
				}
				else {
					$this.val(null)
				}
			}

		})

		return this
	}

	//----------------------------------------------------------------------------------- cutElements
	/**
	 * @param $elements jQuery a jQuery object containing elements to dispatch into separated groups
	 * @param separator string separator jQuery selector
	 * @return jQuery[] elements dispatched into groups
	 */
	const cutElements = function($elements, separator)
	{
		const cut_elements    = []
		let   $local_elements = null
		let   $last_separator = null
		$elements.each(function() {
			const $element = $(this)
			if ($element.is(separator)) {
				pushElements(cut_elements, $local_elements, $last_separator)
				$last_separator = $element
				$local_elements = null
			}
			else if ($local_elements) {
				$local_elements = $local_elements.add($element)
			}
			else {
				$local_elements = $element
			}
		})
		pushElements(cut_elements, $local_elements, $last_separator)
		return cut_elements
	}

	//---------------------------------------------------------------------------------- pushElements
	/**
	 * @param cut_elements    jQuery[]
	 * @param $local_elements jQuery
	 * @param $separator      jQuery
	 */
	const pushElements = function(cut_elements, $local_elements, $separator)
	{
		if ($local_elements && $local_elements.get) {
			if ($separator && cut_elements) {
				cut_elements.push($separator)
			}
			cut_elements.push($local_elements)
		}
	}

})( jQuery )
