$(document).ready(function()
{

	const elements_selector = 'input[name$="]"], select[name$="]"], textarea[name$="]"],'
		+ 'input[data-name$="]"], select[data-name$="]"], textarea[data-name$="]"]'
	const next_elements_selector = 'input:not([data-name], [name])'

	//--------------------------------------------------------------------------------- applyRequired
	/**
	 * Apply data-required => required into $form, depending on the last $modified_element
	 *
	 * this jQuery|object the modified element or the form : DOM element or jQuery object allowed
	 */
	const applyRequired = function()
	{
		const $element  = $(this)
		const $form     = $element.closest('form')
		const $elements = $form.find(elements_selector)

		// ensure that we descend
		$elements.sort(function(element1, element2) {
			const $element1 = $(element1)
			const $element2 = $(element2)
			const name1     = elementName($element1)
			const name2     = elementName($element2)
			const name1_id  = (name1.indexOf('[id]') >= 0)
			const name2_id  = (name2.indexOf('[id]') >= 0)
			if (name1_id && !name2_id) return -1
			if (name2_id && !name1_id) return 1
			const count1    = (name1.match(/]/g) || []).length
			const count2    = (name2.match(/]/g) || []).length
			return count1 - count2
		})

		require($elements, false)
		$form.find('li.bad').removeClass('bad')

		// calculate require
		const required_parents = []
		$elements.each(function() {
			const $element = $(this)
			const name     = elementName($element)
			const parent   = parentName(name)
			if (
				(elementRequired($element) && (!parent || required_parents[parent]))
				|| haveChildrenValues(name, $elements)
			) {
				require($element)
				required_parents[name] = true
			}
			else {
				required_parents[name] = false
			}
		})
	}

	//-------------------------------------------------------------------------- delayedApplyRequired
	const delayedApplyRequired = function()
	{
		const $element      = $(this)
		const $form         = $element.closest('form')
		const before_length = $form.data('before_length')
		const new_length    = $element.val().length
		if ((before_length && !new_length) || (new_length && !before_length)) {
			$form.data('before_length', new_length)
			applyRequired.call($element)
		}
	}

	//----------------------------------------------------------------------------------- elementName
	/**
	 * @param $element jQuery
	 * @return string
	 */
	const elementName = function($element)
	{
		let name = $element.attr('name')
		if (!name) {
			name = $element.data('name')
		}
		return name
	}

	//------------------------------------------------------------------------------- elementRequired
	/**
	 * @param $element jQuery
	 * @return boolean
	 */
	const elementRequired = function($element)
	{
		return $element.data('required') || $element.next(next_elements_selector).data('required')
	}

	//---------------------------------------------------------------------------------- elementValue
	/**
	 * @param $element jQuery
	 * @return ?string
	 */
	const elementValue = function($element)
	{
		if ($element.data('no-empty-check')) {
			return null
		}
		let value = $element.val()
		if (!value) {
			value = $element.next(next_elements_selector).val()
		}
		return value
	}

	//---------------------------------------------------------------------------- haveChildrenValues
	/**
	 * Check if a parent[id] has any value typed in into any of its parent[property] sub-fields
	 *
	 * @const parent_name string
	 * @const $elements   object[] dom elements
	 * @return boolean
	 */
	const haveChildrenValues = function(parent_name, $elements)
	{
		const id_position = parent_name.indexOf('[id]')
		if (id_position < 0) {
			return false
		}
		const filter_in = parent_name.substring(0, id_position)
		let   required  = false
		const trailing  = parent_name.match(/\[[0-9]*]$/)
			? parent_name.substring(parent_name.lastIndexOf('['))
			: ''
		$elements.each(function() {
			const $element = $(this)
			const name     = elementName($element)
			if (
				name.startsWith(filter_in)
				&& (!trailing || name.endsWith(trailing))
				&& (parent_name !== name)
				&& elementValue($element)
			) {
				required = true
				return false
			}
		})
		return required
	}

	//------------------------------------------------------------------------------------ parentName
	/**
	 * Calculate the name of the parent field whose required attribute is to check
	 *
	 * @param name string|null
	 */
	const parentName = function(name)
	{
		let   id_position
		let   parent_name = null
		const last        = name.lastIndexOf('[')
		let   trailing    = ''

		// remove last [xx] where xx is a strict numeric
		if (last >= 0) {
			if (name.match(/\[[0-9]*]$/)) {
				trailing = name.substring(last)
				name     = name.substring(0, last)
			}
		}

		// parent of 'property' : null
		if (last < 0) {
			parent_name = null
		}

		// parent of 'parent[property][id]' : 'parent[id]'
		else if (name.indexOf('[id]') >= 0) {
			// special case : parent of 'parent[id]' : null
			if (name.indexOf('][') >= 0) {
				// parent of 'parent[property][id]' : 'parent[id]'
				id_position         = name.indexOf('[id]')
				const parent_position = name.substring(0, id_position).lastIndexOf('[')
				parent_name         = name.substring(0, parent_position) + name.substring(id_position)
			}
		}

		// parent of 'parent[property]' : 'parent[id]'
		else {
			id_position = name.lastIndexOf('[')
			parent_name = name.substring(0, id_position) + '[id]' + trailing
		}

		return parent_name
	}

	//--------------------------------------------------------------------------------------- require
	/**
	 * Set an element as required, or not
	 *
	 * @param $element jQuery
	 * @param require  boolean default is true
	 */
	const require = function($element, require)
	{
		if (require === undefined) {
			require = true
		}
		const $next = $element.next(next_elements_selector)

		if (require) {
			$element.attr('required', true).closest('.mandatory').addClass('required')
			$next.attr('required', true)
			if ($element.is(':visible') && !$element.val().length) {
				requireTab($element)
			}
		}

		else {
			$element.removeAttr('required').closest('.mandatory:not(.objects)').removeClass('required')
			$next.removeAttr('required')
		}
	}

	//------------------------------------------------------------------------------------ requireTab
	/**
	 * Add a data-required attribute to all tabs header matching the pages of $element
	 *
	 * @param $element jQuery
	 */
	const requireTab = function($element)
	{
		const $page = $element.closest('.ui-tabber-page')
		if ($page.length) {
			const $tabber = $page.closest('.ui-tabber')
			const $tab    = $tabber.find('> .ui-tabber-tabs a[href="#' + $page.attr('id') + '"]').parent()
			if (!$tab.hasClass('bad')) {
				$tab.addClass('bad')
				requireTab($tabber)
			}
		}
	}

	//--------------------------------------------------------- form applyRequired & registerElements
	$('body').build('call', 'form', function()
	{
		this.find('input[required], select[required], textarea[required]')
			.closest('li.mandatory').addClass('required')
		this.find('li.mandatory.objects').addClass('required')
		applyRequired.call(this)
		let $register_elements = this.find(elements_selector)
		$register_elements = $register_elements.add($register_elements.next(next_elements_selector))
		$register_elements.add(next_elements_selector)
			.change(applyRequired)
			.focus(function() { $(this).closest('form').data('before_length', $(this).val().length) })
			.keyup(delayedApplyRequired)
	})

})
